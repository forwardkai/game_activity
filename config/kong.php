<?php
/**
 * kong服务配置
 * @authors 2018-09-13
 * @date    2018-02-23 09:47:38
 */
return [
    'default' => env('KONG_DEFAULT','default'),
    'case' => [
        'username' => env('KONG_CASE_NAME',''),
        'password' => env('KONG_CASE_PASS',''),
        'url' => env('KONG_URL',''),
    ]
];