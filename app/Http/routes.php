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
/*App::before(function($request)
{
    // Sent by the browser since request come in as cross-site AJAX
    // The cross-site headers are sent via .htaccess
    if ($request->getMethod() == "OPTIONS")
        return new SuccessResponse();
});*/

Route::get('/', 'WelcomeController@index');

Route::get('home', 'HomeController@index');
Route::post('question', 'QuestionController@index');
<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes

Route::group(['prefix' => 'api'], function() {
    Route::group(['prefix' => 'v1'], function() {
        //Route::group(['middleware' => 'wechatauth'], function() {
            Route::group(['prefix' => 'question'], function() {
                Route::any('test', 'QuestionController@test');
                Route::any('gettopic', 'QuestionController@getTopic');
                Route::any('getquestion', 'QuestionController@getQuestion');
                Route::any('addquestion', 'QuestionController@addQuestion');
                Route::any('testquestion', 'QuestionController@testQuestion');
                Route::any('myquestion', 'QuestionController@myQuestion');
                Route::any('myanswer', 'QuestionController@myAnswer');
                Route::any('mylisten', 'QuestionController@myListen');
                Route::any('dislike', 'QuestionController@dislike');
                Route::any('like', 'QuestionController@like');
                Route::any('weight', 'QuestionController@weight');
            });
            Route::group(['prefix' => 'user'], function() {
                Route::any('getteacher', 'UserController@getTeacher');
                Route::any('getteacheranswer', 'UserController@getTeacheranswer');
                Route::any('getuserinfo', 'UserController@getUserinfo');
                Route::any('getusernow', 'UserController@getUsernow');
                Route::any('editusernow', 'UserController@editUsernow');
                Route::any('beteacher', 'UserController@beTeacher');
            });
            Route::group(['prefix' => 'answer'], function() {
                Route::any('answer', 'AnswerController@answer');
                Route::any('listen', 'AnswerController@listen');
                Route::any('prepay', 'AnswerController@prepay');
                Route::any('notify', 'AnswerController@notify');
            });
        //});
        Route::any('auth', 'UserController@auth');
        Route::any('code', 'UserController@code');
    });
});
