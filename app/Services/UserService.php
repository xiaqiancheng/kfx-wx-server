<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Repositories\BloggerRepository;

class UserService
{
    public function profile($userId, $iv, $encryptedData)
    {
        $userInfo = BloggerRepository::instance()->find($userId, ['id', 'openid', 'session_key']);

        if (empty($userInfo)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '用户不存在');
        }

        // 解密
        $service = new WeixinService;
        $data = $service->decryptData($userInfo['session_key'], $iv, $encryptedData);

        $userProfile = json_decode($data, true);
        
        BloggerRepository::instance()->saveData([
            'id' => $userId,
            'name' => $userProfile['nickName'],
            'nickName' => $userProfile['nickName'], 
            'avatarUrl' => $userProfile['avatarUrl'],
            'gender' => $userProfile['gender'],
            'city' => $userProfile['city'],
            'province' => $userProfile['province'],
            'country' => $userProfile['country'],
            'language' => $userProfile['language'],
            'language' => $userProfile['language'],
            'is_authorize_user' => 1,
            'update_time' => time()
        ]);

        return true;
    }
}