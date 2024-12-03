<?php

namespace App\Http\Controllers;
use App\Models\Task;

use Illuminate\Http\Request;

class TaskController extends Controller
{
    //
    public function index(){
        $products = Task::all();
        return view('products.index', ['products' => $products]);
    }

    public function tasks(){
        $products = Task::all();
        return view('products.tasks', ['products' => $products]);
    }

    public function create(){
        return view('products.create');
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',

        ]);
        $data['description'] = $data['description'] ?? '';
        $newTask = Task::create($data);
        return redirect(route('task.tasks'));
    }

    public function edit(Task $task) {
        return view('products.edit', ['task' => $task]);
    }
     public function update(Task $task, Request $request) {
         $data = $request->validate([
             'name' => 'required|string|max:255',
             'email' => 'required|email|unique:users,email',
             'date' => 'required|date',
             'description' => 'nullable|string|max:500',

         ]);

         $task->update($data);
        return redirect(route('task.tasks'))->with('success', 'Task updated successfully');
     }

     public function delete(Task $task) {
        $task->delete();
        return redirect(route('task.tasks'))->with('success', 'Task deleted successfully');

     }
}
