<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $allowUrl = [
            'http://127.0.0.1:9528',
            'http://localhost:9528',
            'http://localhost:9526'
        ];
        $origin = $request->getHeader('origin');
        $response = Context::get(ResponseInterface::class);
        $url = $origin[0] ?? '';
        if (in_array($url, $allowUrl)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $url)
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            // Headers 可以根据实际情况进行改写。
            ->withHeader('Access-Control-Allow-Headers', 'DNT, Keep-Alive, User-Agent, Cache-Control, Content-Type, Authorization');
            Context::set(ResponseInterface::class, $response);

            if ($request->getMethod() == 'OPTIONS') {
                return $response;
            }
        }

        return $handler->handle($request);
    }
}