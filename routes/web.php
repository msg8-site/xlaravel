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

Route::get('/', function () {
    // return view('welcome');
    return 'ok';
});

Route::get('index', 'IndexController@index');
Route::get('iframeheader', 'IndexController@iframeheader');
Route::get('ajax_heartbeat', 'IndexController@ajax_heartbeat');
Route::get('iframemenu', 'IndexController@iframemenu');
Route::get('iframebody', 'IndexController@iframebody');

Route::get('login', 'LoginController@login');
Route::post('login_exec', 'LoginController@login_exec');
Route::post('logout', 'LoginController@logout');
Route::get('imagecode', 'LoginController@imgcodecreate');  //登录页图形验证码

Route::get('passwordchange', 'IndexController@passwordchange');
Route::post('passwordchange_exec', 'IndexController@passwordchange_exec');

Route::get('googlecodebind', 'IndexController@googlecodebind');
Route::post('googlecodebind_exec', 'IndexController@googlecodebind_exec');

Route::get('user_index', 'UserController@index');
Route::get('user_index_tabledata', 'UserController@index_tabledata');
Route::get('user_add', 'UserController@add');
Route::post('user_add_exec', 'UserController@add_exec');
Route::get('user_update', 'UserController@update');
Route::post('user_update_exec', 'UserController@update_exec');
Route::post('user_delete', 'UserController@delete');

Route::get('node_index', 'NodeController@index');
Route::get('node_index_tabledata', 'NodeController@index_tabledata');
Route::post('node_update_exec', 'NodeController@update_exec');

Route::get('menu_index', 'MenuController@index');
Route::get('menu_index_tabledata', 'MenuController@index_tabledata');
Route::get('menu_add', 'MenuController@add');
Route::post('menu_add_exec', 'MenuController@add_exec');
Route::get('menu_update', 'MenuController@update');
Route::post('menu_update_exec', 'MenuController@update_exec');
Route::post('menu_delete', 'MenuController@delete');

Route::get('role_index', 'RoleController@index');
Route::get('role_index_tabledata', 'RoleController@index_tabledata');
Route::get('role_add', 'RoleController@add');
Route::post('role_add_exec', 'RoleController@add_exec');
Route::get('role_update', 'RoleController@update');
Route::post('role_update_exec', 'RoleController@update_exec');
Route::post('role_delete', 'RoleController@delete');

Route::get('loguserlogin_index', 'LoguserloginController@index');
Route::get('loguserlogin_index_tabledata', 'LoguserloginController@index_tabledata');

Route::get('logcodecheck_index', 'LogcodecheckController@index');
Route::get('logcodecheck_index_tabledata', 'LogcodecheckController@index_tabledata');

Route::get('logdatabasecudn_index', 'LogdatabasecudnController@index');
Route::get('logdatabasecudn_index_tabledata', 'LogdatabasecudnController@index_tabledata');


