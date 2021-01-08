<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** 登入認證 */
Route::group(['prefix' => 'auth', 'as' => 'auth.', 'middleware' => ['api.log']], function () {
    /** 登入 */
    Route::post('/login', 'AuthController@login')->name('login');
    /** 登出 */
    Route::post('/logout', 'AuthController@logout')->name('logout')->middleware('api.auth');
    /** 更換Token */
    Route::post('/refresh', 'AuthController@refresh')->name('refresh')->middleware('api.auth');
});

/** 驗證 */
Route::group(['prefix' => 'verify', 'as' => 'verify.', 'middleware' => ['api.log', 'api.auth']], function () {
    /** 註冊驗證 */
    Route::post('/registration', 'VerifyController@registration')->name('registration');
    /** 寄發驗證碼 */
    Route::post('/registration/send', 'VerifyController@sendRegistration')->name('registration.send');
});

/** 密碼 */
Route::group(['prefix' => 'password', 'as' => 'password.', 'middleware' => ['api.log']], function () {
    /** 忘記密碼 */
    Route::post('/forgot', 'PasswordController@forgot')->name('forgot');
    /** 重設密碼 */
    Route::post('/reset', 'PasswordController@reset')->name('reset')->middleware('api.auth');
});

/** 使用者 */
Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
    /** 取得登入資訊 */
    Route::get('/info', 'UserController@getInfo')->name('info')->middleware('api.auth');
    /** 更新個人資訊 */
    Route::post('/info', 'UserController@setInfo')->name('info.set')->middleware(['api.log', 'api.auth']);
    /** 註冊 */
    Route::post('/register', 'RegisterController@register')->name('register')->middleware('api.log');
});

/** 文章 */
Route::group(['prefix' => 'post', 'as' => 'post.', 'middleware' => ['api.auth']], function () {
    /** 搜尋使用者文章 */
    Route::get('/search', 'PostController@searchPosts')->name('search');
    /** 特定文章 */
    Route::get('/{post_id}', 'PostController@show')->name('show');
    /** PO文 */
    Route::post('/', 'PostController@store')->name('store')->middleware('api.log');
    /** 按讚 */
    Route::patch('/{post_id}/like', 'PostController@like')->name('like')->middleware('api.log');
    /** 取消讚 */
    Route::patch('/{post_id}/dislike', 'PostController@dislike')->name('dislike')->middleware('api.log');
});
