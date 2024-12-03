<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>
</head>
<body>
<h1>Task</h1>
<div>
    <a href="{{route('task.index')}}"> HlavnaStranka</a>
</div>
<div>
    <a href="{{route('task.create')}}"> create a task</a>
</div>

<div>
    <table border="1">
        <tr>
            ID
        </tr>
        <tr>
            name
        </tr>
        <tr>
            email
        </tr>
        <tr>
            date
        </tr>
        <tr>
            description
        </tr>
        <tr>
            edit
        </tr>
        <tr>
            delete
        </tr>
        @foreach($products as $task)
            <tr>
                <td>
                    {{$task->id}} </td>
                <td>  {{$task->name}}</td>

                <td>  {{$task->email}}</td>

                <td> {{$task->date}}</td>
                <td>    {{$task->description}}
                </td>
                <td>
                    <a href="{{route('task.edit', ['task' => $task])}}">Edit</a>
                </td>
                <td>
                    <form method="post" action="{{route('task.delete', ['task' => $task])}}">
                        @csrf
                        @method('delete')

                        <input type="submit" value="delete"/>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
</div>
</body>
</html>
