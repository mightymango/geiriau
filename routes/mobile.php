<?php

use App\NativeComponents\Home;
use App\NativeLayouts\AppLayout;
use Illuminate\Support\Facades\Route;

Route::native('/', Home::class)->layout(AppLayout::class)->name('home');
