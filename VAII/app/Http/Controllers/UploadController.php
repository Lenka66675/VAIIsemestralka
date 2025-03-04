<?php

namespace App\Http\Controllers;

use App\Jobs\ImportExcelJob;
use Illuminate\Http\Request;
use App\Models\UploadedData;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class UploadController extends Controller
{
    // Zobrazenie formulára na nahrávanie súboru
    public function showForm()
    {
        return view('products.uploadData');
    }

    // Spracovanie nahraného súboru
    public function upload(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 600);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:102400',
            'source_type' => 'required|string'
        ]);

        try {
            $file = $request->file('file');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $filePath = $file->storeAs('uploads', $fileName, 'public');

            // Načítanie súboru a validácia hlavičiek ešte pred jobom
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/public/uploads/' . $fileName));
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Súbor neobsahuje žiadne údaje.'
                ], 400);
            }

            $headers = array_map('strtolower', array_map('trim', $data[0]));

            if (!$this->validateHeaders($headers, $request->source_type)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Excel súbor neobsahuje správne stĺpce pre zdroj: ' . $request->source_type
                ], 400);
            }

            ImportExcelJob::dispatch(storage_path('app/public/uploads/' . $fileName), $request->source_type);

            return response()->json([
                'success' => true,
                'message' => 'Súbor bol úspešne odoslaný na spracovanie.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri nahrávaní súboru: ' . $e->getMessage()
            ], 500);
        }
    }

    private function validateHeaders($headers, $sourceType)
    {
        $expectedHeaders = [
            'IVMS' => ['path', 'title', 'approval status', 'created', 'approved', 'vendor id', 'country'],
            'MDG' => ['change request', 'description', 'status', 'type', 'created on', 'finalized on', 'bp number', 'country'],
            'Service Now' => ['request', 'short description', 'state', 'item', 'created', 'closed']
        ];

        if (!isset($expectedHeaders[$sourceType])) {
            return false;
        }

        $missingColumns = array_diff($expectedHeaders[$sourceType], $headers);

        return empty($missingColumns);
    }




    // Funkcia na mapovanie stĺpcov podľa `source_type`
    private function getColumnMapping($sourceType, $headers)
    {
        $mapping = [
            'IVMS' => [
                'request' => array_search('path', $headers),
                'description' => array_search('title', $headers),
                'status' => array_search('approval_status', $headers),
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
                'created' => array_search('created_on', $headers),
                'finalized' => array_search('finalized_on', $headers),
                'vendor' => array_search('bp_number', $headers),
                'country' => array_search('country', $headers),
            ],
            'Service Now' => [
                'request' => array_search('request', $headers),
                'description' => array_search('short_description', $headers),
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
        $value = trim($value); // Odstráni medzery na začiatku a konci

        if (preg_match('/REQ(\d+)/', $value, $matches)) {
            return $matches[1]; // Vracia iba číslo bez REQ
        }
        if (preg_match('/(\d+)$/', $value, $matches)) {
            return $matches[1]; // Vracia iba číslo na konci URL
        }
        return $value;
    }



}
