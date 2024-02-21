<?php

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/wxapi/', function () {
   // 任务标签
   Router::get('task/tags', 'App\Controller\V1\TaskController@tags');

   Router::get('task/list', 'App\Controller\V1\TaskController@getList');

   Router::get('task/info/{taskId:\d+}', 'App\Controller\V1\TaskController@getInfo');

   // 获取任务日历状态
   Router::get('task/calendar', 'App\Controller\V1\TaskController@calendar');
   // 获取任务历史
   Router::get('task/history/{taskId:\d+}', 'App\Controller\V1\TaskController@getHistoryList');
});

Router::addGroup('/wxapi/', function () {
    Router::post('task/apply', 'App\Controller\V1\TaskController@apply');
    Router::post('task/video', 'App\Controller\V1\TaskController@video');
 }, [
    'middleware' => [
       App\Middleware\Auth\LoginAuthMiddleware::class
    ],
 ]);