<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ImportsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;




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
    Route::patch('/task/{task}/updateStatus', [TaskController::class, 'updateStatus'])->name('task.updateStatus');




    Route::get('/download/{filename}', [ProjectController::class, 'downloadAttachment'])->name('project.download');
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



Route::get('/dashboard', function () {
    return view('pages.home');
})->name('dashboard');


require __DIR__.'/auth.php';










Route::resource('projects', ProjectController::class)->middleware('auth');


Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('project.show');

Route::middleware([AdminMiddleware::class])->group(function () {
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('project.create');
    Route::post('/projects/store', [ProjectController::class, 'store'])->name('project.store');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('project.destroy');
    Route::put('/projects/{project}/update', [ProjectController::class, 'update'])->name('project.update');
    Route::get('/projects/{project}/edit-data', [ProjectController::class, 'getProject'])->name('project.getData');


});
Route::get('/projects', [ProjectController::class, 'index'])->name('project');





Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('project.show');


Route::middleware(['auth'])->group(function () {
    Route::post('/messages/{id}/reply', [MessageController::class, 'reply'])->name('messages.reply');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
});







// Hlavná stránka dashboardu
Route::get('/dashboard1', function () {
    return view('dashboards.dashboard1'); // Toto bude náš frontend
});
Route::get('/api/dashboard/summary', [DashboardController::class, 'summary']);
Route::get('/api/dashboard/created-vs-finalized', [DashboardController::class, 'createdVsFinalized']);
Route::get('/api/dashboard/filters', [DashboardController::class, 'filters']);

Route::get('/dashboard2', function () {
    return view('dashboards.dashboard2');
});
Route::get('/api/dashboard/monthly-summary', [DashboardController::class, 'monthlySummary']);
Route::get('/api/dashboard/backlog-table', [DashboardController::class, 'backlogTable']);
Route::get('/api/map-data', [DashboardController::class, 'mapData']);
Route::get('/dashboard3', function () {
    return view('dashboards.dashboard3');
});


Route::get('/api/dashboard/filters', [DashboardController::class, 'filtersCountry']);

Route::get('/api/countries', [DashboardController::class, 'getCountries']);
Route::get('/api/dashboard-stats', [DashboardController::class, 'getStats']);


Route::get('/api/dashboard/snapshot', [DashboardController::class, 'snapshotForMonth']);

Route::get('/dashboard4', function () {
    return view('dashboards.dashboard4');
});

Route::get('/api/dashboard/region-snapshot-latest', [\App\Http\Controllers\DashboardController::class, 'regionSnapshotLatest']);
Route::get('/api/dashboard/best-countries-latest', [DashboardController::class, 'bestCountriesLatest']);





Route::middleware(['auth'])->group(function () {
    //USERI
    Route::post('/screenshots', [ScreenshotController::class, 'store'])->name('screenshots.store');
    Route::get('/screenshots', [ScreenshotController::class, 'index'])->name('screenshots.index');
    Route::delete('/screenshots/{id}', [ScreenshotController::class, 'destroy'])->name('screenshots.destroy');
});







Route::middleware([AdminMiddleware::class])->group(function () {
    //ADMINI
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/toggle-role', [UserController::class, 'toggleRole'])->name('users.toggleRole');
    Route::get('/upload', [UploadController::class, 'showForm'])->name('upload.form');
    Route::post('/upload', [UploadController::class, 'upload'])->name('upload.process');
    Route::get('/imports', [ImportsController::class, 'index'])->name('imports.index');
    Route::delete('/imports/{id}', [ImportsController::class, 'destroy'])->name('imports.destroy');
    Route::get('/imports/{id}', [ImportsController::class, 'show'])->name('imports.show');
    Route::get('/imports/{id}/download', [ImportsController::class, 'download'])->name('imports.download');
});
