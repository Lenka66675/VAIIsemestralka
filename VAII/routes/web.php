<?php

use App\Http\Controllers\TaskController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});









//::get('/task', [TaskController::class, 'index'])->name('task.index');
Route::middleware(['auth'])->group(function () {
    Route::get('/tasks', [TaskController::class, 'tasks'])->name('task.tasks');


    Route::get('task/create', [TaskController::class, 'create'])->name('task.create');
    Route::post('/task', [TaskController::class, 'post'])->name('task.store');
    Route::get('/task/{task}/edit', [TaskController::class, 'edit'])->name('task.edit');
    Route::put('/task/{task}', [TaskController::class, 'update'])->name('task.update');
    Route::delete('/task/{task}', [TaskController::class, 'delete'])->name('task.delete');
});

Route::get('/', function () {
    return view('pages.home');
})->name('home');;

Route::get('/contact', function () {
    return view('pages.contact');
});

Route::get('/login', function () {
    return view('pages.login');
})->name('login');


//require __DIR__.'/auth.php';

Route::get('/dashboard', function () {
    return view('dashboard'); // Môžeš zmeniť na iný názov pohľadu, ak ho chceš nazvať inak
})->name('dashboard');



Route::middleware([AdminMiddleware::class])->group(function () {
    Route::get('/admin', function () {
        return 'Welcome, Admin!';
    });
});
require __DIR__.'/auth.php';



