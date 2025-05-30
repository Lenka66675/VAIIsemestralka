@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/tasks.css') }}">

    <h1>Tasks</h1>

    @if(auth()->user()->isAdmin())
        <div class="text-center mt-4">
            <a href="{{ route('task.create') }}" class="btn btn-success custom-button">Create a new Task</a>
        </div>
    @endif

    <div class="filter-container">
        <label for="priorityFilter">Filter by Priority:</label>
        <select id="priorityFilter" class="filter-dropdown">
            <option value="all">All</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>

        <div>

    <div>
        <table border="1">
            <thead>
            <tr>
                <th>ID</th>
                <th>Project</th>
                <th>Description</th>
                <th>Deadline</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Details</th>
                @if(auth()->user()->isAdmin())
                    <th>Edit</th>
                    <th>Delete</th>
                @endif
            </tr>
            </thead>
            <tbody>
            <body data-role="{{ auth()->user()->isAdmin() ? 'admin' : 'user' }}">

            @foreach($tasks as $task)
                <tr id="taskRow-{{ $task->id }}">
                    <td>{{ $task->id }}</td>
                    <td>{{ $task->project ? $task->project->name : 'No Project' }}</td>

                    <td class="editable" data-id="{{ $task->id }}" data-name="description">{{ $task->description }}</td>



                    <td>
                        <span class="taskDeadlineText" data-id="{{ $task->id }}">{{ $task->deadline }}</span>
                        <input type="date" class="taskDeadlineInput d-none" data-id="{{ $task->id }}" value="{{ $task->deadline }}">
                    </td>

                    <td>
                        <span class="taskPriorityText" data-id="{{ $task->id }}">{{ ucfirst($task->priority) }}</span>
                        <select class="taskPriorityInput d-none" data-id="{{ $task->id }}">
                            <option value="low" {{ $task->priority == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ $task->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $task->priority == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </td>

                    <td>
                        @if(auth()->user()->isAdmin())
                            @foreach($task->users as $user)
                                <div>
                                    <strong>{{ $user->name }}:</strong>
                                    {{ $user->pivot ? ucfirst($user->pivot->status) : 'No status' }}
                                </div>
                            @endforeach
                        @else
                            <select class="status-dropdown" data-task-id="{{ $task->id }}">
                                <option value="pending" {{ $task->pivot && $task->pivot->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $task->pivot && $task->pivot->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $task->pivot && $task->pivot->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            <span class="status-updated-message" style="color: green; display: none;">✔ Updated</span>
                        @endif
                    </td>


                    <td>
                        <button class="btn btn-classic viewTaskButton" data-id="{{ $task->id }}">View Details</button>
                    </td>

                    @if(auth()->user()->isAdmin())
                        <td>
                            <button class="btn btn-classic editTaskButton" data-id="{{ $task->id }}">Edit</button>
                            <button class="btn btn-success saveTaskButton d-none" data-id="{{ $task->id }}">Save</button>
                        </td>
                        <td>
                            <button class="btn btn-danger deleteTaskButton"
                                    data-id="{{ $task->id }}"
                                    data-url="{{ url('/task/' . $task->id . '/delete') }}">
                                Delete
                            </button>
                        </td>
                    @endif


                </tr>
            @endforeach
            </tbody>

        </table>
        <div class="pagination-container">
            {{ $tasks->links() }}
        </div>



    </div>

    <div id="taskModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Task Details</h2>
            <p><strong>Description:</strong> <span id="taskDescription"></span></p>

            <div id="existingSolution"></div>
            <div id="existingAttachment"></div>

            @if(!auth()->user()->isAdmin())
                <p><strong>Add a Solution:</strong></p>
                <textarea id="solutionText" placeholder="Enter your solution..."></textarea>
                <p><strong>Attachment:</strong></p>
                <input type="file" id="solutionFile">
                <button class="btn btn-success saveSolutionButton">Save Solution</button>
            @endif
        </div>
    </div>

    <script src="{{ asset('js/status-update.js') }}"></script>
    <script src="{{ asset('js/edit-task.js') }}"></script>
    <script src="{{ asset('js/delete-task.js') }}"></script>
    <script src="{{ asset('js/view-task.js') }}"></script>
            <script src="{{ asset('js/task-filter.js') }}"></script>

@endsection

















