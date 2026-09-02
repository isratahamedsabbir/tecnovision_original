<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialController;

Route::controller(SocialController::class)->prefix('admin/social')->name('admin.social.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
})->middleware(['auth', 'admin', 'prevent-back-history']);
