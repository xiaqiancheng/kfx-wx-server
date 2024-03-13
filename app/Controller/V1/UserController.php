<?php
namespace App\Controller\V1;

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Exception\BusinessException;
use App\Repositories\BloggerBusinessCardRepository;
use App\Repositories\BloggerRepository;
use App\Repositories\MessageNoticeRepository;
use App\Repositories\TaskCollectionRepository;
use App\Services\UserService;
use OpenApi\Annotations as OA;
use HyperfExt\Auth\AuthManager;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use App\Repositories\VideoRepository;
use App\Repositories\TaskRepository;
use App\Repositories\UserIncomeDetailRepository;
use App\Services\QRcodeService;
use Endroid\QrCode\QrCode;

class UserController extends AbstractController
{
    /**
     * @Inject
     * @var AuthManager
     */
    protected $auth;

    /**
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    /**
     * @OA\Post(
     *     path="/wxapi/user/login",
     *     tags={"用户"},
     *     summary="登录",
     *     description="登录",
     *     operationId="UserController_login",
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"code"},
     *             @OA\Property(property="code", type="string", description="登录时获取的code")
     *         )
     *     ),
     *     @OA\Response(response="200", description="登录返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"token", "openid", "unionid", "avatarUrl", "nickName", "level", "income", "is_authorize_user", "is_authorize_phone", "is_douyin_authorize"},
     *                 @OA\Property(property="token", type="string", description="登录凭证"),
     *                 @OA\Property(property="openid", type="string", description="openid"),
     *                 @OA\Property(property="unionid", type="string", description="unionid"),
     *                 @OA\Property(property="avatarUrl", type="string", description="头像"),
     *                 @OA\Property(property="nickName", type="string", description="昵称"),
     *                 @OA\Property(property="level", type="integer", description="级别"),
     *                 @OA\Property(property="income", type="integer", description="收入（分）"),
     *                 @OA\Property(property="is_authorize_user", type="integer", description="是否授权用户信息0未授权 1已授权"),
     *                 @OA\Property(property="is_authorize_phone", type="integer", description="是否绑定手机 0未绑定 1已绑定"),
     *                 @OA\Property(property="is_douyin_authorize", type="integer", description="抖音授权 0否 1是")
     *             )
     *         )
     *     )
     * )
     */
    public function login() 
    {
        $request = $this->request->inputs(['code', 'user_info', 'raw_data', 'signature', 'encrypted_data', 'iv']);
        $validator = $this->validationFactory->make(
            $request,
            [
                'code' => 'required'
            ],
            [
                'code.required' => '用户登录凭证必须'
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, $errorMessage);
        }
        $token = $this->auth->guard('api')->attempt($request);

        $user = auth('api')->user();
        $userInfo = BloggerRepository::instance()->find($user->id, ['id', 'openid', 'unionid', 'avatarUrl', 'name', 'nickName', 'level', 'income', 'is_authorize_user', 'is_authorize_phone', 'doyin_id']);
        
        return $this->response->success([
            'token' => $token,
            'openid' => $userInfo['openid'],
            'unionid' => $userInfo['unionid'],
            'avatarUrl' => $userInfo['avatarUrl'],
            'nickName' => $userInfo['nickName'],
            'level' => $userInfo['level'],
            'income' => $userInfo['income'],
            'is_authorize_user' => $userInfo['is_authorize_user'],
            'is_authorize_phone' => $userInfo['is_authorize_phone'],
            'is_douyin_authorize' => empty($userInfo['doyin_id']) ? 0 : 1
        ]);
    }

