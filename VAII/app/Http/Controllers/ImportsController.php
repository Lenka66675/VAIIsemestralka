<?php

namespace App\Http\Controllers;

use App\Models\ImportedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class ImportsController extends Controller
{
    public function index()
    {
        $imports = ImportedFile::withCount('uploadedData')
            ->orderByDesc('uploaded_at')
            ->paginate(15);

        return view('imports.index', compact('imports'));
    }

    public function destroy($id)
    {
        $import = ImportedFile::findOrFail($id);

        $import->uploadedData()->delete();

        $filePath = storage_path('app/public/uploads/' . $import->file_name);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $import->delete();

        return redirect()->route('imports.index')->with('success', 'Import a jeho údaje boli odstránené.');
    }

    public function show($id)
    {
        $import = ImportedFile::findOrFail($id);

        $uploadedData = $import->uploadedData()->paginate(10);

        return view('imports.show', compact('import', 'uploadedData'));
    }


    public function download($id)
    {
        $import = ImportedFile::findOrFail($id);

        $path = 'uploads/' . $import->file_name;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Súbor neexistuje.');
        }

        return Storage::disk('public')->download($path, $import->original_filename);
    }

}

