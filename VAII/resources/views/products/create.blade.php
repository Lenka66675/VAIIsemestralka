<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>
</head>
<body>
<h1>Create a task</h1>
<form method= "post" action="{{route('task.store')}}">
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

    <div>
        <label>Description</label>
        <input type="text" name="description" placeholder="description" />
    </div>

    <div>
        <input type ="submit" value ="Save a new Task"/>

    </div>
</form>
<script src="{{ asset('js/task-form.js') }}"></script>

</body>
</html>
