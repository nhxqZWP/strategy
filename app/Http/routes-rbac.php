<?php

Route::group(['namespace' => 'Rbac', 'middleware' => 'rbac'], function () {

    Route::get('/operator', ['uses' => 'AdminOperatorController@getList']);
    Route::get('/operator/edit', ['uses' => 'AdminOperatorController@getForm']);
    Route::post('/operator/edit', ['uses' => 'AdminOperatorController@postForm']);
    Route::get('/operator/delete', ['uses' => 'AdminOperatorController@getDelete']);

    Route::get('/operator/password', ['uses' => 'AdminOperatorController@getPassword']);
    Route::post('/operator/password', ['uses' => 'AdminOperatorController@getPassword']);

    Route::get('/role', ['uses' => 'AdminRoleController@getList']);
    Route::get('/role/edit', ['uses' => 'AdminRoleController@getForm']);
    Route::post('/role/edit', ['uses' => 'AdminRoleController@postForm']);
    Route::get('/role/delete', ['uses' => 'AdminRoleController@getDelete']);

    Route::get('/module', ['uses' => 'AdminModuleController@getList']);
    Route::get('/module/edit', ['uses' => 'AdminModuleController@getForm']);
    Route::post('/module/edit', ['uses' => 'AdminModuleController@postForm']);
    Route::get('/module/delete', ['uses' => 'AdminModuleController@getDelete']);
});