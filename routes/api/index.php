<?php

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/wxapi/', function () {
   Router::get('get-ads', 'App\Controller\V1\IndexController@images');

   // 任务标签
   Router::get('task/tags', 'App\Controller\V1\TaskController@tags');

   Router::get('task/list', 'App\Controller\V1\TaskController@getList');
});