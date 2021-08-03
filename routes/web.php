<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/push', 'FirebaseController@push');

Route::get('/', function () {
    return 'Hikkkkkkkkkkkkkkkkk :)';
});

Route::get('/go', function () {
    return 'Hi :)';
});

Route::get('/me', 'UsersController@test');

Route::get('/sms', 'UsersController@sms');

Route::get('/fbase', 'UsersController@fbase');

Route::group(['prefix' => 'api'], function ()  {

    Route::get('/', function () {
        return 'kkkkk';
    });

    Route::group(['prefix' => 'users'], function ()  {
        Route::post('/login', 'UsersController@login');
        Route::post('/register', 'UsersController@register');
        Route::post('/add', 'UsersController@add');
        Route::post('/active', 'UsersController@active');
        Route::post('/edit', 'UsersController@edit');
        Route::post('/me', 'UsersController@me');
        Route::post('/rate_reviews', 'UsersController@rate_reviews');
        Route::post('/doctor_working_hours', 'UsersController@add_doctor_working_hours');
        Route::post('/clinic_working_hours', 'UsersController@add_clinic_working_hours');
        Route::post('/switch_to_doctor', 'UsersController@switch_to_doctor');

        Route::post('/create_pass', 'UsersController@create_pass');
        Route::post('/check_pass', 'UsersController@check_pass');

    });

    Route::group(['prefix' => 'doctors'], function ()  {
        Route::post('/add', 'DoctorsController@add');
        Route::post('/update', 'DoctorsController@update');
        Route::post('/list', 'DoctorsController@all');
        Route::post('/another', 'DoctorsController@another');
        Route::post('/rate', 'DoctorsController@rate');
        Route::post('/rating_list', 'DoctorsController@rating_list');

        Route::post('/change_status', 'DoctorsController@change_status');
        Route::post('/change_speciality', 'DoctorsController@change_speciality');
        Route::post('/change_language', 'DoctorsController@change_language');
        Route::post('/leave_clinic', 'DoctorsController@leave_clinic');
        Route::post('/invite_clinic', 'DoctorsController@invite_clinic');
        

    });

    Route::group(['prefix' => 'clinics'], function ()  {
        Route::post('/add', 'ClinicsController@add');
        Route::post('/get', 'ClinicsController@get');
        Route::post('/edit', 'ClinicsController@edit');
        Route::post('/list', 'ClinicsController@all');
        Route::post('/rate', 'ClinicsController@rate');
        Route::post('/rating_list', 'ClinicsController@rating_list');
        
    });

    Route::group(['prefix' => 'messages'], function ()  {
        Route::post('/send', 'MessagesController@send');
        Route::post('/forward', 'MessagesController@forward');
        Route::post('/delete', 'MessagesController@delete');
        Route::post('/deliver', 'MessagesController@deliver');
        Route::post('/seen', 'MessagesController@seen');
        Route::post('/deliver_web', 'MessagesController@deliver_web');
        Route::post('/seen_web', 'MessagesController@seen_web');
        Route::post('/messages', 'MessagesController@messages');
        // chat lists
        Route::group(['prefix' => 'chat'], function ()  {
            // another
            Route::post('/another', 'MessagesController@another');
            Route::post('/clinic', 'MessagesController@clinic');
            Route::post('/doctor', 'MessagesController@doctor');
            Route::post('/users', 'MessagesController@users');
            Route::post('/all', 'MessagesController@all');
        });
    });

    Route::group(['prefix' => 'messages-clinic'], function ()  {
        Route::post('/send', 'MessagesClinicsController@send');
        Route::post('/forward', 'MessagesClinicsController@forward');
        Route::post('/delete', 'MessagesClinicsController@delete');
        Route::post('/deliver', 'MessagesClinicsController@deliver');
        Route::post('/seen', 'MessagesClinicsController@seen');
        Route::post('/deliver_web', 'MessagesClinicsController@deliver_web');
        Route::post('/seen_web', 'MessagesClinicsController@seen_web');
        Route::post('/messages', 'MessagesClinicsController@messages');
        // chat lists
        Route::group(['prefix' => 'chat'], function ()  {
            // another
            Route::post('/another', 'MessagesController@another');
            Route::post('/clinic', 'MessagesController@clinic');
            Route::post('/doctor', 'MessagesController@doctor');
            Route::post('/users', 'MessagesController@users');
        });
    });

    // requests
    Route::group(['prefix' => 'requests'], function ()  {
        Route::post('/open', 'RequestsController@open');
        Route::post('/close', 'RequestsController@close');
        Route::post('/list', 'RequestsController@all');
    });

    // requests Clinic
    Route::group(['prefix' => 'requests-clinic'], function ()  {
        Route::post('/open', 'RequestsClinicsController@open');
        Route::post('/close', 'RequestsClinicsController@close');
        Route::post('/list', 'RequestsClinicsController@all');
    });

    // langs
    Route::group(['prefix' => 'langs'], function ()  {
        Route::post('/list', 'LangController@all');
    });

    // speciality
    Route::group(['prefix' => 'speciality'], function ()  {
        Route::post('/list', 'SpecialityController@all');
    });

    // Document
    Route::group(['prefix' => 'document'], function ()  {
        Route::post('/add', 'DocumentsController@add');
        Route::post('/list', 'DocumentsController@get');
        Route::post('/privacy', 'DocumentsController@privacy');
        Route::post('/single', 'DocumentsController@single');
        Route::post('/delete', 'DocumentsController@delete');
        Route::post('/update', 'DocumentsController@update');
        Route::post('/search', 'DocumentsController@search');

    });

    // token
    Route::group(['prefix' => 'token'], function ()  {
        Route::post('/add', 'TokenController@add');
    });

    // flag
    Route::group(['prefix' => 'flag'], function ()  {
        Route::post('/is_open', 'FlagController@is_open');
        Route::post('/have_rate', 'FlagController@have_rate');
        Route::post('/is_open_clinics', 'FlagController@is_open_clinics');
        Route::post('/have_rate_clinics', 'FlagController@have_rate_clinics');
        Route::post('/request_id', 'FlagController@request_id');
    });

    // chat-list
    Route::post('/chat-list', 'ChatController@get');

    // uploads
    Route::post('/upload', 'uploadsController@up');

    Route::post('/favourite', 'FavouriteController@add');

    // services
    Route::group(['prefix' => 'service'], function ()  {
        Route::post('/add', 'UsersController@add_service');
        Route::post('/single', 'UsersController@single_service');
        Route::post('/list', 'UsersController@list_service');
        Route::post('/update', 'UsersController@update_service');
        Route::post('/delete', 'UsersController@delete_service');
    });

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

});
