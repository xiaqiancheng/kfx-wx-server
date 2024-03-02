<?php

declare(strict_types=1);

namespace App\Middleware\Auth;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Context;
use HyperfExt\Auth\AuthManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ShareAuthMiddleware implements MiddlewareInterface
{
    /**
     * @Inject
     * @var AuthManager
     */
    protected $auth;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            auth('shareapi')->checkOrFail();
            $userInfo = auth('shareapi')->user();
            if (empty($userInfo)) {
                throw new BusinessException(ErrorCode::TOKEN_ERROR, '无效认证');
            }
        } catch (\HyperfExt\Jwt\Exceptions\TokenInvalidException $error) {
            throw new BusinessException(ErrorCode::TOKEN_ERROR, '无效认证');
        }catch (\Throwable $throwable) {
            throw new BusinessException(ErrorCode::TOKEN_ERROR, '无效认证');
        }
        $serverRequest = Context::get(ServerRequestInterface::class);
        $serverRequest = $serverRequest->withAttribute('auth', $userInfo);

        Context::set(ServerRequestInterface::class, $serverRequest);

        return $handler->handle($serverRequest);
    }
}
