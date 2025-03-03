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

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName(); // Pridanie timestampu, aby sa predišlo konfliktom názvov
        $filePath = $file->storeAs('uploads', $fileName, 'public'); // Ukladá do storage/app/public/uploads/

        // Spusti Laravel Job s absolútnou cestou
        ImportExcelJob::dispatch(storage_path('app/public/uploads/' . $fileName), $request->source_type);

        return redirect()->route('upload.form')->with('success', 'Súbor bol úspešne odoslaný na spracovanie.');
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
