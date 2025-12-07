<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // 1. Dashboard
    Route::get('/dashboard', [PanelController::class, 'index'])->name('dashboard');

    //Export excel
    Route::get('/panels/export', [PanelController::class, 'export'])->name('panels.export');

    //Import
    Route::post('/panels/import', [PanelController::class, 'import'])->name('panels.import');

    // 2. Resource CRUD (Create, Store, Edit, Update, Destroy)
    Route::resource('panels', PanelController::class);
    Route::resource('panels', PanelController::class)->except(['index']); 
    
    // 3. Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';