<?php

namespace App\Http\Controllers;
use App\Models\Task;
use App\Models\User;


use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(){
        $products = Task::all();
        return view('products.index', ['products' => $products]);
    }

    public function tasks2(){
        $products = Task::all();
        return view('products.tasks', ['products' => $products]);
    }

    public function tasks()
    {
        // Ak je užívateľ admin, zobrazí sa všetko
        if (auth()->user()->isAdmin()) {
            $products = Task::all(); // Admin vidí všetky úlohy
        } else {
            $products = auth()->user()->tasks; // Používateľ vidí len svoje úlohy
        }

        return view('products.tasks', ['products' => $products]);
    }




    public function create()
    {
        // Získať všetkých používateľov
        $users = \App\Models\User::all(); // Importuj User model, ak už nie je
        return view('products.create', compact('users'));
    }


    public function post(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'users' => 'required|array', // Zoznam používateľov
            'users.*' => 'exists:users,id', // Každý používateľ musí existovať
        ]);

        $data['description'] = $data['description'] ?? '';

        $newTask = Task::create($data);

        // Priradiť používateľov k tasku
        $newTask->users()->attach($data['users']);

        return redirect(route('task.tasks'));
    }


    public function edit(Task $task) {
        return view('products.edit', ['task' => $task]);
    }
    public function update(Task $task, Request $request)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $task->id,
            'date' => 'sometimes|required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $task->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'task' => $task,
        ]);
    }


    public function delete(Task $task)
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
            'id' => $task->id,
        ]);
    }
}
