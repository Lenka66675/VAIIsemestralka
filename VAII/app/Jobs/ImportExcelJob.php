<?php

namespace App\Jobs;

use App\Models\UploadedData;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $sourceType;
    protected $importId;

    public function __construct($filePath, $sourceType, $importId)
    {
        $this->filePath = $filePath;
        $this->sourceType = $sourceType;
        $this->importId = $importId;
    }


    public function handle()
    {
        $this->log("Starting job for file: " . $this->filePath);

        if (!file_exists($this->filePath)) {
            $this->log("ERROR: File not found: " . $this->filePath);
            return;
        }

        try {
            $spreadsheet = IOFactory::load($this->filePath);
            $this->log("Spreadsheet loaded successfully.");
        } catch (\Exception $e) {
            $this->log("ERROR: Spreadsheet loading failed - " . $e->getMessage());
            return;
        }

        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        if (empty($data)) {
            $this->log("WARNING: No data found in spreadsheet.");
            return;
        }

        $headers = array_map('strtolower', array_map('trim', $data[0]));
        $this->log("Hlavičky načítané z Excelu: " . implode(', ', $headers));

        unset($data[0]);

        if (!$this->validateHeaders($headers)) {
            $this->log("ERROR: Excel file does not have correct columns for '$this->sourceType'. Job stopped.");
            return;
        }

        $columnMap = $this->getColumnMapping($this->sourceType, $headers);
        if (empty($columnMap)) {
            $this->log("WARNING: No column mapping found for source type: " . $this->sourceType);
            return;
        }

        $existingRecords = UploadedData::where('source_type', $this->sourceType)
            ->pluck('status', 'request')
            ->toArray();

        $batchSize = 500;
        $rows = [];
        $processed = 0;
        $totalRecords = count($data);

        foreach ($data as $rowIndex => $row) {
            try {
                $requestId = isset($row[$columnMap['request']]) ? $this->extractImportantValue($row[$columnMap['request']]) : null;
                $status = $row[$columnMap['status']] ?? null;

                if (!$requestId || !$status) {
                    $this->log("Skipping row $rowIndex - Missing request or status.");
                    continue;
                }

                if (!isset($existingRecords[$requestId]) || $existingRecords[$requestId] !== $status) {
                    $rows[] = [
                        'source_type' => $this->sourceType,
                        'request' => $requestId,
                        'description' => $row[$columnMap['description']] ?? null,
                        'status' => $status,
                        'type' => $row[$columnMap['type']] ?? null,
                        'created' => $this->parseDate($row[$columnMap['created']] ?? null),
                        'finalized' => $this->parseDate($row[$columnMap['finalized']] ?? null),
                        'vendor' => $row[$columnMap['vendor']] ?? null,
                        'country' => $row[$columnMap['country']] ?? null,
                        'import_id' => $this->importId,
                        'imported_by' => 'system',
                        'imported_at' => now(),
                    ];

                    $existingRecords[$requestId] = $status;
                } else {
                    $this->log("Row $rowIndex skipped (duplicate request: $requestId, status: $status)");
                }

                $processed++;

                if (count($rows) >= $batchSize) {
                    UploadedData::insert($rows);
                    $this->log("Inserted $processed / $totalRecords records.");
                    $rows = [];
                }
            } catch (\Exception $e) {
                $this->log("ERROR processing row $rowIndex: " . $e->getMessage());
            }
        }

        if (!empty($rows)) {
            UploadedData::insert($rows);
            $this->log("Inserted final batch. Total records: $processed");
        }

        $this->log("Import completed successfully!");
    }




    private function validateHeaders($headers)
    {
        $expectedHeaders = [
            'IVMS' => ['path', 'title', 'approval status', 'created', 'approved', 'vendor id', 'country'],
            'MDG' => ['change request', 'description', 'status', 'type', 'created on', 'finalized on', 'bp number', 'country'],
            'Service Now' => ['request', 'short description', 'state', 'item', 'created', 'closed']
        ];

        if (!isset($expectedHeaders[$this->sourceType])) {
            return false;
        }

        $missingColumns = array_diff($expectedHeaders[$this->sourceType], $headers);

        if (!empty($missingColumns)) {
            $this->log("ERROR: Missing columns: " . implode(', ', $missingColumns));
            return false;
        }

        return true;
    }


    private function getColumnMapping($sourceType, $headers)
    {
        $mapping = [
            'IVMS' => [
                'request' => array_search('path', $headers),
                'description' => array_search('title', $headers),
                'status' => array_search('approval status', $headers),
                'type' => null,
                'created' => array_search('created', $headers),
                'finalized' => array_search('approved', $headers),
                'vendor' => array_search('vendor_id', $headers),
                'country' => array_search('country', $headers),
            ],
            'MDG' => [
                'request' => array_search('change_request', $headers),
                'description' => array_search('description', $headers),
                'status' => array_search('status', $headers),
                'type' => array_search('type', $headers),
                'created' => array_search('created on', $headers),
                'finalized' => array_search('finalized on', $headers),
                'vendor' => array_search('bp_number', $headers),
                'country' => array_search('country', $headers),
            ],
            'Service Now' => [
                'request' => array_search('request', $headers),
                'description' => array_search('short description', $headers),
                'status' => array_search('state', $headers),
                'type' => array_search('item', $headers),
                'created' => array_search('created', $headers),
                'finalized' => array_search('closed', $headers),
                'vendor' => null,
                'country' => null,
            ]
        ];

        return $mapping[$sourceType] ?? [];
    }

    private function extractImportantValue($value)
    {
        $value = trim($value);

        if (preg_match('/REQ(\d+)/', $value, $matches)) {
            return $matches[1];
        }
        if (preg_match('/(\d+)$/', $value, $matches)) {
            return $matches[1];
        }
        return $value;
    }

    private function parseDate($value)
    {
        if (!$value) {
            return null;
        }

        $date = is_numeric($value)
            ? Carbon::createFromTimestamp(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value))
            : Carbon::parse($value);

        return $date->startOfDay()->toDateString();
    }


    private function log($message)
    {
        echo $message . "\n";
        Log::info($message);
        file_put_contents(storage_path('logs/job_debug.log'), $message . "\n", FILE_APPEND);
    }
}
