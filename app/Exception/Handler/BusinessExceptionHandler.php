<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Constants\ErrorCode;
use App\Controller\Response;
use App\Exception\BusinessException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\MethodNotAllowedHttpException;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\Validation\ValidationException;
use PDOException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class BusinessExceptionHandler extends ExceptionHandler
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Response
     */
    protected $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(Response::class);
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 阻止异常冒泡
        $this->stopPropagation();
        if ($throwable instanceof BusinessException) {
            return $this->response->error($throwable->getCode(), $throwable->getMessage());
        }
        if (config('app_env', 'dev') != 'dev') {
            if ($throwable instanceof PDOException) {
                logger('sql')->error(format_throwable($throwable));
                throw new BusinessException(ErrorCode::SERVER_ERROR);
            }
        }
        if ($throwable instanceof ValidationException) {
            $message = $throwable->validator->errors()->first();
            return $this->response->error(ErrorCode::SERVER_ERROR, $message);
        }

        if ($throwable instanceof NotFoundHttpException) {
            return $this->response->error(404, '路由找不到');
        }

        if ($throwable instanceof MethodNotAllowedHttpException) {
            return $this->response->error(405, '请求方式不允许');
        }

        logger()->error(format_throwable($throwable));
        return $this->response->error(ErrorCode::SERVER_ERROR, $throwable->getMessage());
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
