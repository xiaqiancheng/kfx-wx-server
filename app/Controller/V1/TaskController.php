<?php
namespace App\Controller\V1;

use App\Controller\AbstractController;
use App\Repositories\TagRepository;
use OpenApi\Annotations as OA;

class TaskController extends AbstractController
{
    /**
     * @OA\Get(
     *     path="/wxapi/task/tags",
     *     tags={"任务"},
     *     summary="任务标签",
     *     description="任务标签",
     *     operationId="TaskController_tags",
     *     @OA\Response(response="200", description="标签列表返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"form", "content"},
     *                 @OA\Property(property="form", type="array", description="全部形态数据",
     *                     @OA\Items(type="object", 
     *                          required={"id", "name"},
     *                          @OA\Property(property="id", type="integer", description="唯一id"),
     *                          @OA\Property(property="name", type="string", description="名称")
     *                      )
     *                 ),
     *                 @OA\Property(property="content", type="array", description="全部内容数据",
     *                     @OA\Items(type="object", 
     *                          required={"id", "name"},
     *                          @OA\Property(property="id", type="integer", description="唯一id"),
     *                          @OA\Property(property="name", type="string", description="名称")
     *                      )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function tags()
    {
        $list = TagRepository::instance()->getList([], ['id', 'name', 'dimension'], 0);

        $map = [
            1 => 'form', // 形态
            2 => 'content' // 内容
        ];

        $data = [];
        foreach ($list['list'] as $v) {
            $data[$map[$v['dimension']]][] = $v;
        }

        return $this->response->success($data);
    }
}