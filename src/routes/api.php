<?php
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->group(['prefix' => 'admin'], function ($api) {
        $api->resource("/comments", "VCComponent\Laravel\Comment\Http\Controllers\Api\Admin\CommentController");
        $api->put("/comments/status/bulk", "VCComponent\Laravel\Comment\Http\Controllers\Api\Admin\CommentController@bulkUpdateStatus");
        $api->put("/comments/status/{id}", "VCComponent\Laravel\Comment\Http\Controllers\Api\Admin\CommentController@updateStatus");
    });
    $api->get("/comments", "VCComponent\Laravel\Comment\Http\Controllers\Api\Fontend\CommentController@index");
    $api->get("/comments/{id}", "VCComponent\Laravel\Comment\Http\Controllers\Api\Fontend\CommentController@show");
    $api->post("/comments", "VCComponent\Laravel\Comment\Http\Controllers\Api\Fontend\CommentController@store");
    $api->put('/comments/report/{id}', 'VCComponent\Laravel\Comment\Http\Controllers\Api\Fontend\CommentController@report');

});
