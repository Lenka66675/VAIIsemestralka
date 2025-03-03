@extends('layouts.app')

@section('title', 'Upload Excel File')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/createForm.css') }}">

    <h1>Upload Excel File</h1>

    @if(session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif

    <form id="uploadForm" class="create-task-form upload-form" action="{{ route('upload.process') }}" method="post" enctype="multipart/form-data">
        @csrf

        <!-- Obrázok vo formulári -->
        <div class="image-container">
            <img src="{{ asset('images/upload.png') }}" alt="Upload ikona" class="upload-icon">
        </div>

        <!-- Zdroj údajov (Select) -->
        <div class="form-group">
            <label for="source_type">Zdroj údajov</label>
            <select name="source_type" required>
                <option value="IVMS">Vendor</option>
                <option value="MDG">Change Request</option>
                <option value="Service Now">Service Request</option>
            </select>
        </div>

        <!-- Výber súboru + Error správa vedľa tlačidla -->
        <div class="form-group file-upload-container">
            <label for="file">Vyber Excel súbor</label>
            <div class="custom-file-upload">
                <input type="file" id="fileInput" name="file" required>
                <label for="fileInput" class="file-label">
                    <i class="fas fa-folder-open"></i> <span id="fileNameDisplay">Vybrať súbor</span>
                </label>
            </div>
            @if ($errors->has('file'))
                <span class="error-message-inline">{{ $errors->first('file') }}</span>
            @endif
        </div>

        <!-- Dynamické error správy z back-endu -->
        <div id="backendErrors" class="error-message"></div>

        <!-- Loading Spinner (Najskôr skrytý) -->
        <div id="loadingSpinner" class="loading-spinner" style="display: none;">
            <img src="{{ asset('images/spinner.gif') }}" alt="Loading...">
            <p>Nahráva sa, prosím čakajte...</p>
        </div>

        <div class="form-submit-container">
            <button type="submit" class="btn-upload" id="uploadBtn">
                <i class="fas fa-cloud-upload-alt"></i> Upload
            </button>
        </div>
    </form>
    <script src="{{ asset('js/upload-excel.js') }}"></script>

@endsection
