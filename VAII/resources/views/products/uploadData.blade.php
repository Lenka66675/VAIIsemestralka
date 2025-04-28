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

        <!-- Upload Icon -->
        <div class="image-container">
            <img src="{{ asset('images/upload.png') }}" alt="Upload icon" class="upload-icon">
        </div>

        <!-- Data Source -->
        <div class="form-group">
            <label for="source_type">Data Source</label>
            <select name="source_type" required>
                <option value="IVMS">IVMS</option>
                <option value="MDG">MDG</option>
                <option value="Service Now">Service Now</option>
            </select>
        </div>

        <!-- Custom File Upload -->
        <div class="form-group file-upload-container">
            <label for="file">Select Excel file</label>
            <div class="custom-file-upload">
                <label class="btn btn-outline-light">
                    Choose file
                    <input type="file" id="fileInput" name="file" required style="display: none;" onchange="document.getElementById('fileName').innerText = this.files[0]?.name || 'No file chosen';">
                </label>
                <span id="fileName" class="text-light">No file chosen</span>
            </div>
            @if ($errors->has('file'))
                <span class="error-message-inline">{{ $errors->first('file') }}</span>
            @endif
        </div>

        <!-- Error from backend -->
        <div id="backendErrors" class="error-message"></div>

        <!-- Spinner -->
        <div id="loadingSpinner" class="loading-spinner" style="display: none;">
            <img src="{{ asset('images/loading.png') }}" alt="Loading...">
            <p>Please wait...</p>
        </div>

        <!-- Submit -->
        <div class="form-submit-container">
            <button type="submit" class="btn-upload" id="uploadBtn">
                <i class="fas fa-cloud-upload-alt"></i> Upload
            </button>
        </div>
    </form>

    <script src="{{ asset('js/upload-excel.js') }}"></script>

    <style>
        .custom-file-upload {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        #fileName {
            font-weight: 200;
        }
    </style>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function () {
            document.getElementById('fileName').innerText = 'No file chosen';
        });

    </script>
@endsection
