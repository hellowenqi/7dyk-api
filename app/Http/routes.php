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
Route::post('question', 'QuestionController@index');

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);

Route::group(['prefix' => 'api'], function() {
    Route::group(['prefix' => 'v1'], function() {
        //Route::group(['middleware' => 'wechatauth'], function() {
            Route::group(['prefix' => 'question'], function() {
                Route::any('test', 'QuestionController@test');
                Route::any('gettopic', 'QuestionController@getTopic');
                Route::any('getquestion', 'QuestionController@getQuestion');
                Route::any('addquestion', 'QuestionController@addQuestion');
                Route::any('myquestion', 'QuestionController@myQuestion');
                Route::any('myanswer', 'QuestionController@myAnswer');
                Route::any('mylisten', 'QuestionController@myListen');
                Route::any('dislike', 'QuestionController@dislike');
                Route::any('like', 'QuestionController@like');
                Route::any('weight', 'QuestionController@weight');
                Route::any('teacher_question', 'QuestionController@teacher_question');



            });
            Route::group(['prefix' => 'user'], function() {
                Route::any('getteacher', 'UserController@getTeacher');
                Route::any('getuserinfo', 'UserController@getUserinfo');
                Route::any('getusernow', 'UserController@getUsernow');
            });
        //});
        Route::any('auth', 'UserController@auth');
        Route::any('code', 'UserController@code');


        Route::group(['prefix' => 'question'], function() {
            Route::any('test', 'QuestionController@test');
            Route::any('gettopic', 'QuestionController@getTopic');
            Route::any('getquestion', 'QuestionController@getQuestion');
            Route::any('addquestion', 'QuestionController@addQuestion');
            Route::any('myquestion', 'QuestionController@myQuestion');
            Route::any('myanswer', 'QuestionController@myAnswer');
            Route::any('mylisten', 'QuestionController@myListen');


        });
        Route::group(['prefix' => 'user'], function() {
            Route::any('getteacher', 'UserController@getTeacher');
            Route::any('getuserinfo', 'UserController@getUserinfo');
        });
        Route::group(['prefix' => 'answer'], function() {
            Route::any('dislike','AnswerController@dislike');
            Route::any('like','AnswerController@like');
        });


    });
});
