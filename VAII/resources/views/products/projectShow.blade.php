@extends('layouts.app')

@section('title', $project->name)

@section('content')
    <link rel="stylesheet" href="{{ asset('css/projectDetail.css') }}">

    <div class="project-container">
        <div class="project-card" id="projectCard-{{ $project->id }}">

            @if($project->image)
                <img src="{{ asset('storage/' . $project->image) }}" alt="Project Image" class="project-image">
            @endif

            <h1 class="project-title">{{ $project->name }}</h1>

            <p class="project-description">{{ $project->description }}</p>

            @if($project->attachments)
                <div class="project-attachments">
                    <h3>Attachments</h3>
                    <ul id="projectAttachmentsList">
                        @foreach(json_decode($project->attachments, true) as $attachment)
                            <li>
                                📁 <a href="{{ asset('storage/' . $attachment) }}" download>
                                    {{ preg_replace('/^\d+_/', '', basename($attachment)) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="project-buttons">
                <a href="{{ route('project') }}" class="btn btn-danger">🔙 Back to Projects</a>

                @if(auth()->user() && auth()->user()->isAdmin())
                    <button class="editProjectButton btn btn-danger" data-id="{{ $project->id }}">✏ Edit</button>
                    <button class="btn btn-danger deleteProjectButton"
                            data-id="{{ $project->id }}"
                            data-url="{{ route('project.destroy', $project->id) }}">
                        🗑 Delete
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if(auth()->user() && auth()->user()->isAdmin())
        <div id="editProjectModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Edit Project</h2>

                <form id="editProjectForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" id="editProjectId" name="id">

                    <label>Project Name:</label>
                    <input type="text" id="editProjectName" name="name" required>

                    <label>Description:</label>
                    <textarea id="editProjectDescription" name="description"></textarea>

                    <label>Current Image:</label>
                    <img id="editProjectImagePreview" src="" alt="Project Image" style="display:none; width: 100%; max-height: 200px; object-fit: cover;">

                    <label>Change Image:</label>
                    <input type="file" id="editProjectImage" name="image" accept="image/*">

                    <label>Change Attachments:</label>
                    <input type="file" id="editProjectAttachments" name="attachments[]" multiple>

                    <button type="submit" class="btn btn-success">Save Changes</button>
                </form>
            </div>
        </div>






        @endif

    <script src="{{ asset('js/delete-project.js') }}"></script>
    <script src="{{ asset('js/edit-project.js') }}"></script>

@endsection
