<?php

declare(strict_types=1);

namespace App\Controller\Swagger;

use OpenApi\Annotations as OA;

class Info
{
    /**
     * @OA\Info(
     *     title="小程序API",
     *     description="小程序API",
     *     version="1.0.0",
     *     @OA\Contact(
     *         email="xiaqc@qq.com",
     *         name="xiaqc",
     *     )
     * )
     * @OA\OpenApi(
     *     @OA\Server(
     *         url="{{APIserver}}",
     *         description="API server"
     *     )
     * )
     */
    public function swagger()
    {
    }
}
