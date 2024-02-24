<?php

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/wxapi/', function () {
   Router::post('user/login', 'App\Controller\V1\UserController@login');
});

Router::addGroup('/wxapi/', function () {
   Router::post('user/profile', 'App\Controller\V1\UserController@profile');
   Router::post('user/dyauth-code', 'App\Controller\V1\UserController@douyinAuthCode');
   Router::get('user/info', 'App\Controller\V1\UserController@info');
   Router::get('user/income/list', 'App\Controller\V1\UserController@incomeList');
   Router::get('user/notice/list', 'App\Controller\V1\UserController@noticeList');
   Router::get('user/video/list', 'App\Controller\V1\UserController@videoList');
   Router::post('user/notice/read', 'App\Controller\V1\UserController@noticeRead');
}, [
   'middleware' => [
      App\Middleware\Auth\LoginAuthMiddleware::class
   ],
]);