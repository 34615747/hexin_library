<?php
// 审批设置
Route::group([
    'namespace' => 'App\Http\Controllers\Base',
    'prefix' => 'purchase/approval',
    'middleware' => [
        'response',
        'auth',
    ],
], function () {
    Route::any('/get_approval_status', "ApprovalConfigController@getApprovalStatus");
    Route::post('/edit_approval_status', "ApprovalConfigController@editApprovalStatus");

    Route::get('/get_approval_config', "ApprovalConfigController@getApprovalConfig");
    Route::post('/edit_approval_config', "ApprovalConfigController@editApprovalConfig")->middleware('transaction');
});

