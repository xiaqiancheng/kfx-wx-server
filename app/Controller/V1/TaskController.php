<?php
namespace App\Controller\V1;

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Exception\BusinessException;
use App\Repositories\BloggerRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\TagRepository;
use App\Repositories\VideoRepository;
use App\Services\TaskService;
use OpenApi\Annotations as OA;
use App\Repositories\TaskCollectionRepository;
use App\Services\FileService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class TaskController extends AbstractController
{
    /**
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    /**
     * @OA\Get(
     *     path="/wxapi/task/tags",
     *     tags={"任务"},
     *     summary="任务结算方式和标签",
     *     description="任务结算方式和标签",
     *     operationId="TaskController_tags",
     *     @OA\Response(response="200", description="任务结算方式和标签列表返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"settle_type", "form", "content"},
     *                 @OA\Property(property="settle_type", type="array", description="结算方式",
     *                     @OA\Items(type="object", 
     *                          required={"value", "label"},
     *                          @OA\Property(property="value", type="integer", description="唯一值"),
     *                          @OA\Property(property="label", type="string", description="名称")
     *                      )
     *                 ),
     *                 @OA\Property(property="form", type="array", description="全部形态数据",
     *                     @OA\Items(type="object", 
     *                          required={"value", "label"},
     *                          @OA\Property(property="value", type="integer", description="唯一值"),
     *                          @OA\Property(property="label", type="string", description="名称")
     *                      )
     *                 ),
     *                 @OA\Property(property="content", type="array", description="全部内容数据",
     *                     @OA\Items(type="object", 
     *                          required={"value", "label"},
     *                          @OA\Property(property="value", type="integer", description="唯一值"),
     *                          @OA\Property(property="label", type="string", description="名称")
     *                      )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function tags()
    {
        $list = TagRepository::instance()->getList([], ['id as value', 'name as label', 'dimension'], 0);

        $map = [
            1 => 'form', // 形态
            2 => 'content' // 内容
        ];

        $data = [];
        foreach ($list['list'] as $v) {
            $data[$map[$v['dimension']]][] = $v;
        }

        // 结算方式
        $data['settle_type'] = [
            [
                'label' => '广告分成',
                'value' => 1
            ],
            [
                'label' => '支付分成（基础）',
                'value' => 2
            ],
            [
                'label' => '支付分成（绑定）',
                'value' => 3
            ],
            [
                'label' => '广告分成+支付分成（基础）',
                'value' => 7
            ],
            [
                'label' => '广告分成+支付分成（绑定）',
                'value' => 8
            ]
        ];

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
     *     @OA\Parameter(name="task_settle_type", in="query", description="结算方式，类型包含：1-广告分成、2-支付分成（基础）、3-支付分成（绑定）、7-广告分成+支付分成（基础）、8-广告分成+支付分成（绑定）",
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
     *     @OA\Response(response="200", description="任务列表返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"total_count", "list"},
     *                 @OA\Property(property="list", type="array", description="任务数据",
     *                     @OA\Items(type="object", 
     *                          required={"id", "task_name", "task_settle_type", "start_page", "anchor_title", "task_icon", "task_tags", "refer_ma_captures", "profit", "task_start_time", "task_end_time", "payment_allocate_ratio", "restrict_level"},
     *                          @OA\Property(property="id", type="integer", description="id"),
     *                          @OA\Property(property="task_name", type="string", description="任务名称"),
     *                          @OA\Property(property="task_settle_type", type="integer", description="结算方式，类型包含：1-广告分成、2-支付分成（基础）、3-支付分成（绑定）、7-广告分成+支付分成（基础）、8-广告分成+支付分成（绑定）"),
     *                          @OA\Property(property="start_page", type="string", description="小程序页面地址"),
     *                          @OA\Property(property="anchor_title", type="string", description="锚点标题"),
     *                          @OA\Property(property="task_icon", type="string", description="任务图标"),
     *                          @OA\Property(property="task_tags", type="object", description="任务标签"),
     *                          @OA\Property(property="refer_ma_captures", type="object", description="小程序截图"),
     *                          @OA\Property(property="profit", type="integer", description="最大收益（分）"),
     *                          @OA\Property(property="task_start_time", type="integer", description="任务开始时间，秒级时间戳"),
     *                          @OA\Property(property="task_end_time", type="integer", description="任务结束时间，秒级时间戳"),
     *                          @OA\Property(property="payment_allocate_ratio", type="float", description="达人分成比例，百分比"),
     *                          @OA\Property(property="restrict_level", type="string", description="限制达人报名等级，数组格式"),
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

  $userInfo = null;
        try {
            auth('api')->checkOrFail();
            $userInfo = auth('api')->user();
        }catch (\Throwable $throwable) {}
        $filter = ['status' => 1];
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
            $sort = ['profit' => 'desc'];
        }

        $service = new TaskService();

        $list = $service->getList($filter, ['id', 'task_name', 'task_settle_type', 'start_page', 'anchor_title', 'task_icon', 'task_tags', 'refer_ma_captures', 'profit', 'task_start_time', 'task_end_time', 'payment_allocate_ratio', 'restrict_level', 'update_time'],  $page, $pageSize, $sort);

        // $modelVideo=new \app\common\model\Video();
        $intTime = time()-86400*5;
        // //var_dump($list);
        if(!empty($list['list']) ) {
            foreach ($list["list"] as $key => &$vo) {
                // if (empty($vo["id"])) {
                //     unset($list["lists"][$key]);

                //     continue;
                // }

                // $listVideo = $modelVideo->order('income desc')->paginate(['list_rows'=>1,'query' => ["task_id"=>$vo["id"]]])->toArray();

                // $vo["max_video_info"] = empty($listVideo["data"])?[]:$listVideo["data"];
                if($intTime<$vo['task_end_time']){
                        $vo['task_time_over'] = false;
                
                }else{

                        $vo['task_time_over'] = true;
                }
                
       if ($userInfo) {
               $vo['collection_status'] = -1;
        $vo['reject_reason'] = '';
        // 视频审核状态
        $vo['video_check_status'] = -1;
        $vo['is_balance'] = -1;
            $result = TaskCollectionRepository::instance()->findOneBy(['task_id' => $vo['id'], 'blogger_id' => $userInfo->id], ['status', 'reject_reason', 'updated_at'], ['id' => 'desc']);
            if ($result) {
                $vo['collection_status'] = $result['status'];
                $vo['reject_reason'] = $result['reject_reason'];
                $vo['reject_time'] = $result['updated_at'];
            }

            $result1 = VideoRepository::instance()->findOneBy(['task_id' => $vo['id'], 'blogger_id' => $userInfo->id], ['status', 'is_balance'], ['id' => 'desc']);
            if ($result1) {
                $vo['video_check_status'] = $result1['status'];
                $vo['is_balance'] = $result1['is_balance'];
            }
        }
                $vo['payment_allocate_ratio'] = $vo['payment_allocate_ratio'] > 0 ? $vo['payment_allocate_ratio'] / 100 : 0; // 达人分成比例
            }
//            unset($vo);
        }

        return $this->response->success($list);
    }

    /**
     * @OA\Get(
     *     path="/wxapi/task/info/{taskId}",
     *     tags={"任务"},
     *     summary="任务详情",
     *     description="任务详情",
     *     operationId="TaskController_getInfo",
     *     @OA\Parameter(name="taskId", in="path", description="任务ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="任务详情返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"id", "task_name", "task_type", "sign_status", "task_desc", "shop_id", "reserve_time", "remark", "audit_requirement", "creative_guidance", "task_settle_type", "task_start_time", "task_end_time", "payment_allocate_ratio", "task_icon", "task_tags", "refer_ma_captures", "cost_template_id", "collection_status", "reject_reason", "video_check_status", "is_balance", "max_video_info", "restrict_level", "release_start_time", "release_end_time"},
     *                 @OA\Property(property="id", type="integer", description="任务id"),
     *                 @OA\Property(property="task_name", type="string", description="任务名称"),
     *                 @OA\Property(property="task_type", type="integer", description="任务类型 1普通任务 2探店任务"),
     *                 @OA\Property(property="sign_status", type="integer", description="探店签到状态 0未签到 1已签到"),
     *                 @OA\Property(property="task_desc", type="string", description="任务介绍"),
     *                 @OA\Property(property="shop_id", type="string", description="店铺ID 数组格式"),
     *                 @OA\Property(property="reserve_time", type="string", description="预约时间 数组格式"),
     *                 @OA\Property(property="remark", type="string", description="探店备注"),
     *                 @OA\Property(property="detail", type="string", description="详细描述"),
     *                 @OA\Property(property="audit_requirement", type="string", description="审核要求"),
     *                 @OA\Property(property="creative_guidance", type="string", description="创作指导"),
     *                 @OA\Property(property="task_settle_type", type="integer", description="结算方式，类型包含：1-广告分成、2-支付分成（基础）、3-支付分成（绑定）、7-广告分成+支付分成（基础）、8-广告分成+支付分成（绑定）"),
     *                 @OA\Property(property="task_start_time", type="integer", description="任务开始时间，秒级时间戳"),
     *                 @OA\Property(property="task_end_time", type="integer", description="任务结束时间，秒级时间戳"),
     *                 @OA\Property(property="payment_allocate_ratio", type="float", description="达人分成比例，百分比"),
     *                 @OA\Property(property="task_icon", type="string", description="任务图标"),
     *                 @OA\Property(property="task_tags", type="object", description="任务标签"),
     *                 @OA\Property(property="refer_ma_captures", type="object", description="小程序截图"),
     *                 @OA\Property(property="cost_template_id", type="integer", description="费用模板ID"),
     *                 @OA\Property(property="collection_status", type="integer", description="任务领取审核状态 -1未领取 0待审核，1已审核，2审核未通过"),
     *                 @OA\Property(property="reject_reason", type="string", description="任务审核拒绝原因"),
     *                 @OA\Property(property="reject_time", type="string", description="任务审核拒绝时间"),
     *                 @OA\Property(property="video_check_status", type="integer", description="视频审核状态 -1未提交视频 0待审核 1已审核 2已拒绝"),
     *                 @OA\Property(property="is_balance", type="integer", description="是否结算  0任务中 1待结算 2已结算"),
     *                 @OA\Property(property="restrict_level", type="string", description="限制达人报名等级，数组格式"),
     *                 @OA\Property(property="release_start_time", type="string", description="发布开始时间"),
     *                 @OA\Property(property="release_end_time", type="string", description="发布结束时间"),
     *                 @OA\Property(property="max_video_info", type="array", description="视频榜单数据",
     *                     @OA\Items(type="object", 
     *                          required={"id", "cover", "play_count", "forward_count", "digg_count"},
     *                          @OA\Property(property="id", type="integer", description="视频榜单id"),
     *                          @OA\Property(property="cover", type="string", description="封面图"),
     *                          @OA\Property(property="play_count", type="integer", description="播放量"),
     *                          @OA\Property(property="forward_count", type="integer", description="收藏转发量"),
     *                          @OA\Property(property="digg_count", type="integer", description="点赞数")
     *                      )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getInfo($taskId) {
        $userInfo = null;
        try {
            auth('api')->checkOrFail();
            $userInfo = auth('api')->user();
        }catch (\Throwable $throwable) {}

        $service = new TaskService();

        $data = $service->find($taskId, ['id', 'task_name', 'task_type', 'task_desc', 'detail', 'task_settle_type', 'task_start_time', 'task_end_time', 'task_icon', 'task_tags', 'refer_ma_captures', 'cost_template_id', 'payment_allocate_ratio', 'audit_requirement', 'creative_guidance', 'shop_id', 'reserve_time', 'remark', 'restrict_level', 'update_time']);
    
        $data['payment_allocate_ratio'] = $data['payment_allocate_ratio'] > 0 ? $data['payment_allocate_ratio'] / 100 : 0; // 达人分成比例

        // 任务领取状态
        $data['collection_status'] = -1;
        $data['reject_reason'] = '';
        $data['sign_status'] = 0;
        // 视频审核状态
        $data['video_check_status'] = -1;
        $data['is_balance'] = -1;

        $data['release_start_time'] = '';
        $data['release_end_time'] = '';
        if ($userInfo) {
            $result = TaskCollectionRepository::instance()->findOneBy(['task_id' => $taskId, 'blogger_id' => $userInfo->id], ['status', 'reject_reason', 'sign_status', 'updated_at'], ['id' => 'desc']);
            if ($result) {
                $data['collection_status'] = $result['status'];
                $data['reject_reason'] = $result['reject_reason'];
                $data['reject_time'] = $result['updated_at'];
                $data['sign_status'] = $result['sign_status'];
            }

            $result1 = VideoRepository::instance()->findOneBy(['task_id' => $taskId, 'blogger_id' => $userInfo->id], ['status', 'refuse_reason', 'is_balance', 'release_start_time', 'release_end_time'], ['id' => 'desc']);
            if ($result1) {
                $data['video_check_status'] = $result1['status'];
                $data['reject_reason'] = $result1['refuse_reason'];
                $data['is_balance'] = $result1['is_balance'];
                $data['release_start_time'] = $result1['release_start_time'];
                $data['release_end_time'] = $result1['release_end_time'];
            }
        }

        // 视频榜单
        $videoList = VideoRepository::instance()->getList(['task_id' => $taskId, 'status' => 1], ['id', 'cover', 'play_count', 'forward_count', 'digg_count'], 1, 100, ['play_count' => 'desc']);
        $mediaId = array_unique(array_column($videoList['list'], 'cover'));
        $fileService = new FileService;
        $fileData = $fileService->getFileByMediaId($mediaId);

        foreach ($videoList['list'] as &$v) {
            $v['cover'] = env('HOST') . '/' . $fileData[$v['cover']];
        }

        $data['max_video_info'] = $videoList['list'];

        return $this->response->success($data);
    }

    /**
     * @OA\Get(
     *     path="/wxapi/task/calendar",
     *     tags={"任务"},
     *     summary="任务日历",
     *     description="任务日历",
     *     operationId="TaskController_calendar",
     *     @OA\Parameter(name="month", in="path", description="月份 如：2024-02",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="日历状态返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回 0：无任务，1：有任务")
     *         )
     *     )
     * )
     */
    public function calendar() {
        $month = $this->request->input('month');

        if (!$month) {
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, '月份必传');
        }
        $days_in_month = date('t', strtotime("$month-01"));

        $month_array = array_fill(0, $days_in_month, 0);

        $random_indices = array_rand($month_array, 10);
        foreach ($random_indices as $index) {
            $month_array[$index] = 1;
        }

        return $this->response->success($month_array);
    }

    /**
     * @OA\Get(
     *     path="/wxapi/task/history/{taskId}",
     *     tags={"任务"},
     *     summary="任务历史",
     *     description="任务历史",
     *     operationId="TaskController_getHistoryList",
     *     @OA\Parameter(name="page", in="query", description="页码 1开始",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Parameter(name="page_size", in="query", description="每页数量",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Response(response="200", description="任务列表返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"total_count", "list"},
     *                 @OA\Property(property="list", type="array", description="任务历史数据",
     *                     @OA\Items(type="object", 
     *                          required={"name", "modify_date", "task_desc"},
     *                          @OA\Property(property="name", type="string", description="任务名称"),
     *                          @OA\Property(property="modify_date", type="string", description="修改日期"),
     *                          @OA\Property(property="task_desc", type="string", description="任务描述")
     *                      )
     *                 ),
     *                 @OA\Property(property="total_count", type="integer", description="总数量")
     *             )
     *         )
     *     )
     * )
     */
    public function getHistoryList($taskId) {
        $list = [
            'total_count' => 4,
            'list' => [
                [
                    'name' => '任务名称',
                    'modify_date' => '2024-01-22',
                    'task_desc' => '测试产品介绍测试产品介绍测试产品介绍测试产品介绍测试产品介绍测试产品介绍'
                ],
                [
                    'name' => '任务名称1',
                    'modify_date' => '2024-01-11',
                    'task_desc' => '测试产品介绍测试产品介绍测试产品介绍测试产品介绍测试产品介绍测试产品介绍'
                ],
                [
                    'name' => '任务名称2',
                    'modify_date' => '2024-01-09',
                    'task_desc' => '测试产品介绍测试产品介绍测试产品介绍测试产品介绍测试产品介绍测试产品介绍'
                ],
                [
                    'name' => '任务名称2',
                    'modify_date' => '2024-01-08',
                    'task_desc' => '测试产品介绍测试产品介绍测试产品介绍测试产品介绍测试产品介绍测试产品介绍'
                ]
            ]
        ];

        return $this->response->success($list);
    }

    /**
     * @OA\Post(
     *     path="/wxapi/task/apply",
     *     tags={"任务"},
     *     summary="任务申领",
     *     description="任务申领",
     *     operationId="TaskController_apply",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"task_id", "business_card_id", "level_id"},
     *             @OA\Property(property="task_id", type="integer", description="任务ID"),
     *             @OA\Property(property="business_card_id", type="integer", description="名片ID"),
     *             @OA\Property(property="level_id", type="integer", description="级别ID"),
     *             @OA\Property(property="shop_name", type="string", description="店铺名称"),
     *             @OA\Property(property="reserve_time", type="string", description="预约时间 如2023-04-02 23:12:32"),
     *             @OA\Property(property="extra_cost", type="float", description="额外费用（元）"),
     *             @OA\Property(property="remark", type="string", description="备注")
     *         )
     *     ),
     *     @OA\Response(response="200", description="返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息")
     *         )
     *     )
     * )
     */
    public function apply()
    {
        $request = $this->request->inputs(['task_id', 'business_card_id', 'level_id', 'shop_id', 'shop_name', 'reserve_time', 'extra_cost', 'remark']);

        $validator = $this->validationFactory->make(
            $request,
            [
                'task_id' => 'required',
                'business_card_id' => 'required'
            ],
            [
                'task_id.required' => '任务id必须',
                'business_card_id.required' => '请选择名片'
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, $errorMessage);
        }

        $user = $this->request->getAttribute('auth');

        $service = new TaskService();
        $data = $service->find($request['task_id'], ['id', 'task_name', 'task_start_time', 'task_end_time', 'status']);
        if (empty($data)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务不存在');
        }
        if ($data['status'] !== 1 || $data['task_start_time'] > time() || $data['task_end_time'] < time()) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务无效或者任务未开始或已结束');
        }

        $result = TaskCollectionRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => ['in', [0, 1]]], ['status', 'reject_reason']);
        if ($result) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务已申领');
        }

        $count = TaskCollectionRepository::instance()->getCount(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => 3]);
        if ($count >= 3) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务达到最大申领次数');
        }

        $saveData = [
            'task_id' => $request['task_id'],
            'business_card_id' => $request['business_card_id'],
            'level_id' => $request['level_id'],
            'blogger_id' => $user->id,
            'shop_id' => intval($request['shop_id'] ?? 0),
            'shop_name' => $request['shop_name'] ?? '',
            'extra_cost' => bcmul((string)($request['extra_cost'] ?? 0), '100'),
            'remark' => $request['remark'] ?? ''
        ];
        if ($request['reserve_time']) {
            $saveData['reserve_time'] = $request['reserve_time'];
        }

        try {
            TaskCollectionRepository::instance()->saveData($saveData);

            $reservationData = [
                'shop_id' => $saveData['shop_id'],
                'bloger_id' => $saveData['blogger_id'],
                'shop_name' => $saveData['shop_name']
            ];
            if ($request['reserve_time']) {
                $reservationData['reservation_time'] = $request['reserve_time'];
            }
            ReservationRepository::instance()->saveData($reservationData);
        } catch (\Throwable $e) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务受领失败');
        }

        return $this->response->success([], '任务受领成功');
    }

    /**
     * @OA\Post(
     *     path="/wxapi/task/video",
     *     tags={"任务"},
     *     summary="提交任务视频",
     *     description="提交任务视频",
     *     operationId="TaskController_video",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"task_id", "cover", "video_link"},
     *             @OA\Property(property="task_id", type="integer", description="任务ID"),
     *             @OA\Property(property="title", type="string", description="视频标题"),
     *             @OA\Property(property="cover", type="string", description="视频封面 上传图片返回的ID"),
     *             @OA\Property(property="video_link", type="string", description="上传视频返回的ID"),
     *             @OA\Property(property="remark", type="string", description="备注")
     *         )
     *     ),
     *     @OA\Response(response="200", description="返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息")
     *         )
     *     )
     * )
     */
    public function video()
    {
        $request = $this->request->inputs(['task_id', 'title', 'cover', 'video_link', 'remark']);

        $validator = $this->validationFactory->make(
            $request,
            [
                'task_id' => 'required',
                'cover' => 'required',
                'video_link' => 'required'
            ],
            [
                'task_id.required' => '任务id必须',
                'cover.required' => '视频封面必须',
                'video_link.required' => '视频链接必须',
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, $errorMessage);
        }

        $user = $this->request->getAttribute('auth');

        $service = new TaskService();
        $data = $service->find($request['task_id'], ['id', 'task_name', 'status']);
        if (empty($data)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务不存在');
        }
        if ($data['status'] !== 1) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务无效');
        }

        $result = TaskCollectionRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => ['in', [0, 1]]], ['id', 'business_card_id', 'status', 'reject_reason']);
        if (empty($result)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务还未申领');
        }
        if ($result['status'] == 0) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务还在审核中');
        }

        $result1 = VideoRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => ['in', [0, 1]]], ['status']);
        if ($result1) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '视频已提交');
        }

        $saveData = [
            'task_id' => $request['task_id'],
            'business_card_id' => $result['business_card_id'],
            'task_collection_id' => $result['id'],
            'blogger_id' => $user->id,
            'title' => $request['title'] ?? '',
            'cover' => $request['cover'],
            'video_link' => $request['video_link'],
            'remark' => $request['remark'] ?? '',
            'create_time' => time()
        ];

        try {
            VideoRepository::instance()->saveData($saveData);
        } catch (\Throwable $e) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '视频提交失败');
        }

        return $this->response->success([], '视频提交成功');
    }

    /**
     * @OA\Post(
     *     path="/wxapi/task/video/data",
     *     tags={"任务"},
     *     summary="视频数据提交",
     *     description="视频数据提交",
     *     operationId="TaskController_addVideoData",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"task_id", "video_captures"},
     *             @OA\Property(property="task_id", type="integer", description="任务ID"),
     *             @OA\Property(property="video_captures", type="string", description="视频截图 上传图片返回的ID"),
     *             @OA\Property(property="comment_count", type="integer", description="评论数量"),
     *             @OA\Property(property="forward_count", type="integer", description="转发数量"),
     *             @OA\Property(property="play_count", type="integer", description="播放数量"),
     *             @OA\Property(property="remark", type="string", description="备注")
     *         )
     *     ),
     *     @OA\Response(response="200", description="返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息")
     *         )
     *     )
     * )
     */
    public function addVideoData()
    {
        $request = $this->request->inputs(['task_id', 'video_captures', 'comment_count', 'forward_count', 'play_count', 'remark']);

        $validator = $this->validationFactory->make(
            $request,
            [
                'task_id' => 'required',
                'video_captures' => 'required',
            ],
            [
                'task_id.required' => '任务id必须',
                'video_captures.required' => '视频截图必须',
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, $errorMessage);
        }

        $user = $this->request->getAttribute('auth');

        $service = new TaskService();
        $data = $service->find($request['task_id'], ['id', 'task_name', 'status']);
        if (empty($data)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务不存在');
        }
        if ($data['status'] !== 1) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务无效');
        }

        $result1 = VideoRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => ['in', [0, 1]]], ['id', 'task_collection_id', 'status']);
        if (empty($result1)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '视频未提交');
        }
        if ($result1['status'] == 0) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '视频还在审核中');
        }
        if ($result1['is_balance'] == 1) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '视频数据已提交');
        }

        $saveData = [
            'id' => $result1['id'],
            'video_captures' => $request['video_captures'],
            'comment_count' => intval($request['comment_count'] ?? 0),
            'forward_count' => intval($request['forward_count'] ?? 0),
            'play_count' => intval($request['play_count'] ?? 0),
            'is_balance' => 1,
            'remark' => $request['remark'] ?? ''
        ];

        try {
            VideoRepository::instance()->saveData($saveData);

            TaskCollectionRepository::instance()->saveData([
                'id' => $result1['task_collection_id'],
                'is_balance' => 1,
            ]);
        } catch (\Throwable $e) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '视频数据提交失败');
        }

        return $this->response->success([], '视频数据提交成功');
    }

    /**
     * @OA\Post(
     *     path="/wxapi/task/cancel",
     *     tags={"任务"},
     *     summary="任务取消",
     *     description="任务取消",
     *     operationId="TaskController_addVideoData",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"task_id"},
     *             @OA\Property(property="task_id", type="integer", description="任务ID")
     *         )
     *     ),
     *     @OA\Response(response="200", description="返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息")
     *         )
     *     )
     * )
     */
    public function cancel()
    {
        $request = $this->request->inputs(['task_id']);

        $validator = $this->validationFactory->make(
            $request,
            [
                'task_id' => 'required'
            ],
            [
                'task_id.required' => '任务必id须'
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, $errorMessage);
        }

        $service = new TaskService();
        $data = $service->find($request['task_id'], ['id', 'task_name', 'status']);
        if (empty($data)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务不存在');
        }

        $user = $this->request->getAttribute('auth');

        $result = TaskCollectionRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => ['in', [0, 1, 2]]], ['id', 'status', 'reject_reason'], ['id' => 'desc']);
        if (empty($result)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务还未申领');
        }

        if (in_array($result['status'], [2])) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务已被拒绝');
        }

        $saveData = [
            'id' => $result['id'],
            'status' => 3
        ];

        try {
            TaskCollectionRepository::instance()->saveData($saveData);
        } catch (\Throwable $e) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务取消失败');
        }

        return $this->response->success([], '任务取消成功');
    }

    /**
     * @OA\Post(
     *     path="/wxapi/task/video/settle",
     *     tags={"任务"},
     *     summary="费用结算",
     *     description="费用结算",
     *     operationId="TaskController_videoSettle",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"task_id"},
     *             @OA\Property(property="task_id", type="integer", description="任务ID")
     *         )
     *     ),
     *     @OA\Response(response="200", description="返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息")
     *         )
     *     )
     * )
     */
    public function videoSettle()
    {
        $request = $this->request->inputs(['task_id']);

        $validator = $this->validationFactory->make(
            $request,
            [
                'task_id' => 'required'
            ],
            [
                'task_id.required' => '任务id必须'
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, $errorMessage);
        }

        $service = new TaskService();
        $data = $service->find($request['task_id'], ['id', 'task_name', 'status']);
        if (empty($data)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务不存在');
        }

        $user = $this->request->getAttribute('auth');

        $result = TaskCollectionRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => 1], ['extra_cost']);
        $result1 = VideoRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => 1], ['id', 'task_collection_id', 'play_count', 'comment_count', 'forward_count', 'status']);

        if (empty($result) || empty($result1)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务结算失败');
        }

        $saveData = [
            'id' => $result1['id'],
            'is_balance' => 2
        ];

        try {
            VideoRepository::instance()->saveData($saveData);

            TaskCollectionRepository::instance()->saveData([
                'id' => $result1['task_collection_id'],
                'is_balance' => 2,
            ]);
            // 写明细
            BloggerRepository::instance()->addScore($user->id, $result['extra_cost'], '视频推销任务', $request['task_id'], $result1);
        } catch (\Throwable $e) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务结算失败');
        }

        return $this->response->success([], '任务结算成功');
    }

    /**
     * @OA\Post(
     *     path="/wxapi/task/video/upload",
     *     tags={"任务"},
     *     summary="任务视频上传",
     *     description="任务视频上传",
     *     operationId="TaskController_uploadVideo",
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"video"},
     *             @OA\Property(property="video", type="file", description="视频文件"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="信息返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="string", description="视频ID")
     *         )
     *     )
     * )
     */
    public function uploadVideo()
    {
        $file = $this->request->file('video');
        
        if (empty($file)) {
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, '请选择文件');
        }

        $fileService = new FileService();
        $result = $fileService->uploadVideo($file);

        return $this->response->success($result);
    }

    /**
     * @OA\Post(
     *     path="/wxapi/task/visit-shop/sign",
     *     tags={"任务"},
     *     summary="探店签到",
     *     description="探店签到",
     *     operationId="TaskController_visitShopSign",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"task_id"},
     *             @OA\Property(property="task_id", type="integer", description="任务ID")
     *         )
     *     ),
     *     @OA\Response(response="200", description="返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息")
     *         )
     *     )
     * )
     */
    public function visitShopSign()
    {
        $request = $this->request->inputs(['task_id']);

        $validator = $this->validationFactory->make(
            $request,
            [
                'task_id' => 'required'
            ],
            [
                'task_id.required' => '任务必id须'
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, $errorMessage);
        }

        $service = new TaskService();
        $data = $service->find($request['task_id'], ['id', 'task_name', 'status']);
        if (empty($data)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务不存在');
        }

        $user = $this->request->getAttribute('auth');

        $result = TaskCollectionRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => ['in', [0, 1, 2]]], ['id', 'status', 'sign_status'], ['id' => 'desc']);
        if (empty($result)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务还未申领');
        }

        if (in_array($result['status'], [2])) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务已被拒绝');
        }

        if ($result['sign_status'] === 1) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '已签到');
        }

        $saveData = [
            'id' => $result['id'],
            'sign_status' => 1
        ];

        try {
            TaskCollectionRepository::instance()->saveData($saveData);
        } catch (\Throwable $e) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '探店签到失败');
        }

        return $this->response->success([], '探店签到成功');
    }
}