    /**
     * @OA\Post(
     *     path="/wxapi/user/profile",
     *     tags={"用户"},
     *     summary="用户信息授权更新",
     *     description="用户信息授权更新",
     *     operationId="UserController_profile",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"encrypted_data", "iv"},
     *             @OA\Property(property="encrypted_data", type="string", description="包括敏感数据在内的完整用户信息的加密数据"),
     *             @OA\Property(property="iv", type="string", description="加密算法的初始向量")
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
    public function profile() 
    {
        $request = $this->request->inputs(['encrypted_data', 'iv']);
        $validator = $this->validationFactory->make(
            $request,
            [
                'encrypted_data' => 'required',
                'iv' => 'required',
            ],
            [
                'encrypted_data.required' => '加密数据信息不能为空',
                'iv.required' => 'iv不能为空',
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, $errorMessage);
        }

        $userId = $this->request->getAttribute('auth')->id;

        $service = new UserService();
        $service->profile($userId, $request['iv'], $request['encrypted_data']);

        return $this->response->success([], '用户信息更新成功');
    }

    /**
     * @OA\Get(
     *     path="/wxapi/user/info",
     *     tags={"用户"},
     *     summary="用户资料",
     *     description="用户资料",
     *     operationId="UserController_info",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\Response(response="200", description="信息返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"openid", "unionid", "avatarUrl", "nickName", "level", "income", "is_douyin_authorize"},
     *                 @OA\Property(property="openid", type="string", description="openid"),
     *                 @OA\Property(property="unionid", type="string", description="unionid"),
     *                 @OA\Property(property="avatarUrl", type="string", description="头像"),
     *                 @OA\Property(property="nickName", type="string", description="昵称"),
     *                 @OA\Property(property="level", type="integer", description="级别"),
     *                 @OA\Property(property="income", type="integer", description="收入（分）"),
     *                 @OA\Property(property="is_douyin_authorize", type="integer", description="抖音授权 0否 1是")
     *             )
     *         )
     *     )
     * )
     */
    public function info()
    {
        $user = $this->request->getAttribute('auth');

        return $this->response->success([
            'openid' => $user->openid,
            'unionid' => $user->unionid,
            'avatarUrl' => $user->avatarUrl,
            'nickName' => $user->nickName,
            'level' => $user->level,
            'income' => $user->income,
            'is_douyin_authorize' => empty($user->doyin_id) ? 0 : 1
        ]);
    }

    /**
     * @OA\Post(
     *     path="/wxapi/user/dyauth-code",
     *     tags={"用户"},
     *     summary="生成抖音授权码",
     *     description="生成抖音授权码",
     *     operationId="UserController_douyinAuthCode",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\Response(response="200", description="信息返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="string", description="base64")
     *         )
     *     )
     * )
     */
    public function douyinAuthCode()
    {
        $user = $this->request->getAttribute('auth');

        $url = env('HOST') . '/dy_authorize/?code=' . urlencode($user->openid);

		$qr = new QRcodeService;
        $base64 = $qr->create($url);

        return $this->response->success($base64);
    }

    /**
     * @OA\Get(
     *     path="/wxapi/user/income/list",
     *     tags={"用户"},
     *     summary="收益明细",
     *     description="收益明细",
     *     operationId="UserController_incomeList",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\Parameter(name="page", in="query", description="页码 1开始",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Parameter(name="page_size", in="query", description="每页数量",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Response(response="200", description="收益列表返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"total_count", "list"},
     *                 @OA\Property(property="list", type="array", description="收益数据",
     *                     @OA\Items(type="object", 
     *                          required={"task_id", "date", "name", "amount"},
     *                          @OA\Property(property="task_id", type="integer", description="任务id"),
     *                          @OA\Property(property="date", type="string", description="日期"),
     *                          @OA\Property(property="name", type="string", description="名称"),
     *                          @OA\Property(property="amount", type="integer", description="收益（分）")
     *                      )
     *                 ),
     *                 @OA\Property(property="total_count", type="integer", description="总数量")
     *             )
     *         )
     *     )
     * )
     */
    public function incomeList() 
    {
        $page = $this->request->input('page', 1);
        $pageSize = $this->request->input('page_size', 20);

        $user = $this->request->getAttribute('auth');

        $list = UserIncomeDetailRepository::instance()->getList(['blogger_id' => $user->id], ['id', 'task_id', 'created_at as date', 'name', 'amount'], $page, $pageSize, ['id' => 'desc']);
        return $this->response->success($list);
    }

