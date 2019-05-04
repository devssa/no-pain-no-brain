<?php

use Illuminate\Http\Request;

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

/*
 * Intercepta o método HTTP, verificando se está autenticado ou não
 */

Route::post('oauth/token', 'ApiLoginController@login');
Route::group(['namespace' => 'Auth'], function () {
    Route::post('login', 'ApiLoginController@login')->name('login');
});

Route::post('/cadastro', 'UserController@store')->name('user.signup');

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/usuario/listar', 'UserController@index')->name('user.index')->middleware('scope:Administrador');
    Route::get('/usuario/{id}', 'UserController@show')->name('user.show')->middleware('scope:Administrador');
    Route::put('/usuario/{id}/atualizar', 'UserController@update')->name('user.update')->middleware('scope:Administrador');
    Route::put('/usuario/{id}/atualizar-status', 'UserController@userStatus')->name('user.status')->middleware('scope:Administrador');
    Route::delete('/usuario/{id}/deletar', 'UserController@destroy')->name('user.destroy')->middleware('scope:Administrador');
    Route::get('/post/listar', 'PostController@index')->name('post.index')->middleware('scope:Editor');
    Route::get('/post/{id}', 'PostController@show')->name('post.show')->middleware('scope:Editor');
    Route::post('/post/cadastrar', 'PostController@store')->name('post.store')->middleware('scope:Editor');
    Route::put('/post/{id}/atualizar', 'PostController@update')->name('post.update')->middleware('scope:Editor');
    Route::delete('/post/{id}/deletar', 'PostController@destroy')->name('post.destroy')->middleware('scope:Editor');
});

