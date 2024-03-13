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
   Router::post('user/profile/update', 'App\Controller\V1\UserController@profileUpdate');
   Router::get('dy/info', 'App\Controller\V1\UserController@getDouYinInfo');
   Router::post('user/business-card/add', 'App\Controller\V1\UserController@businessCardAdd');
   Router::get('user/business-card/list', 'App\Controller\V1\UserController@getBusinessCardList');
   Router::post('user/business-card/del/{cardId:\d+}', 'App\Controller\V1\UserController@businessCardDel');
   Router::post('user/business-card/edit/{cardId:\d+}', 'App\Controller\V1\UserController@businessCardEdit');
   Router::get('user/business-card/info/{cardId:\d+}', 'App\Controller\V1\UserController@businessCardInfo');
}, [
   'middleware' => [
      App\Middleware\Auth\LoginAuthMiddleware::class
   ],
]);