    /**
     * @OA\Get(
     *     path="/wxapi/user/notice/list",
     *     tags={"用户"},
     *     summary="消息通知列表",
     *     description="消息通知列表",
     *     operationId="UserController_noticeList",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\Parameter(name="page", in="query", description="页码 1开始",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Parameter(name="page_size", in="query", description="每页数量",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Response(response="200", description="通知列表返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"total_count", "list"},
     *                 @OA\Property(property="list", type="array", description="通知数据",
     *                     @OA\Items(type="object", 
     *                          required={"id", "task_id", "time", "name", "description", "status"},
     *                          @OA\Property(property="id", type="integer", description="消息id"),
     *                          @OA\Property(property="task_id", type="integer", description="任务id"),
     *                          @OA\Property(property="time", type="string", description="时间"),
     *                          @OA\Property(property="name", type="string", description="名称"),
     *                          @OA\Property(property="description", type="string", description="描述"),
     *                          @OA\Property(property="status", type="integer", description="读取状态 0未读 1已读")
     *                      )
     *                 ),
     *                 @OA\Property(property="total_count", type="integer", description="总数量")
     *             )
     *         )
     *     )
     * )
     */
    public function noticeList() 
    {
        $page = $this->request->input('page', 1);
        $pageSize = $this->request->input('page_size', 20);

        $user = $this->request->getAttribute('auth');

        $list = MessageNoticeRepository::instance()->getList(['blogger_id' => $user->id], ['id', 'task_id', 'created_at as time', 'name', 'description', 'status'], $page, $pageSize, ['id' => 'desc']);
        return $this->response->success($list);
    }

