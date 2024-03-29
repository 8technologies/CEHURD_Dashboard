<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiEnterprisesController;
use App\Http\Controllers\ApiPostsController;
use App\Http\Controllers\ApiPublicController;
use App\Models\Location;
use App\Models\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::POST("users/register", [ApiPublicController::class, "register"]);
Route::POST("users/login", [ApiPublicController::class, "login"]);
Route::POST("users/send-code", [ApiPublicController::class, "sendCode"]);
Route::POST("users/change-password", [ApiPublicController::class, "changePassword"]);
Route::get("test", function () {
    die("Romina test");
});

Route::group(['middleware' => 'api'], function ($router) {
    Route::get("users/me", [ApiAuthController::class, 'me']);

    //enterprises
    Route::post("enterprises", [ApiEnterprisesController::class, 'create']);
    Route::get("enterprises", [ApiEnterprisesController::class, 'index']);
    Route::post("enterprises-select", [ApiEnterprisesController::class, 'select']);

    //posts
    Route::post("post-media-upload", [ApiPostsController::class, 'upload_media']);
    Route::post("post", [ApiPostsController::class, 'create_post']);
    Route::post("users-update", [ApiPostsController::class, 'users_update']);
    Route::post("password-update", [ApiPostsController::class, 'password_update']);
    Route::post("activities", [ApiPostsController::class, 'create_activity']);
    Route::get("activities", [ApiPostsController::class, 'activities']);
});

Route::get("categories", [ApiPostsController::class, 'categories']);
Route::get("cases", [ApiPostsController::class, 'index']);
Route::get('process-pending-images', [ApiPostsController::class, 'process_pending_images']);
Route::get('locations', function (){

    $data = [];
    foreach (Location::All() as $v) {
        $d['id'] = $v->id;
        $d['parent'] = $v->parent;
        $d['name_text'] = $v->name_text;
        $data[] = $d;
    }

    return Utils::response([
        'code' => 1,
        'message' => "Success.",
        'data' => $data
    ]);
});


Route::get('ajax', function (Request $r) {

    $_model = trim($r->get('model'));
    $conditions = [];
    foreach ($_GET as $key => $v) {
        if (substr($key, 0, 6) != 'query_') {
            continue;
        }
        $_key = str_replace('query_', "", $key);
        $conditions[$_key] = $v;
    }

    if (strlen($_model) < 2) {
        return [
            'data' => []
        ];
    }

    $model = "App\Models\\" . $_model;
    $search_by_1 = trim($r->get('search_by_1'));
    $search_by_2 = trim($r->get('search_by_2'));

    $q = trim($r->get('q'));

    $res_1 = $model::where(
        $search_by_1,
        'like',
        "%$q%"
    )
        ->where($conditions)
        ->limit(20)->get();
    $res_2 = [];

    if ((count($res_1) < 20) && (strlen($search_by_2) > 1)) {
        $res_2 = $model::where(
            $search_by_2,
            'like',
            "%$q%"
        )
            ->where($conditions)
            ->limit(20)->get();
    }

    $data = [];
    foreach ($res_1 as $key => $v) {
        $name = "";
        if (isset($v->$search_by_1)) {
            $name = " - " . $v->$search_by_1;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id" . $name
        ];
    }
    foreach ($res_2 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name = " - " . $v->$search_by_1;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id" . $name
        ];
    }

    return [
        'data' => $data
    ];
});
