<?php

use App\Livewire\Home;
use App\Livewire\Learn;
use App\Livewire\Settings;
use App\Livewire\Study;
use App\Livewire\Words;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/learn', Learn::class)->name('learn');
Route::get('/study', Study::class)->name('study');
Route::get('/words', Words::class)->name('words');
Route::get('/settings', Settings::class)->name('settings');
