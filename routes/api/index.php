<?php

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/wxapi/', function () {
   Router::get('get-ads', 'App\Controller\V1\IndexController@images');

   // 任务标签
   Router::get('task/tags', 'App\Controller\V1\TaskController@tags');

   Router::get('task/list', 'App\Controller\V1\TaskController@getList');

   Router::get('task/info/{taskId:\d+}', 'App\Controller\V1\TaskController@getInfo');

   // 获取任务日历状态
   Router::get('task/calendar', 'App\Controller\V1\TaskController@calendar');
   // 获取任务历史
   Router::get('task/history/{taskId:\d+}', 'App\Controller\V1\TaskController@getHistoryList');

   Router::get('news/list', 'App\Controller\V1\IndexController@newsList');
});