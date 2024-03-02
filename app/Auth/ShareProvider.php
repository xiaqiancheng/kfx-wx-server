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
use App\Repositories\FileShareRepository;

class ShareProvider implements UserProviderInterface
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier
     */
    public function retrieveById($identifier): ?AuthenticatableInterface
    {
        $userInfo = FileShareRepository::instance()->find($identifier, ['id']);
        return new GenericUser($userInfo);
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials): ?AuthenticatableInterface
    {
        // 验证用户是否存在
        $share = FileShareRepository::instance()->findOneBy(['token' => $credentials['token']], ['id', 'expiration_time', 'extracted_code', 'valid_time']);

        if (empty($share)) {
            throw new BusinessException(ErrorCode::AUTH_ERROR, '认证失败');
        }

        if (strtolower($credentials['code']) !== strtolower($share['extracted_code'])) {
            throw new BusinessException(ErrorCode::AUTH_ERROR, '提取码校验失败');
        }

        if ($share['valid_time'] == 0) {
            if (strtotime($share['expiration_time']) < time()) {
                throw new BusinessException(ErrorCode::AUTH_ERROR, '链接已失效');
            }
        }
        
        $userInfo = [
            'user' => $share['id']
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
