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

// ->middleware('cors')

// Route::group(['middleware' => 'auth:api'], function(){

// });

// Route::get('database/{table}', function(Request $request){
// 	return response()->json(DB::select("select * from $request->table"));
// });

Route::get('/welcome', function(){
	return 'Welcome Brother';
});

Route::middleware('cors')->group(function(){

   	Route::get('game', 'GameController@index');

	Route::get('paragraph/{game_id}', 'ParagraphController@show');
	Route::post('paragraph', 'ParagraphController@store');

	Route::get('play/{game_id}/{query}', 'PlayController@show');
	Route::post('play', 'PlayController@store');

	Route::post('facebook/login', 'Auth\LoginController@fbSignUpOrLogIn'); 
	Route::post('login', 'API\UserController@login');
	Route::post('register', 'API\UserController@register');

	Route::get('user', 'API\UserController@user');
	Route::post('update', 'API\UserController@update');
	Route::post('change_password', 'API\UserController@change_password');
	Route::get('logout', 'API\UserController@logout');

});

// HELLO PEOPLE
