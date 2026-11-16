<?php

use App\NativeComponents\Home;
use App\NativeComponents\Learn;
use App\NativeComponents\Study;
use App\NativeComponents\Words;
use App\NativeLayouts\AppLayout;
use Illuminate\Support\Facades\Route;

Route::native('/', Home::class)->layout(AppLayout::class)->name('home');
Route::native('/study', Study::class)->layout(AppLayout::class)->name('study');
Route::native('/words', Words::class)->layout(AppLayout::class)->name('words');
Route::native('/learn', Learn::class)->name('learn');
