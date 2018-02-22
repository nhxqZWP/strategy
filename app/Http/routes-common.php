<?php

Route::group([], function() {
    Route::get('/login', ['uses'=>'LoginController@getLogin']);
    Route::post('/login', ['uses'=>'LoginController@postLogin']);
    Route::get('/logout', ['uses'=>'LoginController@getLogout']);
    Route::get('/no-auth', ['uses'=>'LoginController@getNoAuth']);
});

Route::group(['middleware'=>'rbac'], function() {
    Route::get('/hotfix', ['uses' => 'Common\HotfixController@getList']);
    Route::get('/hotfix/edit', ['uses' => 'Common\HotfixController@getForm']);
    Route::post('/hotfix/edit', ['uses' => 'Common\HotfixController@postForm']);
    Route::get('/hotfix/status', ['uses' => 'Common\HotfixController@getStatus']);
    Route::get('/hotfix/{id}/delete', ['uses' => 'Common\HotfixController@getDelete']);

    Route::get('/lang', ['uses' => 'Common\LangController@getList']);
    Route::get('/lang/edit', ['uses' => 'Common\LangController@getForm']);
    Route::post('/lang/edit', ['uses' => 'Common\LangController@postForm']);
    Route::get('/lang/status', ['uses' => 'Common\LangController@getStatus']);
    Route::get('/lang/{id}/delete', ['uses' => 'Common\LangController@getDelete']);
});