<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@index');

Route::get('home', 'HomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);

Route::group(['prefix' => 'api'], function() {
    Route::group(['prefix' => 'v1'], function() {
        Route::group(['prefix' => 'question'], function() {
            Route::any('test', 'QuestionController@test');
            Route::any('gettopic', 'QuestionController@getTopic');
            Route::any('getquestion', 'QuestionController@getQuestion');
        });
        Route::group(['prefix' => 'user'], function() {
            Route::any('getteacher', 'UserController@getTeacher');
            Route::any('getuserinfo', 'UserController@getUserinfo');
        });
        Route::group(['prefix' => 'user'], function() {
        });
    });
});
