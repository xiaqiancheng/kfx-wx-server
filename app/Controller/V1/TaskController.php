<?php
namespace App\Controller\V1;

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Exception\BusinessException;
use App\Repositories\BloggerRepository;
use App\Repositories\TagRepository;
use App\Repositories\VideoRepository;
use App\Services\TaskService;
use OpenApi\Annotations as OA;
use App\Repositories\TaskCollectionRepository;
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
     *                          required={"id", "task_name", "task_settle_type", "start_page", "anchor_title", "task_icon", "task_tags", "refer_ma_captures", "profit", "task_start_time", "task_end_time"},
     *                          @OA\Property(property="id", type="integer", description="id"),
     *                          @OA\Property(property="task_name", type="string", description="任务名称"),
     *                          @OA\Property(property="task_settle_type", type="integer", description="结算方式，类型包含：1-广告分成 2-支付交易CPS"),
     *                          @OA\Property(property="start_page", type="string", description="小程序页面地址"),
     *                          @OA\Property(property="anchor_title", type="string", description="锚点标题"),
     *                          @OA\Property(property="task_icon", type="string", description="任务图标"),
     *                          @OA\Property(property="task_tags", type="object", description="任务标签"),
     *                          @OA\Property(property="refer_ma_captures", type="object", description="小程序截图"),
     *                          @OA\Property(property="profit", type="integer", description="最大收益（分）"),
     *                          @OA\Property(property="task_start_time", type="integer", description="任务开始时间，秒级时间戳"),
     *                          @OA\Property(property="task_end_time", type="integer", description="任务结束时间，秒级时间戳")
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

        $list = $service->getList($filter, ['id', 'task_name', 'task_settle_type', 'start_page', 'anchor_title', 'task_icon', 'task_tags', 'refer_ma_captures', 'profit', 'task_start_time', 'task_end_time'],  $page, $pageSize, $sort);

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
     *                 required={"id", "task_name", "task_desc", "audit_requirement", "creative_guidance", "task_settle_type", "task_start_time", "task_end_time", "payment_allocate_ratio", "task_icon", "task_tags", "refer_ma_captures", "commission", "collection_status", "reject_reason", "video_check_status", "is_balance", "max_video_info"},
     *                 @OA\Property(property="id", type="integer", description="任务id"),
     *                 @OA\Property(property="task_name", type="string", description="任务名称"),
     *                 @OA\Property(property="task_desc", type="string", description="任务介绍"),
     *                 @OA\Property(property="audit_requirement", type="string", description="审核要求"),
     *                 @OA\Property(property="creative_guidance", type="string", description="创作指导"),
     *                 @OA\Property(property="task_settle_type", type="integer", description="结算方式，类型包含：1-广告分成、2-支付分成（基础）、3-支付分成（绑定）、7-广告分成+支付分成（基础）、8-广告分成+支付分成（绑定）"),
     *                 @OA\Property(property="task_start_time", type="integer", description="任务开始时间，秒级时间戳"),
     *                 @OA\Property(property="task_end_time", type="integer", description="任务结束时间，秒级时间戳"),
     *                 @OA\Property(property="payment_allocate_ratio", type="float", description="达人分成比例，百分比"),
     *                 @OA\Property(property="task_icon", type="string", description="任务图标"),
     *                 @OA\Property(property="task_tags", type="object", description="任务标签"),
     *                 @OA\Property(property="refer_ma_captures", type="object", description="小程序截图"),
     *                 @OA\Property(property="commission", type="integer", description="额外奖励（分）"),
     *                 @OA\Property(property="collection_status", type="integer", description="任务领取审核状态 -1未领取 0待审核，1已审核，2审核未通过"),
     *                 @OA\Property(property="reject_reason", type="string", description="任务审核拒绝原因"),
     *                 @OA\Property(property="reject_time", type="string", description="任务审核拒绝时间"),
     *                 @OA\Property(property="video_check_status", type="integer", description="视频审核状态 -1未提交视频 0待审核 1已审核 2已拒绝"),
     *                 @OA\Property(property="is_balance", type="integer", description="是否结算  0任务中 1待结算 2已结算"),
     *                 @OA\Property(property="max_video_info", type="array", description="视频榜单数据",
     *                     @OA\Items(type="object", 
     *                          required={"id", "cover", "play_count", "forward_count"},
     *                          @OA\Property(property="id", type="integer", description="视频榜单id"),
     *                          @OA\Property(property="cover", type="string", description="封面图"),
     *                          @OA\Property(property="play_count", type="integer", description="播放量"),
     *                          @OA\Property(property="forward_count", type="integer", description="收藏转发量")
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

        $data = $service->find($taskId, ['id', 'task_name', 'task_desc', 'task_settle_type', 'task_start_time', 'task_end_time', 'task_icon', 'task_tags', 'refer_ma_captures', 'commission']);
    
        $data['payment_allocate_ratio'] = 1; // 达人分成比例
        $data['audit_requirement'] = '审核要求'; // 审核要求
        $data['creative_guidance'] = '创作指导'; // 创作指导

        // 任务领取状态
        $data['collection_status'] = -1;
        $data['reject_reason'] = '';
        // 视频审核状态
        $data['video_check_status'] = -1;
        $data['is_balance'] = -1;

        if ($userInfo) {
            $result = TaskCollectionRepository::instance()->findOneBy(['task_id' => $taskId, 'blogger_id' => $userInfo->id], ['status', 'reject_reason', 'updated_at'], ['id' => 'desc']);
            if ($result) {
                $data['collection_status'] = $result['status'];
                $data['reject_reason'] = $result['reject_reason'];
                $data['reject_time'] = $result['updated_at'];
            }

            $result1 = VideoRepository::instance()->findOneBy(['task_id' => $taskId, 'blogger_id' => $userInfo->id], ['status', 'is_balance'], ['id' => 'desc']);
            if ($result1) {
                $data['video_check_status'] = $result1['status'];
                $data['is_balance'] = $result1['is_balance'];
            }
        }

        // 视频榜单
        $videoList = VideoRepository::instance()->getList(['task_id' => $taskId, 'status' => 1], ['id', 'cover', 'play_count', 'forward_count'], 1, 100, ['income' => 'desc']);
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
     *             required={"task_id", "reserve_time"},
     *             @OA\Property(property="task_id", type="integer", description="任务ID"),
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
        $request = $this->request->inputs(['task_id', 'shop_id', 'shop_name', 'reserve_time', 'extra_cost', 'remark']);

        $validator = $this->validationFactory->make(
            $request,
            [
                'task_id' => 'required',
                'reserve_time' => 'required'
            ],
            [
                'task_id.required' => '任务id必须',
                'reserve_time.required' => '预约时间必须'
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

        $result = TaskCollectionRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => ['in', [0, 1]]], ['status', 'reject_reason']);
        if ($result) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务已申领');
        }

        $saveData = [
            'task_id' => $request['task_id'],
            'blogger_id' => $user->id,
            'shop_id' => intval($request['shop_id'] ?? 0),
            'shop_name' => $request['shop_name'] ?? '',
            'reserve_time' => $request['reserve_time'],
            'extra_cost' => bcmul((string)($request['extra_cost'] ?? 0), '100'),
            'remark' => $request['remark'] ?? ''
        ];

        try {
            TaskCollectionRepository::instance()->saveData($saveData);
        } catch (\Throwable $e) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务申领失败');
        }

        return $this->response->success([], '任务申领成功');
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
     *             @OA\Property(property="cover", type="string", description="视频封面链接"),
     *             @OA\Property(property="video_link", type="string", description="视频链接"),
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

        $result = TaskCollectionRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => ['in', [0, 1]]], ['status', 'reject_reason']);
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
     *             @OA\Property(property="video_captures", type="string", description="视频截图"),
     *             @OA\Property(property="comment_count", type="integer", description="评论数量"),
     *             @OA\Property(property="forward_count", type="integer", description="转发数量"),
     *             @OA\Property(property="play_count", type="integer", description="播放数量")
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
        $request = $this->request->inputs(['task_id', 'video_captures', 'comment_count', 'forward_count', 'play_count']);

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

        $result1 = VideoRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => ['in', [0, 1]]], ['id', 'status']);
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
            'is_balance' => 1
        ];

        try {
            VideoRepository::instance()->saveData($saveData);
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

        $result = TaskCollectionRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => ['in', [0, 1, 2]]], ['id', 'status', 'reject_reason'], ['id' => 'desc']);
        if (empty($result)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务还未申领');
        }

        if (in_array($result['status'], [1, 2])) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务已审核或者任务已被拒绝');
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
        $result1 = VideoRepository::instance()->findOneBy(['task_id' => $request['task_id'], 'blogger_id' => $user->id, 'status' => 1], ['id', 'status']);

        $saveData = [
            'id' => $result1['id'],
            'is_balance' => 2
        ];

        try {
            VideoRepository::instance()->saveData($saveData);

            // 写明细
            BloggerRepository::instance()->addScore($user->id, $result['extra_cost'], '视频推销任务', $request['task_id']);
        } catch (\Throwable $e) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '任务结算失败');
        }

        return $this->response->success([], '任务结算成功');
    }
}
