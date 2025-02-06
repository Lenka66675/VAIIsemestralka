@extends('layouts.app')

@section('title', $project->name)

@section('content')
    <link rel="stylesheet" href="{{ asset('css/projectDetail.css') }}">

    <div class="project-container">
        <div class="project-card">

            <!-- Obrázok projektu -->
            @if($project->image)
                <img src="{{ asset('storage/' . $project->image) }}" alt="Project Image" class="project-image">
            @endif

            <!-- Názov projektu -->
            <h1 class="project-title">{{ $project->name }}</h1>

            <!-- Popis projektu -->
            <p class="project-description">{{ $project->description }}</p>

            <!-- Sekcia príloh -->
            @if($project->attachments)
                <div class="project-attachments">
                    <h3>Attachments</h3>
                    <ul>
                        @foreach(json_decode($project->attachments, true) as $attachment)
                            <li>
                                📁
                                <a href="{{ asset('storage/' . $attachment) }}" download>
                                    {{ preg_replace('/^\d+_/', '', basename($attachment)) }}  <!-- ⬅ Odstráni číslo na začiatku -->
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <!-- Tlačidlá -->
            <div class="project-buttons">
                <a href="{{ route('project') }}" class="btn btn-danger">🔙 Back to Projects</a>
                <button class="btn btn-danger">✏ Edit</button>
                <button class="btn btn-danger">🗑 Delete</button>
            </div>

        </div>
    </div>
@endsection
