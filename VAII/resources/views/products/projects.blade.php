@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/projects.css') }}">

    <h1>Projects</h1>

    <div class="text-center mt-4">
        <a href="{{ route('project.create') }}" class="btn btn-success">Create a new Project</a>
    </div>

    <div class="project-list">
        @foreach($projects as $project)
            <div class="project-card">
                <h2>{{ $project->name }}</h2>
                <p>{{ $project->description }}</p>
                <a href="{{ route('project.show', $project->id) }}" class="btn">View Details</a>
            </div>
        @endforeach
    </div>
    <div class="pagination-container">
        {{ $projects->links() }}
    </div>
@endsection
