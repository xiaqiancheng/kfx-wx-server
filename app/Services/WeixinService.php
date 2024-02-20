<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
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
        
        if (!($response['errcode'] ?? '')) {
            return $response;
        }

        logger('用户登录', 'wx')->error(json_encode($response));
        return false;
    }

    public function decryptData($session, $iv, $encryptedData) 
    {
        if (strlen($session) != 24) {
            throw new BusinessException(ErrorCode::ILLEGAL_AES_KEY);
		}
		$aesKey = base64_decode($session);

		if (strlen($iv) != 24) {
            throw new BusinessException(ErrorCode::ILLEGAL_IV);
		}
		$aesIV = base64_decode($iv);

		$aesCipher = base64_decode($encryptedData);

		$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

		$dataObj = json_decode($result);
		if ($dataObj == NULL ) {
            throw new BusinessException(ErrorCode::ILLEGAL_BUFFER);
		}

		if ($dataObj->watermark->appid != $this->appid ) {
            throw new BusinessException(ErrorCode::ILLEGAL_BUFFER);
		}
		return $result;
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
        
        $body = $response->getBody();

        $contents = $body->getContents();

        return json_decode($contents, true);
    }
}