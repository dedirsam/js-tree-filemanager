<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FileManagerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/filemanager', [FileManagerController::class, 'index'])->name('filemanager');
    Route::get('/filemanager/tree', [FileManagerController::class, 'getTree']);
    Route::get('/filemanager/tree', [FileManagerController::class, 'tree'])->name('filemanager.tree');

    Route::get('/filemanager/browse/{path?}', [FileManagerController::class, 'browse'])->where('path', '.*')->name('filemanager.browse');

    Route::post('/filemanager/create-folder', [FileManagerController::class, 'createFolder'])->name('filemanager.createFolder');

    Route::post('/filemanager/folder/delete', [FileManagerController::class, 'deleteFolder'])->name('filemanager.deleteFolder');


    Route::post('/filemanager/rename-folder', [FileManagerController::class, 'renameFolder'])->name('filemanager.renameFolder');

    Route::post('/filemanager/file/upload', [FileManagerController::class, 'upload']);
    Route::delete('/filemanager/file/delete', [FileManagerController::class, 'deleteFile']);
});

require __DIR__ . '/auth.php';
