<?php

use App\Livewire\Home;
use App\Livewire\Learn;
use App\Livewire\Settings;
use App\Livewire\Study;
use App\Livewire\Words;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Home::class)->name('home');
Route::livewire('/learn', Learn::class)->name('learn');
Route::livewire('/study', Study::class)->name('study');
Route::livewire('/words', Words::class)->name('words');
Route::livewire('/settings', Settings::class)->name('settings');
