@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/createProject.css') }}">

    <h1>Create a Project</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form class="create-project-form" method="post" action="{{ route('project.store') }}" enctype="multipart/form-data">
        @csrf

        <div>
            <label>Project Name</label>
            <input type="text" name="name" required placeholder="Enter project name" />
            @error('name') <p class="error-message">{{ $message }}</p> @enderror
        </div>

        <div>
            <label>Project Description</label>
            <textarea name="description" placeholder="Enter project description"></textarea>
            @error('description') <p class="error-message">{{ $message }}</p> @enderror
        </div>

        <div>
            <label>Project Image</label>
            <input type="file" name="image" accept="image/*" />
            @error('image') <p class="error-message">{{ $message }}</p> @enderror
        </div>

        <div>
            <label>Attach Files</label>
            <input type="file" name="attachments[]" multiple />
            @error('attachments.*') <p class="error-message">{{ $message }}</p> @enderror
        </div>

        <div class="form-submit-container">
            <button type="submit" class="btn btn-primary">Create Project</button>
        </div>
    </form>
@endsection
