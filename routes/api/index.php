<?php

use Hyperf\HttpServer\Router\Router;

Router::addGroup('/wxapi/', function () {
   Router::get('get-ads', 'App\Controller\V1\IndexController@images');

   Router::get('news/list', 'App\Controller\V1\IndexController@newsList');

   Router::get('shop/list', 'App\Controller\V1\IndexController@shopList');

   Router::post('upload', 'App\Controller\V1\IndexController@upload');

   Router::post('webhooks', 'App\Controller\V1\IndexController@webhooks');

   Router::get('cost-template/list', 'App\Controller\V1\IndexController@getCostTemplateList');

   Router::post('share', 'App\Controller\V1\ShareController@share');
   Router::post('share/verify', 'App\Controller\V1\ShareController@shareVerify');

   Router::get('video/rank', 'App\Controller\V1\IndexController@videoRank');

   Router::get('download/{code}', 'App\Controller\V1\IndexController@download');
});

Router::addGroup('/wxapi/', function () {
   Router::get('share/file/list/{token}', 'App\Controller\V1\ShareController@getFile');
   Router::get('share/get-download-code', 'App\Controller\V1\ShareController@getdownloadCode');
}, [
   'middleware' => [
      App\Middleware\Auth\ShareAuthMiddleware::class
   ],
]);
