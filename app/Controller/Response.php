<?php

declare(strict_types=1);

namespace App\Controller;

use App\Constants\ErrorCode;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(ResponseInterface::class);
    }

    /**
     * @param $data
     * @param mixed $string
     * @return PsrResponseInterface
     */
    public function raw($string)
    {
        return $this->response->raw($string);
    }

    /**
     * @param $data
     * @return PsrResponseInterface
     */
    public function json($data)
    {
        return $this->response->json($data);
    }

    /**
     * @param $data
     * @param $message
     * @return PsrResponseInterface
     */
    public function success($data = [], $message = 'success')
    {
        $data = [
            'errcode' => 0,
            'errmsg' => $message,
            'data' => $data,
        ];

        return $this->response->json($data);
    }

    /**
     * @param $data
     * @param $message
     * @param mixed $code
     * @return PsrResponseInterface
     */
    public function openapi($data = [], $code = '200', $message = 'success')
    {
        $data = [
            'status' => 'success',
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        return $this->response->json($data);
    }

    /**
     * @param string $message
     * @param int $code
     * @param string $errdata
     * @return PsrResponseInterface
     */
    public function error($code = ErrorCode::SERVER_ERROR, $message = '')
    {
        return $this->response->json([
            'errcode' => $code,
            'errmsg' => $message,
        ]);
    }

    /**
     * @return \Hyperf\HttpMessage\Server\Response
     */
    public function response()
    {
        return Context::get(PsrResponseInterface::class);
    }
}
