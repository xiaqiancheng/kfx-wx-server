<?php
namespace App\Controller\V1;

use App\Controller\AbstractController;
use App\Repositories\TagRepository;
use App\Services\TaskService;
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

    /**
     * @OA\Get(
     *     path="/wxapi/task/list",
     *     tags={"任务"},
     *     summary="任务列表",
     *     description="任务列表",
     *     operationId="TaskController_getList",
     *     @OA\Parameter(name="page", in="query", description="页码 1开始",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Parameter(name="page_size", in="query", description="每页数量",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Parameter(name="task_name", in="query", description="任务名称",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="task_settle_type", in="query", description="结算方式，类型包含：1-广告分成 2-支付交易CPS",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="form", in="query", description="形态",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="content", in="query", description="内容",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="sort", in="query", description="排序 1：最新任务 2：投稿人数降序 3：最高收益",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="标签列表返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"total_count", "list"},
     *                 @OA\Property(property="list", type="array", description="全部形态数据",
     *                     @OA\Items(type="object", 
     *                          required={"id", "task_name", "task_settle_type", "start_page", "anchor_title", "task_icon", "task_tags", "refer_ma_captures", "commission"},
     *                          @OA\Property(property="id", type="integer", description="唯一id"),
     *                          @OA\Property(property="task_name", type="string", description="任务名称"),
     *                          @OA\Property(property="task_settle_type", type="integer", description="结算方式，类型包含：1-广告分成 2-支付交易CPS"),
     *                          @OA\Property(property="start_page", type="string", description="小程序页面地址"),
     *                          @OA\Property(property="anchor_title", type="string", description="锚点标题"),
     *                          @OA\Property(property="task_icon", type="string", description="任务图标"),
     *                          @OA\Property(property="task_tags", type="object", description="任务标签"),
     *                          @OA\Property(property="refer_ma_captures", type="object", description="小程序截图"),
     *                          @OA\Property(property="commission", type="integer", description="最大收益（分）")
     *                      )
     *                 ),
     *                 @OA\Property(property="total_count", type="integer", description="总数量")
     *             )
     *         )
     *     )
     * )
     */
    public function getList() 
    {
        $page = $this->request->input('page', 1);
        $pageSize = $this->request->input('page_size', 20);
        $params = $this->request->inputs(['start_month', 'end_month', 'task_name', 'task_settle_type', 'form', 'content', 'sort']);

        $filter = [];
        if ($params['start_month'] ?? '' && $params['start_month'] ?? '') {
            if ($params['start_month'] && $params['start_month']) {
                $filter['create_time'] = ['between', [strtotime($params['start_month'].'-01'), strtotime(date('Y-m-d', strtotime('-1 day', strtotime("+1 months", strtotime($params['end_month'])))).'23:59:59')]];
            }
        }

        if ($params['task_name'] ?? '' && $params['task_name']) {
            $filter['task_name'] = ['like', "%{$params['task_name']}%"];
        }
        
        if ($params['task_settle_type'] ?? '' && $params['task_settle_type']) {
            $filter['task_settle_type'] = $params['task_settle_type'];
        }

        if ($params['form'] ?? '' && $params['form']) {
            $filter['task_tags'] = ['like', "%\"" . $params['form'] . "\",%"];
        }

        if ($params['content'] ?? '' && $params['content']) {
            if ($filter['task_tags'] ?? '') {
                $filter['task_tags'] = ['like', "%" . $params['form'] . "\"," . "\"" . $params['content'] . "%"];
            } else {
                $filter['task_tags'] = ['like', "%,\"" . $params['content'] . "%"];
            }
        }

        $sort = ['id' => 'desc'];
        if ($params['sort'] ?? '' && $params['sort'] == 1) {
            $sort = ['create_time' => 'desc'];
        }
        if ($params['sort'] ?? '' && $params['sort'] == 2) {
            $sort = ['contribution_number' => 'desc'];
        }
        if ($params['sort'] ?? '' && $params['sort'] == 3) {
            $sort = ['commission' => 'desc'];
        }

        $service = new TaskService();

        $list = $service->getList($filter, ['id', 'task_name', 'task_settle_type', 'start_page', 'anchor_title', 'task_icon', 'task_tags', 'refer_ma_captures', 'commission'],  $page, $pageSize, $sort);

        // $modelVideo=new \app\common\model\Video();
        // //var_dump($list);
        // if(!empty($list['lists']) ) {

        //     foreach ($list["lists"] as $key => &$vo) {
        //         if (empty($vo["id"])) {
        //             unset($list["lists"][$key]);

        //             continue;
        //         }

        //         $listVideo = $modelVideo->order('income desc')->paginate(['list_rows'=>1,'query' => ["task_id"=>$vo["id"]]])->toArray();

        //         $vo["max_video_info"] = empty($listVideo["data"])?[]:$listVideo["data"];
        //     }
        //     unset($vo);
        // }


        return $this->response->success($list);
    }
}