<?php

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/wxapi/', function () {
   Router::post('user/login', 'App\Controller\V1\UserController@login');
});