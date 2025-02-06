@extends('layouts.app')

@section('title', 'Create Task')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/createForm.css') }}">

    <h1>Create a Task</h1>
    <form class="create-task-form" method="post" action="{{route('task.store')}}">
        @csrf
        @method('post')

        <div>
            <label>Project</label>
            <select name="project_id">
                <option value="">No Project</option> <!-- ✅ Možnosť bez projektu -->
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Deadline -->
        <div>
            <label>Deadline</label>
            <input type="date" name="deadline" required />
        </div>

        <!-- Priority Selection -->
        <div>
            <label>Priority</label>
            <select name="priority" required>
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
            </select>
        </div>

        <!-- Expanded Textarea for Description -->
        <div>
            <label>Description</label>
            <textarea name="description" placeholder="Task description" rows="5"></textarea>
        </div>

        <!-- Assign Users as a Styled List -->
        <div>
            <label>Assign to Users</label>
            <ul class="checkbox-list">
                @foreach($users as $user)
                    <li class="checkbox-item">
                        <input type="checkbox" id="user-{{ $user->id }}" name="users[]" value="{{ $user->id }}">
                        <label for="user-{{ $user->id }}">{{ $user->name }}</label>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="form-submit-container">
            <input type="submit" value="Save a new Task" />
        </div>
    </form>

    <script src="{{ asset('js/task-form.js') }}"></script>
@endsection
