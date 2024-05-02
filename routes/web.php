<?php

use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {return 'Hello world!';});

Route::post('/users', [UserController::class, 'store']);
Route::post('/transfers', [TransferController::class, 'store']);