    /**
     * @OA\Get(
     *     path="/wxapi/user/video/list",
     *     tags={"用户"},
     *     summary="视频列表",
     *     description="视频列表",
     *     operationId="UserController_noticeList",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\Parameter(name="page", in="query", description="页码 1开始",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Parameter(name="page_size", in="query", description="每页数量",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Parameter(name="status", in="query", description="状态 1.待审核 2.待提交 4.已驳回 5.已取消 6.已完成",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Response(response="200", description="视频列表返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"total_count", "list"},
     *                 @OA\Property(property="list", type="array", description="通知数据",
     *                     @OA\Items(type="object", 
     *                          required={"id", "task_id", "task_type", "task_name", "task_icon", "cover", "play_count", "digg_count", "forward_count", "task_status", "release_start_time", "release_end_time", "sign_status"},
     *                          @OA\Property(property="id", type="integer", description="视频id"),
     *                          @OA\Property(property="task_id", type="integer", description="任务id"),
     *                          @OA\Property(property="task_type", type="integer", description="任务类型 1普通任务 2探店任务"),
     *                          @OA\Property(property="task_name", type="string", description="任务名称"),
     *                          @OA\Property(property="task_icon", type="string", description="任务图标"),
     *                          @OA\Property(property="cover", type="string", description="封面图"),
     *                          @OA\Property(property="play_count", type="integer", description="播放数量"),
     *                          @OA\Property(property="digg_count", type="integer", description="点赞数"),
     *                          @OA\Property(property="forward_count", type="integer", description="转发数"),
     *                          @OA\Property(property="task_status", type="integer", description="状态 1.待审核 2.待提交 4.已驳回 5.已取消 6.已完成"),
     *                          @OA\Property(property="release_start_time", type="datetime", description="发布开始时间"),
     *                          @OA\Property(property="release_end_time", type="datetime", description="发布结束时间"),
     *                          @OA\Property(property="sign_status", type="integer", description="探店签到状态 0未签到 1已签到")
     *                      )
     *                 ),
     *                 @OA\Property(property="total_count", type="integer", description="总数量")
     *             )
     *         )
     *     )
     * )
     */
    public function videoList()
    {
        // 1.待审核：展示申领待审核的任务以及视频待审核的任务
        // 2.待提交：展示待提交视频的任务以及待提交视频数据的任务
        // 3.待结算：展示待结算的任务
        // 4.已驳回：展示申领审核失败的任务以及视频审核失败的任务
        // 5.已取消：展示用户取消的任务
        // 6.已完成：展示已完成的任务

        $page = $this->request->input('page', 1);
        $pageSize = $this->request->input('page_size', 20);
        $status = $this->request->input('status', 0); // 是否结算 1任务中 2待结算 3已结算

        $user = $this->request->getAttribute('auth');

        $filter = [
            'blogger_id' => $user->id
        ];
        // 待审核
        if ($status == 1) {
            $filter['status'] = ['in', [0, 1]];
            $filter['video_status'] = 0;
        }

        // 待提交
        if ($status == 2) {
            $filter['video_status'] = 1;
            $filter['is_balance'] = 0;
        }

        // 待结算
        // if ($status == 3) {
        //     $filter['is_balance'] = 1;
        // }

        // 已驳回
        if ($status == 4) {
            $filter['status'] = 2;
            $filter['video_status'] = ['or', 2];
        }

        // 已取消
        if ($status == 5) {
            $filter['status'] = 3;
        }

        // 已完成
        if ($status == 6) {
            $filter['is_balance'] = 1;
        }
        
        $taskCollectionList = TaskCollectionRepository::instance()->getList($filter, ['id', 'task_id', 'blogger_id', 'status', 'video_status', 'is_balance' ,'sign_status'], $page, $pageSize, ['id' => 'desc']);

        $taskIds = array_unique(array_column($taskCollectionList['list'], 'task_id'));

        $taskList = TaskRepository::instance()->getList(['id' => ['in', $taskIds]], ['id', 'task_name', 'task_type', 'task_icon'], 0, 0);
        $taskIDForKey = array_column($taskList['list'], null, 'id');

        foreach ($taskCollectionList['list'] as &$value) {
            // 判断状态
            if (in_array($value['status'], [0, 1]) && $value['video_status'] == 0) {
                $value['task_status'] = 1;
            }
            if ($value['video_status'] == 1 && $value['is_balance'] == 0) {
                $value['task_status'] = 2;
            }
            if ($value['is_balance'] == 1) {
                $value['task_status'] = 6;
            }
            if ($value['status'] == 2 || $value['video_status'] == 2) {
                $value['task_status'] = 4;
            }
            if ($value['status'] == 3) {
                $value['task_status'] = 5;
            }
            // if ($value['is_balance'] == 2) {
            //     $value['task_status'] = 6;
            // }
            
            $value['task_type'] = $taskIDForKey[$value['task_id']]['task_type'];
            $value['task_name'] = $taskIDForKey[$value['task_id']] ? $taskIDForKey[$value['task_id']]['task_name'] : '';
            $value['task_icon'] = $taskIDForKey[$value['task_id']] ? $taskIDForKey[$value['task_id']]['task_icon'] : '';

            $result1 = VideoRepository::instance()->findOneBy(['task_collection_id' => $value['id'], 'blogger_id' => $value['blogger_id'], 'status' => $value['video_status'], 'is_balance' => $value['is_balance']], ['cover', 'play_count', 'comment_count', 'forward_count', 'release_start_time', 'release_end_time']);
            $value['cover'] = $result1['cover'] ?? '';
            $value['play_count'] = $result1['play_count'] ?? 0;
            $value['comment_count'] = $result1['comment_count'] ?? 0;
            $value['forward_count'] = $result1['forward_count'] ?? 0;
            $value['release_start_time'] = $result1['release_start_time'] ?? '';
            $value['release_end_time'] = $result1['release_end_time'] ?? '';
            unset($value['status'], $value['video_status'], $value['is_balance']);
        }

        return $this->response->success($taskCollectionList);
    }

