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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
Route::group(['prefix' => 'messages'], function ()  {
	Route::post('/contact_list', 'WebServiceController@contact_list');
	Route::post('/contact_list_web', 'WebServiceController@contact_list_web');
	Route::post('/contact_list_clinic_web', 'WebServiceController@contact_list_clinic_web');
	
	Route::post('/users_chat_list', 'WebServiceController@users_chat_list');
	Route::post('/doctors_chat_list', 'WebServiceController@doctors_chat_list');
	Route::post('/clinics_chat_list', 'WebServiceController@clinics_chat_list');
	Route::post('/doctors_open_chat_list', 'WebServiceController@doctors_open_chat_list');
	Route::post('/clinics_open_chat_list', 'WebServiceController@clinics_open_chat_list');
	
	Route::post('/single_chat', 'WebServiceController@single_chat');
	Route::post('/single_chat_clinic', 'WebServiceController@single_chat_clinic');
	Route::post('/contact_phone', 'WebServiceController@contact_phone');

	Route::post('/open_session', 'WebServiceController@open_session');
	Route::post('/check_session', 'WebServiceController@check_session');
	Route::post('/close_session', 'WebServiceController@close_session');
	
	
});
Route::post('/all_countries', 'WebServiceController@all_countries');
Route::post('/get_country', 'WebServiceController@get_country');
