<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Guzzle\HandlerStackFactory;
use GuzzleHttp\Client;

class WeixinService
{
    protected $appid;
    protected $secret;

    protected $code2SessionUrl = 'https://api.weixin.qq.com/sns/jscode2session';

    public function __construct() {
        $this->appid = config('wx.appid');
        $this->secret = config('wx.secret');
    }
    public function login($code)
    {
        $response = $this->request($this->code2SessionUrl, 'GET', ['appid' => $this->appid, 'secret' => $this->secret, 'js_code' => $code, 'grant_type' => 'authorization_code']);
        
        if ($response['errcode'] === 0) {
            return $response;
        }
        return false;
    }

    public function request(string $url, string $method = 'GET', array $options = [])
    {
        $factory = new HandlerStackFactory();
        $stack = $factory->create();

        $client = make(Client::class, [
            'config' => [
                'handler' => $stack,
            ],
        ]);

        $params = [];
        if ($method === 'GET') {
            $params['query'] = $options;
        }
        if ($method === 'POST') {
            $params['body'] = $options;
        }

        $response = $client->request($method, $url, $params);

        $body = (string)$response->getBody();

        return json_decode($body, true);
    }
}