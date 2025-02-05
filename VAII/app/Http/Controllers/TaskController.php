<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Zobrazenie zoznamu úloh.
     */
    public function tasks()
    {
        if (auth()->user()->isAdmin()) {
            // ✅ Admin vidí všetky úlohy
            $tasks = Task::all();
        } else {
            // ✅ Používateľ vidí len úlohy, ktoré nie sú "completed"
            $tasks = auth()->user()->tasks()->wherePivot('status', '!=', 'completed')->get();
        }

        return view('products.tasks', ['tasks' => $tasks]);
    }


    /**
     * Formulár na vytvorenie úlohy.
     */
    public function create()
    {
        // Získať všetkých používateľov okrem adminov
        $users = User::where('role', '!=', 'admin')->get();
        return view('products.create', compact('users'));
    }

    /**
     * Uloženie novej úlohy.
     */
    public function post(Request $request)
    {
        $data = $request->validate([
            'deadline' => 'required|date',
            'description' => 'nullable|string|max:500',
            'priority' => 'required|in:low,medium,high',
            'users' => 'required|array', // Používateľ musí byť vybraný
            'users.*' => 'exists:users,id', // Každý užívateľ musí existovať
        ]);

        // Vytvorenie novej úlohy
        $newTask = Task::create([
            'deadline' => $data['deadline'],
            'description' => $data['description'] ?? '',
            'priority' => $data['priority']
        ]);

        // Priradiť používateľov k úlohe
        $newTask->users()->attach($data['users']);

        return redirect(route('task.tasks'));
    }

    /**
     * Formulár na editovanie úlohy.
     */
    public function edit(Task $task)
    {
        return view('products.edit', compact('task'));
    }

    /**
     * Aktualizácia úlohy.
     */
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'deadline' => 'sometimes|required|date',
            'priority' => 'sometimes|required|in:low,medium,high'
        ]);

        $task->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully!',
            'task' => $task
        ]);
    }



    /**
     * Odstránenie úlohy.
     */
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

        if ($task->users->contains($user->id)) {
            // ✅ IBA AKTUALIZUJEME STAV, NEODSTRAŇUJEME ÚLOHU Z DB
            $task->users()->updateExistingPivot($user->id, ['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully!',
                'status' => $request->status
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'You are not authorized to update this task!'
        ], 403);
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
            'attachments.*' => 'nullable|file|max:5120'
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

                // Pridať nový súbor k existujúcim
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
            // Dekóduj JSON zo stĺpca "attachment"
            $attachments = json_decode($solution->pivot->attachment, true) ?? [];

            return response()->json([
                'solution' => $solution->pivot->solution,
                'attachments' => array_map(function ($attachment) {
                    return asset('storage/' . $attachment);
                }, $attachments), // Správne vygenerované URL pre frontend
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

        // Nájdeme a odstránime daný súbor zo zoznamu
        $newAttachments = array_filter($attachments, function ($attachment) use ($fileName) {
            return basename($attachment) !== $fileName;
        });

        // Aktualizujeme databázu
        $task->users()->updateExistingPivot($user->id, ['attachment' => json_encode(array_values($newAttachments))]);

        // Odstránime súbor zo servera
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
