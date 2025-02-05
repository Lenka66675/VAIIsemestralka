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

    <div>
        <table border="1">
            <thead>
            <tr>
                <th>ID</th>
                <th>Description</th>
                <th>Deadline</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Details</th> <!-- 🔹 New Column for Viewing Details -->
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
                    <td class="editable" data-id="{{ $task->id }}" data-name="description">{{ $task->description }}</td>

                    <!-- Deadline -->
                    <td>
                        <span class="taskDeadlineText" data-id="{{ $task->id }}">{{ $task->deadline }}</span>
                        <input type="date" class="taskDeadlineInput d-none" data-id="{{ $task->id }}" value="{{ $task->deadline }}">
                    </td>

                    <!-- Priority -->
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
                                    <strong>{{ $user->name }}:</strong> {{ ucfirst($user->pivot->status) }}
                                </div>
                            @endforeach
                        @else
                            <select class="status-dropdown" data-task-id="{{ $task->id }}">
                                <option value="pending" {{ $task->pivot->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $task->pivot->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $task->pivot->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            <span class="status-updated-message" style="color: green; display: none;">✔ Updated</span>
                        @endif
                    </td>

                    <!-- 🔹 New View Details Button -->
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
    </div>

    <!-- 🔹 Task Modal for Adding Solutions -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Task Details</h2>
            <p><strong>Description:</strong> <span id="taskDescription"></span></p>

            <!-- 🔹 Display Existing Solutions -->
            <div id="existingSolution"></div>
            <div id="existingAttachment"></div>

            <!-- 🔹 Employee Solution Input -->
            @if(!auth()->user()->isAdmin())
                <p><strong>Add a Solution:</strong></p>
                <textarea id="solutionText" placeholder="Enter your solution..."></textarea>
                <p><strong>Attachment:</strong></p>
                <input type="file" id="solutionFile">
                <button class="btn btn-success saveSolutionButton">Save Solution</button>
            @endif
        </div>
    </div>

    <!-- 🔹 Include JavaScript Files -->
    <script src="{{ asset('js/status-update.js') }}"></script>
    <script src="{{ asset('js/edit-task.js') }}"></script>
    <script src="{{ asset('js/delete-task.js') }}"></script>
    <script src="{{ asset('js/view-task.js') }}"></script>
@endsection
