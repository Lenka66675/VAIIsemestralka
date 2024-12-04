<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/task', [TaskController::class, 'index'])->name('task.index');
Route::get('/tasks', [TaskController::class, 'tasks'])->name('task.tasks');


Route::get('task/create', [TaskController::class, 'create'])->name('task.create');
Route::post('/task', [TaskController::class, 'store'])->name('task.store');
Route::get('/task/{task}/edit', [TaskController::class, 'edit'])->name('task.edit');
Route::put('/task/{task}', [TaskController::class, 'update'])->name('task.update');
Route::delete('/task/{task}', [TaskController::class, 'delete'])->name('task.delete');


Route::get('/', function () {
    return view('pages.home');
});

Route::get('/contact', function () {
    return view('pages.contact');
});

Route::get('/login', function () {
    return view('pages.login');
})->name('login');