    /**
     * @OA\Post(
     *     path="/wxapi/user/notice/read",
     *     tags={"用户"},
     *     summary="设置消息已读",
     *     description="设置消息已读",
     *     operationId="UserController_noticeRead",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"notice_id"},
     *             @OA\Property(property="notice_id", type="integer", description="消息id")
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
    public function noticeRead()
    {
        $user = $this->request->getAttribute('auth');

        $noticeId = $this->request->input('notice_id');
        if (!$noticeId) {
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, '消息ID必须');
        }

        MessageNoticeRepository::instance()->updateOneBy(['blogger_id' => $user->id, 'id' => $noticeId], ['status' => 1]);

        return $this->response->success([], '成功');
    }

    /**
     * @OA\Post(
     *     path="/wxapi/user/profile/update",
     *     tags={"用户"},
     *     summary="修改用户基本信息",
     *     description="修改用户基本信息",
     *     operationId="UserController_profileUpdate",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={},
     *             @OA\Property(property="nickName", type="string", description="昵称"),
     *             @OA\Property(property="avatarUrl", type="string", description="头像"),
     *             @OA\Property(property="name", type="string", description="姓名"),
     *             @OA\Property(property="id_card", type="string", description="身份证"),
     *             @OA\Property(property="alipay_account", type="string", description="支付宝号"),
     *             @OA\Property(property="bank_card_number", type="string", description="银行卡号"),
     *             @OA\Property(property="open_bank", type="string", description="开户行")
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
    public function profileUpdate()
    {
        $request = $this->request->inputs(['nickName', 'avatarUrl', 'name', 'id_card', 'alipay_account', 'bank_card_number', 'open_bank']);
        $userId = $this->request->getAttribute('auth')->id;

        $data = [];
        if (($request['nickName'] ?? '') && $request['nickName']) {
            $data['nickName'] = $request['nickName'];
        }
        if (($request['avatarUrl'] ?? '') && $request['avatarUrl']) {
            $data['avatarUrl'] = $request['avatarUrl'];
        }
        if (($request['name'] ?? '') && $request['name']) {
            $data['name'] = $request['name'];
        }
        if (($request['id_card'] ?? '') && $request['id_card']) {
            $data['id_card'] = $request['id_card'];
        }
        if (($request['alipay_account'] ?? '') && $request['alipay_account']) {
            $data['alipay_account'] = $request['alipay_account'];
        }
        if (($request['bank_card_number'] ?? '') && $request['bank_card_number']) {
            $data['bank_card_number'] = $request['bank_card_number'];
        }
        if (($request['open_bank'] ?? '') && $request['open_bank']) {
            $data['open_bank'] = $request['open_bank'];
        }

        if ($data) {
            $data['id'] = $userId;
            $data['update_time'] = time();
            BloggerRepository::instance()->saveData($data);
        }

        return $this->response->success([], '用户信息更新成功');
    }

    /**
     * @OA\Get(
     *     path="wxapi/dy/info",
     *     tags={"用户"},
     *     summary="获取抖音信息",
     *     description="获取抖音信息",
     *     operationId="UserController_info",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\Parameter(name="url", in="query", description="链接", required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="信息返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"uid", "nickname", "avatar", "fans_count", "digg_count", "level"},
     *                 @OA\Property(property="uid", type="string", description="抖音ID"),
     *                 @OA\Property(property="nickname", type="string", description="昵称"),
     *                 @OA\Property(property="avatar", type="string", description="头像"),
     *                 @OA\Property(property="fans_count", type="integer", description="粉丝数"),
     *                 @OA\Property(property="digg_count", type="integer", description="点赞数"),
     *                 @OA\Property(property="level", type="integer", description="等级")
     *             )
     *         )
     *     )
     * )
     */
    public function getDouYinInfo()
    {
        $url = $this->request->input('url', '');
        if (empty($url)) {
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, 'URL不能为空');
        }

