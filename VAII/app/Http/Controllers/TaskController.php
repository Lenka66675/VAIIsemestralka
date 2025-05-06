<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Project;

class TaskController extends Controller
{

    public function tasks()
    {
        if (auth()->user()->isAdmin()) {
            $tasks = Task::simplePaginate(10);
        } else {
            $tasks = auth()->user()->tasks()->wherePivot('status', '!=', 'completed')->paginate(10);
        }

        return view('products.tasks', compact('tasks'));
    }




    public function create()
    {

        $users = User::where('role', '!=', 'admin')->get();
        $projects = Project::all();
        return view('products.create', compact('users', 'projects'));
    }


    public function post(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'deadline' => 'required|date|after:today',
            'description' => 'required|string|max:500',
            'priority' => 'required|in:low,medium,high',
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
        ]);


        $newTask = Task::create([
            'project_id' => $data['project_id'] ?? null,
            'deadline' => $data['deadline'],
            'description' => $data['description'] ?? '',
            'priority' => $data['priority'],
        ]);


        $newTask->users()->attach($data['users']);

        return redirect(route('task.tasks'));
    }


    public function edit(Task $task)
    {
        return view('products.edit', compact('task'));
    }


    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'deadline' => 'sometimes|required|date|after:today',
            'description' => 'sometimes|required|string|max:500',
            'priority' => 'sometimes|required|in:low,medium,high'
        ]);

        $task->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully!',
            'task' => $task
        ]);
    }




    public function delete(Task $task)
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully!',
            'id' => $task->id
        ]);
    }







    public function updateStatus(Request $request, Task $task)
    {
        $user = auth()->user();

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        if (!$task->users()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this task!'
            ], 403);
        }

        $task->users()->updateExistingPivot($user->id, ['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully!',
            'status' => $request->status
        ]);
    }







    public function show(Task $task)
    {
        return response()->json([
            "id" => $task->id,
            "description" => $task->description,
            "solution" => $task->solution,
            "attachment" => $task->attachment ? asset('storage/' . $task->attachment) : null
        ]);
    }

    public function saveSolution(Request $request, Task $task)
    {
        $request->validate([
            'solution' => 'nullable|string|max:2000',
            'attachments.*' => 'nullable|file|max:5120|mimes:jpg,png,pdf,doc,docx,xlsx,csv'
        ]);

        $user = auth()->user();
        $data = [];

        if ($request->has('solution')) {
            $data['solution'] = $request->input('solution');
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('attachments', $filename, 'public');

                $existingAttachments = json_decode($user->tasks()->where('task_id', $task->id)->first()->pivot->attachment, true) ?? [];
                $existingAttachments[] = $path;
                $data['attachment'] = json_encode($existingAttachments);
            }
        }

        $task->users()->updateExistingPivot($user->id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Solution saved successfully!'
        ]);
    }






    public function getSolution($taskId)
    {
        $user = auth()->user();
        $solution = $user->tasks()->where('task_id', $taskId)->first();

        if ($solution) {
            $attachments = json_decode($solution->pivot->attachment, true) ?? [];

            return response()->json([
                'solution' => $solution->pivot->solution,
                'attachments' => array_map(function ($attachment) {
                    return asset('storage/' . $attachment);
                }, $attachments),
            ]);
        }

        return response()->json(['message' => 'No solution found'], 404);
    }



    public function deleteAttachment(Task $task, $fileName)
    {
        $user = auth()->user();

        $solution = $task->users()->where('user_id', $user->id)->first();

        if (!$solution || !$solution->pivot->attachment) {
            return response()->json(['success' => false, 'message' => 'No attachment found'], 404);
        }

        $attachments = json_decode($solution->pivot->attachment, true) ?? [];

        $newAttachments = array_filter($attachments, function ($attachment) use ($fileName) {
            return basename($attachment) !== $fileName;
        });

        $task->users()->updateExistingPivot($user->id, ['attachment' => json_encode(array_values($newAttachments))]);

        $filePath = storage_path("app/public/attachments/{$fileName}");
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return response()->json(['success' => true, 'message' => 'Attachment deleted successfully']);
    }


    public function getAllSolutions(Task $task)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $solutions = $task->users()->withPivot('solution', 'attachment')->get();

        return response()->json([
            'solutions' => $solutions->map(function ($user) {
                $attachments = json_decode($user->pivot->attachment, true) ?? [];
                return [
                    'user' => $user->name,
                    'solution' => $user->pivot->solution ?? 'No solution provided',
                    'attachments' => array_map(function ($attachment) {
                        return asset('storage/' . $attachment);
                    }, $attachments),
                ];
            })
        ]);
    }




    public function downloadAttachment(Task $task, $userId)
    {
        $user = $task->users()->where('user_id', $userId)->first();

        if (!$user || !$user->pivot->attachment) {
            return response()->json(['error' => 'No attachment found'], 404);
        }

        $filePath = storage_path("app/public/" . $user->pivot->attachment);

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download($filePath);
    }










    public function downloadFile($taskId, $fileName)
    {
        if (!$fileName) {
            return response()->json(['message' => 'File name missing'], 400);
        }

        $filePath = "attachments/{$fileName}";

        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath);
        }

        return response()->json(['message' => 'File not found'], 404);
    }










}
