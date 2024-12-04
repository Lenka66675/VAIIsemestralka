@extends('layouts.app')

@section('title', 'Contact')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/tasks.css') }}">

    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Task Management</title>

</head>
<body>
<h1>Tasks</h1>




<div class="text-center mt-4">
    <a href="{{route('task.create')}}" class="btn btn-success custom-button">Create a new Task</a>
</div>

<div>
    <table border="1">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Date</th>
            <th>Description</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $task)
            <tr id="taskRow-{{$task->id}}">
                <td>{{$task->id}}</td>
                <td class="taskName" data-name="name" data-id="{{$task->id}}" data-url="{{route('task.update', ['task' => $task])}}">
                    {{$task->name}}
                </td>
                <td class="taskEmail" data-name="email" data-id="{{$task->id}}" data-url="{{route('task.update', ['task' => $task])}}">
                    {{$task->email}}
                </td>
                <td class="taskDate" data-name="date" data-id="{{$task->id}}" data-url="{{route('task.update', ['task' => $task])}}">
                    {{$task->date}}
                </td>
                <td class="taskDescription" data-name="description" data-id="{{$task->id}}" data-url="{{route('task.update', ['task' => $task])}}">
                    {{$task->description}}
                </td>
                <td>
                    <button class="editTaskButton" data-id="{{$task->id}}">
                        Edit
                    </button>
                </td>
                <td>
                    <button class="deleteTaskButton" data-url="{{route('task.delete', ['task' => $task])}}">
                        Delete
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<script src="{{ asset('js/edit-task.js') }}"></script>
<script src="{{ asset('js/delete-task.js') }}"></script>
</body>
</html>
@endsection
