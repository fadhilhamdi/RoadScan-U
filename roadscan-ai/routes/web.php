<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SegmentationController;

Route::get('/', function () {
    return view('segmentasi'); // ⬅️ SAMAKAN DENGAN VIEW
});

Route::post('/process', [SegmentationController::class, 'process']);