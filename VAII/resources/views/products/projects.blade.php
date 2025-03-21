@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/projects.css') }}">

    <h1>Projects</h1>
    @if(auth()->check() && auth()->user()->isAdmin())
        <div class="text-center mt-4">
            <a href="{{ route('project.create') }}" class="btn btn-success">Create a new Project</a>
        </div>
    @endif



    <div class="project-list">
        @foreach($projects as $project)
            <div class="project-card"
                 style="background-image: url('{{ $project->gif ? asset('storage/' . $project->gif) : asset('images/Fotoram.io.png') }}');">
                <h2>{{ $project->name }}</h2>
                <a href="{{ route('project.show', $project->id) }}" class="btn">View Details</a>
            </div>
        @endforeach
    </div>


    <div class="pagination-container">
        {{ $projects->links() }}
    </div>

@endsection
