<?php

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/wxapi/', function () {
   Router::get('get-ads', 'App\Controller\V1\IndexController@images');

   Router::get('news/list', 'App\Controller\V1\IndexController@newsList');

   Router::get('shop/list', 'App\Controller\V1\IndexController@shopList');

   Router::post('upload', 'App\Controller\V1\IndexController@upload');

   Router::get('cost-template/list', 'App\Controller\V1\IndexController@getCostTemplateList');
});