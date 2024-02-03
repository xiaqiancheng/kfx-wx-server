<?php

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/wxapi/', function () {
   Router::get('index/images', 'App\Controller\Index\V1\IndexController@images');
});