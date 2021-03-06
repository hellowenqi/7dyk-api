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

//use Session;
//use Config;

Route::get('/', 'WelcomeController@index');
Route::get('/test', function(){
    var_dump($_COOKIE["laravel_session"]);
});
Route::get('home', 'HomeController@index');
Route::post('question', 'QuestionController@index');
//手机端API
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
                Route::post('editusernow', 'UserController@editUsernow');
                Route::any('beteacher', 'UserController@beTeacher');
            });
            Route::group(['prefix' => 'answer'], function() {
                Route::any('answer', 'AnswerController@answer');
                Route::any('listen', 'AnswerController@listen');
            });
            Route::group(['prefix' => 'history'], function() {
                Route::get('/', 'HistoryController@index');
                Route::post('delete', 'HistoryController@destroy');
            });
            Route::get('hot', 'HistoryController@hotList');
        Route::group(['prefix' => 'course'], function() {
            Route::get('userInfo', 'CourseController@userInfo');
            Route::get('chapterList', 'CourseController@chapterList');
            Route::get('pay', 'CourseController@pay');
            Route::get('{id}', 'CourseController@info');
            Route::get('chapterInfo/{id}', 'CourseController@chapterInfo');
            Route::get('chapterMark/{id}', 'CourseController@chapterMark');
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
      Route::group(['middleware' => 'adminauth'],function(){
        //Route::group([],function(){
            Route::group(['prefix' => 'question'], function() {
                Route::get('/', 'QuestionController@getList');
                Route::post('setQuestionOrder', 'QuestionController@setQuestionOrder');
                Route::post('setVirtualValue', 'QuestionController@setVirtualValue');
                Route::post('questionModify', 'QuestionController@questionModify');
            });
            Route::group(['prefix' => 'teachers'], function() {
                Route::get('/', 'TeacherController@getList');
                Route::post('teacherModify', 'TeacherController@teacherModify');
                Route::post('deleteTeacher', 'TeacherController@deleteTeacher');
            });
            Route::group(['prefix' => 'user'], function() {
                Route::get('/', 'UserController@getList');
                Route::post('generateInvite', 'UserController@generateInvite');
                Route::post('generateAnonymousInvite', 'UserController@generateAnonymousInvite');
                Route::get('userInfo', 'UserController@userInfo');
            });
            Route::group(['prefix' => 'history'], function(){
                Route::get('/', 'HistoryController@index');
            });
            Route::group(['prefix' => 'hot'], function(){
                Route::get('/', 'HotController@index');
                Route::post('add', 'HotController@add');
                Route::post('delete', 'HotController@destroy');
                Route::post('update', 'HotController@update');
            });
            //图片的存储和列表显示
            Route::group(['prefix' => 'pic'], function(){
                Route::get('/', 'PictureController@index');
                Route::post('upload', 'PictureController@upload');
                Route::post('delete', 'PictureController@delete');
            });
            //课程
            // admin/v1/course
            Route::group(['prefix' => 'course'], function() {
                Route::get('/', 'CourseController@courseList');
                Route::post('create', 'CourseController@create');
                Route::post('delete', 'CourseController@delete');
                Route::post('update', 'CourseController@update');
                Route::get('info', 'CourseController@info');
                Route::get('affordList', 'CourseController@affordList');
                Route::post("uploadAudio", 'CourseController@uploadAudio');
                Route::any("richEditor", 'CourseController@richEditor');

                Route::get('chapter', 'CourseController@chapter');
                Route::post('chapter/create', 'CourseController@chapterCreate');
                Route::post('chapter/delete/{id}', 'CourseController@chapterDelete');
                Route::post('chapter/update/{id}', 'CourseController@chapterUpdate');
                Route::get('chapter/info/{id}', 'CourseController@chapterInfo');
                Route::post('chapter/previewUpdate', 'CourseController@previewUpdate');
            });
        });
        Route::group(['prefix' => 'login'], function() {
            Route::post('/', 'LoginController@login');
            Route::get('code','LoginController@code');
        });
        Route::get('logout',function(){
            Session::forget('adminId');
            return Redirect::to(Config::get('urls.adminLogin'), 301);
        });
        Route::get('course/chapter/preview', 'CourseController@preview'); //手机预览, 不鉴权
    });
});

//手机和后台管理员公共接口
Route::get('audio/{name}', 'CommonController@audio');  //音频接口

//内部接口
Route::group(['prefix' => 'timer'], function() {
    Route::group(['middleware' => 'innerauth'],function() {
        Route::get('checkExpired', 'TimerController@checkExpired');
        Route::get('getUserInfo', 'TimerController@getUserInfo');
        Route::get('createNonceStr', 'TimerController@createNonceStr');
        Route::get('getToken', 'TimerController@getToken');
    });
});

