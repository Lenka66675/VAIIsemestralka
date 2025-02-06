<?php

use App\Http\Controllers\ProjectController;
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
  //  Route::put('/task/{task}', [TaskController::class, 'update'])->name('task.update');
   // Route::delete('/task/{task}', [TaskController::class, 'delete'])->name('task.delete');
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





Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('task.updateStatus');
Route::patch('/task/{task}/updateStatus', [TaskController::class, 'updateStatus'])->name('task.updateStatus');
//Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('task.update');
Route::put('/task/{task}/update', [TaskController::class, 'update'])->name('task.update');

Route::delete('/task/{task}/delete', [TaskController::class, 'delete'])->name('task.delete');


Route::get('/task/{task}', [TaskController::class, 'show']);

Route::patch('/task/{task}/saveSolution', [TaskController::class, 'saveSolution'])->name('task.saveSolution');
Route::get('/task/{task}/getSolution', [TaskController::class, 'getSolution'])->name('task.getSolution');
Route::get('/task/{task}/getAllSolutions', [TaskController::class, 'getAllSolutions'])->name('task.getAllSolutions');

Route::get('/task/{taskId}/download/{fileName}', [TaskController::class, 'downloadFile'])
    ->where('fileName', '.*')
    ->name('task.download');


Route::resource('projects', ProjectController::class)->middleware('auth');




Route::middleware([AdminMiddleware::class])->group(function () {
    Route::get('/projects', [ProjectController::class, 'index'])->name('project'); // Stránka so všetkými projektmi
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('project.create'); // Formulár na vytvorenie
    Route::post('/projects/store', [ProjectController::class, 'store'])->name('project.store'); // Uloženie projektu
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('project.show');

});




Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('project.destroy');
Route::put('/projects/{project}/update', [ProjectController::class, 'update'])->name('project.update');

Route::get('/projects/{project}/edit-data', [ProjectController::class, 'getProject'])->name('project.getData');
Route::get('/download/{filename}', [ProjectController::class, 'downloadAttachment'])->name('project.download');
