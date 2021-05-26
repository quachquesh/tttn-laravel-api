<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->group(function () {
    // // check quyền admin
    // Route::middleware(['auth:api-lecturer', 'scopes:admin'])->group(function () {
    //     Route::prefix('student')->group(function () {
    //         Route::post('', 'StudentController@store');
    //         Route::post('create-list', 'StudentController@createList');
    //         Route::patch('lock/{id}', 'StudentController@lockAccount')->where('id', '[0-9]+');
    //         Route::patch('unlock/{id}', 'StudentController@unlockAccount')->where('id', '[0-9]+');
    //         Route::put('{id}', 'StudentController@update')->where('id', '[0-9]+');
    //     });

    //     Route::prefix('lecturer')->group(function () {
    //         Route::post('', 'LecturerController@store');
    //     });

    //     Route::prefix('subject')->group(function () {
    //         Route::post('', 'SubjectController@store');
    //     });
    // });

    // Có quyền gv, admin mới được
    // Route::middleware(['auth:api-lecturer', 'scope:gv,admin'])->group(function () {
    //     // student
    //     Route::prefix('student')->group(function () {
    //         Route::get('', 'StudentController@index');
    //     });

    //     // Môn học
    //     Route::prefix('subject')->group(function () {
    //         Route::get('', "SubjectController@index");
    //         Route::get('/user', "SubjectController@showByUserId");
    //     });
    //     // Lớp học
    //     Route::prefix('class-subject')->group(function () {
    //         Route::post('', 'ClassSubjectController@store');
    //         Route::get('/subject/{id}', 'ClassSubjectController@showBySubject')->where('id', '[0-9]+');
    //     });
    //     // Thành viên lớp
    //     Route::prefix('class-member')->group(function () {
    //         Route::post('', "ClassMemberController@store");
    //         Route::get('{id}', "ClassMemberController@index");
    //         Route::delete('{id}', "ClassMemberController@destroy");
    //     });
    // });


    Route::prefix('user')->group(function () {
        Route::get("details", "UserController@details");
        Route::post("login", "UserController@login");
        Route::get("logout", "UserController@logout");
    });

    // student
    Route::prefix('student')->group(function () {
        Route::middleware(['auth:api-student', 'scopes:sv'])->group(function () {
            Route::get('details', 'StudentController@details');
            Route::get('logout', 'StudentController@logout');
        });
        // check quyền admin
        Route::middleware(['auth:api-lecturer', 'scopes:admin'])->group(function () {
            Route::post('', 'StudentController@store');
            Route::post('create-list', 'StudentController@createList');
            Route::patch('lock/{id}', 'StudentController@lockAccount')->where('id', '[0-9]+');
            Route::patch('unlock/{id}', 'StudentController@unlockAccount')->where('id', '[0-9]+');
            Route::put('{id}', 'StudentController@update')->where('id', '[0-9]+');
        });

        // Có quyền gv, admin mới được
        Route::middleware(['auth:api-lecturer', 'scope:gv,admin'])->group(function () {
            Route::get('', 'StudentController@index');
        });
    });

    // lecturer
    Route::prefix('lecturer')->group(function () {
        Route::middleware(['auth:api-lecturer', 'scope:gv,admin'])->group(function () {
            Route::get('details', 'LecturerController@details');
            Route::get('logout', 'LecturerController@logout');
        });

        // quyền admin
        Route::middleware(['auth:api-lecturer', 'scopes:admin'])->group(function () {
            Route::post('', 'LecturerController@store');
        });
        // Route::post('', 'LecturerController@store');
    });

    // Môn học
    Route::prefix('subject')->group(function () {
        Route::get('/{id}', "SubjectController@show")->where('id', '[0-9]+');

        // admin
        Route::middleware(['auth:api-lecturer', 'scopes:admin'])->group(function () {
            Route::post('', 'SubjectController@store');
        });

        // gv, admin
        Route::middleware(['auth:api-lecturer', 'scope:gv,admin'])->group(function () {
            Route::get('', "SubjectController@index");
            Route::get('/all', "SubjectController@showAll");
            Route::get('/user', "SubjectController@showByUserId");
        });

    });

    // Lớp học
    Route::prefix('class-subject')->group(function () {
        Route::get('/user', 'ClassSubjectController@index');
        Route::get('/{id}', 'ClassSubjectController@show')->where('id', '[0-9]+');
        Route::get('/all-info/{id}', 'ClassSubjectController@allInfo')->where('id', '[0-9]+');

        // admin
        Route::middleware(['auth:api-lecturer', 'scopes:admin'])->group(function () {

        });

        // gv, admin
        Route::middleware(['auth:api-lecturer', 'scope:gv,admin'])->group(function () {
            Route::post('', 'ClassSubjectController@store');
            Route::get('', 'ClassSubjectController@getAllClass');
            Route::put('/{id}', 'ClassSubjectController@update')->where('id', '[0-9]+');
            Route::get('/subject/{id}', 'ClassSubjectController@showBySubject')->where('id', '[0-9]+');
        });

        // group
        Route::get('/{id}/group', 'GroupController@getMyGroup')->where('id', '[0-9]+');
        Route::get('/{id}/groups', 'GroupController@index')->where('id', '[0-9]+');
        Route::post('/{id}/groups/{type}', 'GroupController@store')->where(['id' => '[0-9]+','type' => '[0-9]']);
        // ticket group
        Route::get('/{id}/ticket-groups', 'GroupController@getTickets')->where('id', '[0-9]+');
        Route::post('/{id}/ticket-groups/{type}', 'GroupController@createTicket')->where(['id' => '[0-9]+','type' => '[0-9]']);
        Route::put('/{id}/ticket-groups/{ticket_id}', 'GroupController@updateTicket')->where(['id' => '[0-9]+','ticket_id' => '[0-9]+']);
        Route::delete('/{id}/ticket-groups/{ticket_id}', 'GroupController@destroyTicket')->where(['id' => '[0-9]+','ticket_id' => '[0-9]+']);
    });

    // Thành viên lớp
    Route::prefix('class-member')->group(function () {
        Route::get('{class_id}', "ClassMemberController@index")->where('class_id', '[0-9]+');

        // admin
        Route::middleware(['auth:api-lecturer', 'scopes:admin'])->group(function () {

        });

        // gv, admin
        Route::middleware(['auth:api-lecturer', 'scope:gv,admin'])->group(function () {
            Route::post('', "ClassMemberController@store");
            // Route::get('{class_id}', "ClassMemberController@index")->where('class_id', '[0-9]+');
            Route::delete('{class_id}', "ClassMemberController@destroy")->where('class_id', '[0-9]+');
            Route::post("list-member", "ClassMemberController@destroyList");
        });
    });

    Route::prefix('notify')->group(function () {
        // notify
        Route::post('{class_id}', "NotifyController@store")->where('class_id', '[0-9]+');
        Route::put('{notify_id}', "NotifyController@update")->where('notify_id', '[0-9]+');
        Route::delete('{class_id}/{notify_id}', "NotifyController@destroy")->where(['class_id' => '[0-9]+', 'notify_id' => '[0-9]+']);
        Route::get("/listMember/{notify_id}", "NotifyController@getNotifyToMember")->where(["notify_id" => "[0-9]+"]);

        // reply
        Route::post('{notify_id}/reply', "NotifyController@replyStore")->where('notify_id', '[0-9]+');
        Route::put('{class_id}/reply/{reply_id}', "NotifyController@replyUpdate")->where(['class_id' => '[0-9]+','reply_id' => '[0-9]+']);
        Route::delete('{class_id}/reply/{reply_id}', "NotifyController@replyDestroy")->where(['class_id' => '[0-9]+','reply_id' => '[0-9]+']);
    });

    Route::get('download', "DownloadController@download");
});
