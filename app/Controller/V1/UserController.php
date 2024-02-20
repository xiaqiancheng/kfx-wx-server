<?php
namespace App\Controller\V1;

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Exception\BusinessException;
use App\Services\UserService;
use OpenApi\Annotations as OA;
use HyperfExt\Auth\AuthManager;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

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
     *                 required={"token", "is_authorize_user", "is_authorize_phone"},
     *                 @OA\Property(property="token", type="string", description="登录凭证"),
     *                 @OA\Property(property="is_authorize_user", type="integer", description="是否授权用户信息0未授权 1已授权"),
     *                 @OA\Property(property="is_authorize_phone", type="integer", description="是否绑定手机 0未绑定 1已绑定")
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
        return $this->response->success(['token' => $token, 'is_authorize_user' => $user->is_authorize_user, 'is_authorize_phone' => $user->is_authorize_phone]);
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
     *                 required={"avatarUrl", "nickName", "level", "income"},
     *                 @OA\Property(property="avatarUrl", type="string", description="头像"),
     *                 @OA\Property(property="nickName", type="string", description="昵称"),
     *                 @OA\Property(property="level", type="integer", description="级别"),
     *                 @OA\Property(property="income", type="integer", description="收入（分）")
     *             )
     *         )
     *     )
     * )
     */
    public function info()
    {
        $user = $this->request->getAttribute('auth');

        return $this->response->success([
            'avatarUrl' => $user->avatarUrl,
            'nickName' => $user->nickName,
            'level' => $user->level,
            'income' => $user->income
        ]);
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
     *                          required={"date", "name", "amount"},
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

        return $this->response->success([
            'list' => [
                [
                    'date' => '2024-01-06',
                    'name' => '视频推销任务6',
                    'amount' => 5000
                ],
                [
                    'date' => '2024-01-05',
                    'name' => '视频推销任务1',
                    'amount' => 2000
                ],
                [
                    'date' => '2024-01-04',
                    'name' => '视频推销任务2',
                    'amount' => 3000
                ],
                [
                    'date' => '2024-01-03',
                    'name' => '视频推销任务',
                    'amount' => 1200
                ],
                [
                    'date' => '2024-01-02',
                    'name' => '视频推销任务1',
                    'amount' => 5000
                ],
            ],
            'total_count' => 5
        ]);
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
     *                          required={"id", "time", "name", "description", "status"},
     *                          @OA\Property(property="id", type="integer", description="消息id"),
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

        return $this->response->success([
            'list' => [
                [
                    'id' => 1,
                    'time' => '2024-01-06 20:39',
                    'name' => '视频审核通过',
                    'description' => '视频审核通过视频审核通过视频审核通过视频审核通过',
                    'status' => 0
                ],
                [
                    'id' => 2,
                    'time' => '2024-01-05 20:39',
                    'name' => '视频审核通过',
                    'description' => '视频审核通过视频审核通过视频审核通过视频审核通过',
                    'status' => 0
                ],
                [
                    'id' => 3,
                    'time' => '2024-01-04 20:39',
                    'name' => '视频审核通过',
                    'description' => '视频审核通过视频审核通过视频审核通过视频审核通过',
                    'status' => 0
                ],
                [
                    'id' => 4,
                    'time' => '2024-01-03 20:39',
                    'name' => '视频审核通过',
                    'description' => '视频审核通过视频审核通过视频审核通过视频审核通过',
                    'status' => 0
                ],
                [
                    'id' => 5,
                    'time' => '2024-01-02 20:39',
                    'name' => '视频审核通过',
                    'description' => '视频审核通过视频审核通过视频审核通过视频审核通过',
                    'status' => 1
                ],
            ],
            'total_count' => 5
        ]);
    }
}