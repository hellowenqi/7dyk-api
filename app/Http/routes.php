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
Route::group(['prefix' => 'api'], function() {
    Route::group(['prefix' => 'v1'], function() {
        Route::group(['middleware' => 'wechatauth'], function() {
//        Route::group([], function() {
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
                Route::any('cancelLike', 'QuestionController@cancelLike');
                Route::any('weight', 'QuestionController@weight');
                Route::any('teacher_question', 'QuestionController@teacher_question');
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
            });
        });
        Route::group(['prefix' => 'answer'], function() {
            Route::any('audio', 'AnswerController@audio');
        });
        Route::any('auth', 'UserController@auth');
        Route::any('code', 'UserController@code');
        Route::any('notify', 'AnswerController@notify');
    });
});
//后台管理员
Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function() {
    Route::group(['prefix' => 'v1'], function() {
        Route::get('/', function(){
            return redirect('http://h5app.7dyk.com/ama/admin/public/index.html');
        });
        Route::get('/login', function(){
            return redirect('http://h5app.7dyk.com/ama/admin/public/login.html');
        });
//        Route::group(['middleware' => 'adminauth'],function(){
        Route::group([],function(){
            Route::group(['prefix' => 'question'], function() {
                Route::get('/', 'QuestionController@getList');
                Route::post('setQuestionOrder', 'QuestionController@setQuestionOrder');
                Route::post('setVirtualValue', 'QuestionController@setVirtualValue');
                Route::post('questionModify', 'QuestionController@questionModify');
            });
            Route::group(['prefix' => 'teachers'], function() {
                Route::get('/', 'TeacherController@getList');
                Route::post('teacherModify', 'TeacherController@teacherModify');
            });
            Route::group(['prefix' => 'user'], function() {
                Route::post('generateInvite', 'UserController@generateInvite');
                Route::post('generateAnonymousInvite', 'UserController@generateAnonymousInvite');
            });
        });
        Route::group(['prefix' => 'login'], function() {
            Route::post('/', 'LoginController@login');
            Route::get('/code','LoginController@code');
        });
    });
});
//定时器
Route::group(['prefix' => 'timer'], function() {
    Route::get('checkExpired', 'TimerController@checkExpired');
    Route::get('getUserInfo', 'TimerController@getUserInfo');
});
