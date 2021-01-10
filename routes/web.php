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

// Route::get('/', function () {
//     return 'ok';
// });

//主框架页面
Route::get('index', 'IndexController@index');
Route::get('iframeheader', 'IndexController@iframeheader');
Route::get('ajax_heartbeat', 'IndexController@ajax_heartbeat');
Route::get('iframemenu', 'IndexController@iframemenu');
Route::get('iframebody', 'IndexController@iframebody');

//后台登陆
Route::get('login', 'LoginController@login');
Route::post('login_exec', 'LoginController@login_exec');
Route::post('logout', 'LoginController@logout');
Route::get('imagecode', 'LoginController@imgcodecreate');  //登录页图形验证码

//用户修改密码
Route::get('passwordchange', 'IndexController@passwordchange');
Route::post('passwordchange_exec', 'IndexController@passwordchange_exec');

//用户绑定谷歌动态码
Route::get('googlecodebind', 'IndexController@googlecodebind');
Route::post('googlecodebind_exec', 'IndexController@googlecodebind_exec');

//用户管理
Route::get('user_index', 'UserController@index');
Route::get('user_index_tabledata', 'UserController@index_tabledata');
Route::get('user_add', 'UserController@add');
Route::post('user_add_exec', 'UserController@add_exec');
Route::get('user_update', 'UserController@update');
Route::post('user_update_exec', 'UserController@update_exec');
Route::post('user_delete', 'UserController@delete');

//节点管理
Route::get('node_index', 'NodeController@index');
Route::get('node_index_tabledata', 'NodeController@index_tabledata');
Route::post('node_update_exec', 'NodeController@update_exec');

//菜单管理
Route::get('menu_index', 'MenuController@index');
Route::get('menu_index_tabledata', 'MenuController@index_tabledata');
Route::get('menu_add', 'MenuController@add');
Route::post('menu_add_exec', 'MenuController@add_exec');
Route::get('menu_update', 'MenuController@update');
Route::post('menu_update_exec', 'MenuController@update_exec');
Route::post('menu_delete', 'MenuController@delete');

//角色管理
Route::get('role_index', 'RoleController@index');
Route::get('role_index_tabledata', 'RoleController@index_tabledata');
Route::get('role_add', 'RoleController@add');
Route::post('role_add_exec', 'RoleController@add_exec');
Route::get('role_update', 'RoleController@update');
Route::post('role_update_exec', 'RoleController@update_exec');
Route::post('role_delete', 'RoleController@delete');

//登陆日志
Route::get('loguserlogin_index', 'LoguserloginController@index');
Route::get('loguserlogin_index_tabledata', 'LoguserloginController@index_tabledata');

//动态码日志
Route::get('logcodecheck_index', 'LogcodecheckController@index');
Route::get('logcodecheck_index_tabledata', 'LogcodecheckController@index_tabledata');

//数据操作日志
Route::get('logdatabasecudn_index', 'LogdatabasecudnController@index');
Route::get('logdatabasecudn_index_tabledata', 'LogdatabasecudnController@index_tabledata');

//参考样例-框架为了快速创建数据表增删改查而设立的参考样例
Route::get('example_index', 'ExampleController@index');
Route::get('example_index_tabledata', 'ExampleController@index_tabledata');
Route::get('example_add', 'ExampleController@add');
Route::post('example_add_exec', 'ExampleController@add_exec');
Route::get('example_update', 'ExampleController@update');
Route::post('example_update_exec', 'ExampleController@update_exec');
Route::post('example_delete', 'ExampleController@delete');

//文档管理
Route::get('markdown_childiframe', 'MarkdownController@childiframe');
Route::get('markdown_leftmenu', 'MarkdownController@leftmenu');
Route::post('markdown_search', 'MarkdownController@search');
Route::get('markdown_rightbody', 'MarkdownController@rightbody');
Route::post('markdown_backup_exec', 'MarkdownController@backup_exec');
Route::post('markdown_recovery_exec', 'MarkdownController@recovery_exec');

Route::post('markdown_realtimeshow', 'MarkdownController@realtimeshow');
Route::get('markdown_docshow', 'MarkdownController@docshow');
Route::get('markdown_index', 'MarkdownController@index');
Route::get('markdown_index_tabledata', 'MarkdownController@index_tabledata');
Route::get('markdown_add', 'MarkdownController@add');
Route::post('markdown_add_exec', 'MarkdownController@add_exec');
Route::get('markdown_update', 'MarkdownController@update');
Route::post('markdown_update_exec', 'MarkdownController@update_exec');
Route::post('markdown_delete', 'MarkdownController@delete');

//开放文档
Route::get('/', 'PublicdocController@childiframe');
Route::get('publicdoc_leftmenu', 'PublicdocController@leftmenu');
Route::post('publicdoc_search', 'PublicdocController@search');
Route::get('publicdoc_rightbody', 'PublicdocController@rightbody');
Route::get('publicdoc_docshow', 'PublicdocController@docshow');

//基金辅助工具
Route::get('fund_index', 'FundController@index');
Route::get('fund_index_tabledata', 'FundController@index_tabledata');
Route::get('fund_add', 'FundController@add');
Route::post('fund_add_exec', 'FundController@add_exec');
Route::post('fund_delete', 'FundController@delete');
Route::get('fund_index_update', 'FundController@index_update');
Route::get('fund_index_update_tabledata', 'FundController@index_update_tabledata');
Route::get('fundchild_update', 'FundController@childupdate');
Route::post('fundchild_update_exec', 'FundController@childupdate_exec');
Route::get('fund_mainchildupdate', 'FundController@mainchildupdate');


