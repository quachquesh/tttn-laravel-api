<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->group(function () {
    // check quyền admin
    Route::middleware(['auth:api-lecturer', 'scopes:admin'])->group(function () {
        Route::prefix('student')->group(function () {
            Route::post('', 'StudentController@store');
            Route::post('create-list', 'StudentController@createList');
            Route::patch('lock', 'StudentController@lockAccount');
            Route::patch('unlock', 'StudentController@unlockAccount');
            Route::put('{id}', 'StudentController@update')->where('id', '[0-9]+');
        });

        Route::prefix('lecturer')->group(function () {
            Route::post('', 'LecturerController@store');
        });
    });

    // Có quyền gv, admin mới được
    Route::middleware(['auth:api-lecturer', 'scope:gv,admin'])->group(function () {
        // student
        Route::prefix('student')->group(function () {
            Route::get('', 'StudentController@index');
        });

        // Môn học
        Route::prefix('subject')->group(function () {
            Route::post('', 'SubjectController@store');
            Route::get('', "SubjectController@index");
            Route::get('/user', "SubjectController@showByUserId");
        });
        // Lớp học
        Route::prefix('class-subject')->group(function () {
            Route::post('', 'ClassSubjectController@store');
            Route::get('/subject/{id}', 'ClassSubjectController@showBySubject')->where('id', '[0-9]+');
        });
        // Thành viên lớp
        Route::prefix('class-member')->group(function () {
            Route::post('', "ClassMemberController@store");
            Route::get('{id}', "ClassMemberController@index");
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

    // Môn học
    Route::prefix('subject')->group(function () {
        Route::get('/{id}', "SubjectController@show")->where('id', '[0-9]+');
    });
    // Lớp học
    Route::prefix('class-subject')->group(function () {
        Route::middleware(['auth:api-student'])->group(function () {
            Route::get('/user', 'ClassSubjectController@index');
        });
        Route::get('/{id}', 'ClassSubjectController@show')->where('id', '[0-9]+');
        Route::get('/all-info/{id}', 'ClassSubjectController@allInfo')->where('id', '[0-9]+');
    });
    Route::prefix('class-member')->group(function () {
        Route::get('/{id}', "ClassMemberController@index")->where('id', '[0-9]+');
    });
});
