<?php
namespace App\Controller\V1;

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Exception\BusinessException;
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

    public function login() {
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
        return $this->response->success(['token' => $token]);
    }
}