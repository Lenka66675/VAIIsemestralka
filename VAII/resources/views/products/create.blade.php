@extends('layouts.app')

@section('title', 'Create Task')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/createForm.css') }}">

    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Create a Task</title>
    </head>
    <body>
    <h1>Create a task</h1>
    <form class="create-task-form" method="post" action="{{route('task.store')}}">
        @csrf
        @method('post')

        <div>
            <label>Name</label>
            <input type="text" name="name" placeholder="name" />
        </div>

        <div>
            <label>Email</label>
            <input type="text" name="email" placeholder="email" />
        </div>

        <div>
            <label>Date</label>
            <input type="date" name="date" placeholder="date" />
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

        <div>
            <div class="form-submit-container">
                <input type="submit" value="Save a new Task" />
            </div>
        </div>
    </form>
    <script src="{{ asset('js/task-form.js') }}"></script>

    </body>
    </html>
@endsection
