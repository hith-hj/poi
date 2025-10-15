<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class,'map'])->name('home');
Route::get('/points', [HomeController::class,'getPoints'])->name('points');
