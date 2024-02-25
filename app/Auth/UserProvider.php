<?php

declare(strict_types=1);

namespace App\Auth;

use App\Ego\GenericUser;
use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use HyperfExt\Auth\Contracts\AuthenticatableInterface;
use HyperfExt\Auth\Contracts\UserProviderInterface;
use App\Services\WeixinService;
use App\Repositories\BloggerRepository;

class UserProvider implements UserProviderInterface
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier
     */
    public function retrieveById($identifier): ?AuthenticatableInterface
    {
        $userInfo = BloggerRepository::instance()->find($identifier, ['id', 'openid', 'avatarUrl', 'name', 'nickName', 'level', 'income', 'doyin_id']);
        return new GenericUser($userInfo);
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials): ?AuthenticatableInterface
    {
        $service = new WeixinService;
        $result = $service->login($credentials['code']);

        if (!$result) {
            throw new BusinessException(ErrorCode::AUTH_ERROR, '登录失败');
        }
        
        // 验证用户是否存在
        $user = BloggerRepository::instance()->findOneBy(['openid' => $result['openid']], ['id']);
        
        $data = [
            'session_key' => $result['session_key']
        ];
        if (!empty($user)) {
            $data['id'] = $user['id'];
            $data['update_time'] = time();
        } else {
            $data['openid'] = $result['openid'];
            $data['unionid'] = $result['unionid'] ?? '';
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['level'] = 1;
        }
        $user = BloggerRepository::instance()->saveData($data);

        $userInfo = [
            'id' => $user['id'],
            'is_authorize_user' => $user['is_authorize_user'], 
            'is_authorize_phone' => $user['is_authorize_phone'],
            'user' => $user['id']
        ];
        return new GenericUser($userInfo);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param mixed $identifier
     */
    public function retrieveByToken($identifier, string $token): ?AuthenticatableInterface
    {
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(AuthenticatableInterface $user, string $token): void
    {
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool
    {
        // if (! isset($user->account_id) || ! $user->account_id) {
        //     throw new BusinessException(ErrorCode::AUTH_ERROR, '账号错误');
        // }
        return true;
    }
}
