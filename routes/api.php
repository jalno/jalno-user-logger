<?php

use Jalno\UserLogger\Http\Controllers\LogsController;
use Illuminate\Support\Facades\Route;

Route::prefix("v1")->middleware(["api", "auth"])->group(function () {
	Route::apiResource("logs", LogsController::class)->except(['store', 'update']);
});