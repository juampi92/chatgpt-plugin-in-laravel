<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('.well-known/ai-plugin.json', fn () => File::get(resource_path('.well-known/ai-plugin.json')));

Route::get('openapi.yaml', fn () => File::get(resource_path('openapi.yaml')));