        $time = time();
        $secondstime = getMilliseconds();
        $redirect_url = get_redirect_url($url);
        $parts = parse_url($redirect_url);
        $path = $parts['path'];
        $userPos = strpos($path, 'user/');
        $sec_user_id = substr($path, $userPos + 5); // 5是'user/'的长度
        $info_url = 'https://m.douyin.com/web/api/v2/user/info/?reflow_source=reflow_page&sec_uid=' . $sec_user_id;
        $output = file_get_contents($info_url);
        $res = json_decode($output, true);
        if (isset($res['user_info'])) {
            $result = $res['user_info'];
            $feiguasearch='https://dy.feigua.cn/api/v1/other/navSearch/blogger?keyWord='.$result['unique_id'].'&_='.$secondstime.'';

            $resfg = feiguaUrl($feiguasearch);
            $query_string = parse_url($resfg['Data']['BloggerResult']['List'][0]['DetailUrl'], PHP_URL_FRAGMENT); // 这将返回"#"后面的部分
            $query_string = ltrim($query_string, '#'); // 移除开头的"#"// 现在我们将查询字符串解析为关联数组
            parse_str($query_string, $query_params);// 提取bloggerId和sign的值
            $bloggerId = isset($query_params['/blogger-detail/index?bloggerId']) ? $query_params['/blogger-detail/index?bloggerId'] : null;
            $sign = isset($query_params['sign']) ? $query_params['sign'] : null;
            $ts = isset($query_params['ts']) ? $query_params['ts'] : null;
            $mainpart='https://dy.feigua.cn/api/v1/bloggerdetailoverview/detail/mainpart?id='.$bloggerId.'&sign='.$sign.'&ts='.$ts.'&_='.$secondstime.'';
            $otherpart = 'https://dy.feigua.cn/api/v1/bloggerdetailoverview/detail/otherpart?id='.$bloggerId.'&sign='.$sign.'&ts='.$time.'&_='.$secondstime.'';
            $resMainpart = feiguaUrl($mainpart);
            $avatarDomain = '';
            $avatar = isset($resMainpart['Data']) ? $resMainpart['Data']['Avatar'] : '';
            if ($avatar) {
                $parsed_url = parse_url($avatar);
                $avatarDomain = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];
            }
            $resOtherpart = feiguaUrl($otherpart);
            if (!$resOtherpart['Status'] || !$resMainpart['Status']) {
                throw new BusinessException(ErrorCode::SERVER_ERROR, '获取失败，请稍后再试');
            }
            return $this->response->success([
                'uid' => $res['user_info']['unique_id'],
                'nickname' => $res['user_info']['nickname'],
                'avatar' => $avatarDomain,
                'fans_count' => $res['user_info']['mplatform_followers_count'],
                'digg_count' => $res['user_info']['total_favorited'],
                'level' => $resOtherpart['Data']['SellGoodsLevelInt'] ?? 0,
            ], '获取信息成功');
        } else {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '获取失败，请稍后再试');
        }
    }

    /**
     * @OA\Post(
     *     path="/wxapi/user/business-card/add",
     *     tags={"用户"},
     *     summary="添加抖音名片",
     *     description="添加抖音名片",
     *     operationId="UserController_businessCardAdd",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"url", "douyin_id"},
     *             @OA\Property(property="url", type="string", description="主页链接"),
     *             @OA\Property(property="douyin_id", type="string", description="抖音ID"),
     *             @OA\Property(property="nickname", type="string", description="昵称"),
     *             @OA\Property(property="avatar", type="string", description="头像"),
     *             @OA\Property(property="fans_count", type="integer", description="粉丝数"),
     *             @OA\Property(property="digg_count", type="integer", description="点赞数"),
     *             @OA\Property(property="level_id", type="integer", description="等级ID")
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
    public function businessCardAdd()
    {
        $request = $this->request->inputs(['url', 'nickname', 'avatar', 'douyin_id', 'fans_count', 'digg_count', 'level_id']);
        $userId = $this->request->getAttribute('auth')->id;

        $validator = $this->validationFactory->make(
            $request,
            [
                'url' => 'required',
                'douyin_id' => 'required'
            ],
            [
                'url.required' => '主页链接必须',
                'douyin_id.required' => '抖音ID必须'
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, $errorMessage);
        }

        $data['blogger_id'] = $userId;
        $data['douyin_id'] = $request['douyin_id'];
        $data['url'] = $request['url'];

        if (($request['nickname'] ?? '') && $request['nickname']) {
            $data['nickname'] = $request['nickname'];
        }
        if (($request['avatar'] ?? '') && $request['avatar']) {
            $data['avatar'] = $request['avatar'];
        }
        if (($request['fans_count'] ?? '') && $request['fans_count']) {
            $data['fans_count'] = $request['fans_count'];
        }
        if (($request['digg_count'] ?? '') && $request['digg_count']) {
            $data['digg_count'] = $request['digg_count'];
        }
        if (($request['level_id'] ?? '') && $request['level_id']) {
            $data['level_id'] = $request['level_id'];
        }
        $res = BloggerBusinessCardRepository::instance()->findOneBy([
            'blogger_id' => $userId,
            'douyin_id' => $request['douyin_id']
        ]);
        if ($res) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '此账号已添加');
        }

        BloggerBusinessCardRepository::instance()->saveData($data);

        return $this->response->success([], '添加名片成功');
    }

    /**
     * @OA\Get(
     *     path="/wxapi/user/business-card/list",
     *     tags={"用户"},
     *     summary="获取名片列表",
     *     description="获取名片列表",
     *     operationId="UserController_getBusinessCardList",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\Response(response="200", description="名片列表返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"list"},
     *                 @OA\Property(property="list", type="array", description="任务数据",
     *                     @OA\Items(type="object",
     *                          required={"id", "url", "douyin_id", "nickname", "avatar", "fans_count", "digg_count", "level_id"},
     *                          @OA\Property(property="id", type="integer", description="名片ID"),
     *                          @OA\Property(property="url", type="string", description="主页链接"),
     *                          @OA\Property(property="douyin_id", type="string", description="抖音ID"),
     *                          @OA\Property(property="nickname", type="string", description="昵称"),
     *                          @OA\Property(property="avatar", type="string", description="头像"),
     *                          @OA\Property(property="fans_count", type="integer", description="粉丝数"),
     *                          @OA\Property(property="digg_count", type="integer", description="点赞数"),
     *                          @OA\Property(property="level_id", type="integer", description="等级")
     *                      )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getBusinessCardList()
    {
        $userId = $this->request->getAttribute('auth')->id;

        $filter['blogger_id'] = $userId;

        $list = BloggerBusinessCardRepository::instance()->getList($filter, ['id', 'url', 'douyin_id', 'nickname', 'avatar', 'fans_count', 'digg_count', 'level_id'], 0, 0, ['id' => 'desc'], [], false);

        return $this->response->success($list);
    }

    /**
     * @OA\Post(
     *     path="user/business-card/del/{cardId}",
     *     tags={"用户"},
     *     summary="删除名片",
     *     description="删除名片",
     *     operationId="UserController_businessCardDel",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\Parameter(name="cardId", in="path", description="名片ID",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Response(response="200", description="结果返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息")
     *         )
     *     )
     * )
     */
    public function businessCardDel($cardId)
    {
        BloggerBusinessCardRepository::instance()->deleteByIds($cardId);

        return $this->response->success([], '删除名片成功');
    }

    /**
     * @OA\Post(
     *     path="/wxapi/user/business-card/edit/{cardId}",
     *     tags={"用户"},
     *     summary="更新抖音名片",
     *     description="更新抖音名片",
     *     operationId="UserController_businessCardEdit",
     *     @OA\Parameter(name="Authorization", in="header", description="jwt签名", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\Parameter(name="cardId", in="path", description="名片ID",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"url", "douyin_id"},
     *             @OA\Property(property="douyin_id", type="string", description="抖音ID"),
     *             @OA\Property(property="nickname", type="string", description="昵称"),
     *             @OA\Property(property="avatar", type="string", description="头像"),
     *             @OA\Property(property="fans_count", type="integer", description="粉丝数"),
     *             @OA\Property(property="digg_count", type="integer", description="点赞数"),
     *             @OA\Property(property="level_id", type="integer", description="等级ID")
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
    public function businessCardEdit($cardId)
    {
        $request = $this->request->inputs(['nickname', 'avatar', 'douyin_id', 'fans_count', 'digg_count', 'level_id']);

        $validator = $this->validationFactory->make(
            $request,
            [
                'douyin_id' => 'required'
            ],
            [
                'douyin_id.required' => '抖音ID必须'
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, $errorMessage);
        }

        $cardData = BloggerBusinessCardRepository::instance()->find($cardId, ['blogger_id', 'douyin_id']);
        if (empty($cardData)) {
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, '名片不存在');
        }

        $userId = $this->request->getAttribute('auth')->id;
        if ($request['douyin_id'] !== $cardData['douyin_id'] || $cardData['blogger_id'] !== $userId) {
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, '名片不允许更新');
        }

        $data = [
            'id' => $cardId
        ];
        if (($request['nickname'] ?? '') && $request['nickname']) {
            $data['nickname'] = $request['nickname'];
        }
        if (($request['avatar'] ?? '') && $request['avatar']) {
            $data['avatar'] = $request['avatar'];
        }
        if (($request['fans_count'] ?? '') && $request['fans_count']) {
            $data['fans_count'] = $request['fans_count'];
        }
        if (($request['digg_count'] ?? '') && $request['digg_count']) {
            $data['digg_count'] = $request['digg_count'];
        }
        if (($request['level_id'] ?? '') && $request['level_id']) {
            $data['level_id'] = $request['level_id'];
        }

        BloggerBusinessCardRepository::instance()->saveData($data);

        return $this->response->success([], '名片更新成功');
    }
}