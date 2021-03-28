<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->group(function () {
    // admin
    Route::middleware(['auth:api-lecturer', 'scopes:admin'])->group(function () {
        Route::prefix('student')->group(function () {
            Route::get('', 'StudentController@index');
            Route::post('', 'StudentController@store');
            Route::post('create-list', 'StudentController@createList');
            Route::patch('lock', 'StudentController@lockAccount');
            Route::patch('unlock', 'StudentController@unlockAccount');
            Route::put('{id}', 'StudentController@update');
        });

        Route::prefix('lecturer')->group(function () {
            Route::post('', 'LecturerController@store');
        });
    });
    // student
    Route::prefix('student')->group(function () {
        Route::post('login', 'StudentController@login');
        Route::middleware(['auth:api-student'])->group(function () {
            Route::get('details', 'StudentController@details');
            Route::get('logout', 'StudentController@logout');
        });
    });
    // lecturer
    Route::prefix('lecturer')->group(function () {
        Route::post('login', 'LecturerController@login');
        Route::middleware(['auth:api-lecturer', 'scope:gv,admin'])->group(function () {
            Route::get('details', 'LecturerController@details');
            Route::get('logout', 'LecturerController@logout');
        });
    });
});
