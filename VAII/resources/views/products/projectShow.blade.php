@extends('layouts.app')

@section('title', $project->name)

@section('content')
    <link rel="stylesheet" href="{{ asset('css/projectDetail.css') }}">

    <div class="project-container">
        <div class="project-card" id="projectCard-{{ $project->id }}">

            <!-- 🖼 Obrázok projektu -->
            @if($project->image)
                <img src="{{ asset('storage/' . $project->image) }}" alt="Project Image" class="project-image">
            @endif

            <!-- 📝 Názov projektu -->
            <h1 class="project-title">{{ $project->name }}</h1>

            <!-- 📄 Popis projektu -->
            <p class="project-description">{{ $project->description }}</p>

            <!-- 📎 Sekcia príloh -->
            @if($project->attachments)
                <div class="project-attachments">
                    <h3>Attachments</h3>
                    <ul id="projectAttachmentsList">
                        @if($project->attachments)
                            @foreach(json_decode($project->attachments, true) as $attachment)
                                <li>
                                    📁 <a href="{{ asset('storage/' . $attachment) }}" download>
                                        {{ preg_replace('/^\d+_/', '', basename($attachment)) }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    </ul>



                </div>
            @endif


            <!-- 🎛 Tlačidlá -->
            <div class="project-buttons">
                <a href="{{ route('project') }}" class="btn btn-danger">🔙 Back to Projects</a>
                <button class="editProjectButton btn btn-danger" data-id="{{ $project->id }}">✏ Edit</button>
                <button class="btn btn-danger deleteProjectButton"
                        data-id="{{ $project->id }}"
                        data-url="{{ route('project.destroy', $project->id) }}">
                    🗑 Delete
                </button>
            </div>
        </div>
    </div>

    <!-- 🆕 MODÁLNE OKNO PRE EDITÁCIU -->
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

    <script src="{{ asset('js/delete-project.js') }}"></script>
    <script src="{{ asset('js/edit-project.js') }}"></script>

@endsection
