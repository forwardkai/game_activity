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
    return view('welcome');
});
Route::get('view_demo', 'DemoController@viewDemo');
Route::post('img_demo','DemoController@imgDemo');

// Demo
Route::middleware(['AfterMiddleware'])->group(
    function () {
        Route::get('demo', 'DemoController@demo');
        Route::get('mysql_demo', 'DemoController@MysqlDemo');
        Route::get('kong_demo', 'DemoController@kongDemo');
        Route::get('reids_demo', 'DemoController@reidsDemo');
        Route::get('mns_demo', 'DemoController@pushMessage');
    }
);

Route::get('excel/export','ExcelController@export');
Route::get('excel/import','ExcelController@import');
