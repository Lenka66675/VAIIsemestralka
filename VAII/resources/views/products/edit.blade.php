@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Task</title>
</head>
<body>
<h1>Edit Task</h1>

<form method="post" action="{{ route('task.update', ['task' => $task]) }}">
    @csrf
    @method('put')

    <div>
        <label>Deadline</label>
        <input type="date" name="deadline" placeholder="Deadline" value="{{ $task->deadline }}" required />
    </div>

    <div>
        <label>Priority</label>
        <select name="priority">
            <option value="low" {{ $task->priority == 'low' ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ $task->priority == 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high" {{ $task->priority == 'high' ? 'selected' : '' }}>High</option>
        </select>
    </div>

    <div>
        <label>Description</label>
        <textarea name="description" placeholder="Task description" rows="5">{{ $task->description }}</textarea>
    </div>

    <div>
        <input type="submit" value="Update Task" />
    </div>
</form>

</body>
</html>
@endsection
