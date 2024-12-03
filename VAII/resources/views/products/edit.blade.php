<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>
</head>
<body>
<h1>Edit a task</h1>
<form method= "post" action="{{route('task.update', ['task' => $task])}}">
    @csrf
    @method('put')

    <div>
        <label>Name</label>
        <input type="text" name="name" placeholder="name" value="{{$task->name}}"/>
    </div>

    <div>
        <label>Email</label>
        <input type="text" name="email" placeholder="email" value="{{$task->email}}"/>
    </div>

    <div>
        <label>Date</label>
        <input type="date" name="date" placeholder="date" value="{{$task->date}}"/>
    </div>

    <div>
        <label>Description</label>
        <input type="text" name="description" placeholder="description" value="{{$task->description}}"/>
    </div>

    <div>
        <input type ="submit" value ="Update Task"/>

    </div>
</form>
</body>
</html>
