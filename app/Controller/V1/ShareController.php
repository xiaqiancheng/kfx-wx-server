<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller\V1;

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Exception\BusinessException;
use App\Repositories\FileShareRepository;
use App\Repositories\MediumRepository;
use App\Repositories\VideoRepository;
use HyperfExt\Auth\AuthManager;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class ShareController  extends AbstractController
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

    public function share()
    {
        $request = $this->request->inputs(['task_id', 'video_id', 'valid_time', 'gen_status', 'extracted_code']);

        if ($request['task_id'] ?? '' && $request['task_id']) {
            $filter['task_id'] = ['in', $request['task_id']];
            $column = ['video_link'];
        }
        if ($request['video_id'] ?? '' && $request['video_id']) {
            $filter['id'] = $request['video_id'];
            $column = ['cover', 'video_link'];
        }
        $list = VideoRepository::instance()->getList($filter, $column, 0, 0, [], [], false);

        $mediaId = array_unique(array_column($list['list'], 'video_link'));
        if ($request['video_id'] ?? '' && $request['video_id']) {
            $mediaId = array_merge($mediaId, array_unique(array_column($list['list'], 'cover')));
        }

        $path = MediumRepository::instance()->getList(['media_id' => ['in', $mediaId]], ['path'], 0, 0, [], [], false);

        $baseOutputDir = BASE_PATH . '/runtime/share';
        $outputPath = $baseOutputDir . '/' . md5('share' . time()) . '.zip';

        create_zip_archive($path['list'], $outputPath, $baseOutputDir, true);

        $data['files'] = $outputPath;
        $data['token'] = create_uniqid();
        $data['extracted_code'] = $request['extracted_code'];
        $data['valid_time'] = $request['valid_time'];
        if ($request['gen_status'] == 0) {
            // 自动生成
            $data['extracted_code'] = generate_random_code();
        }

        if ($data['valid_time'] == 1) {
            // 7天
            $data['expiration_time'] = date('Y-m-d H:i:s', strtotime("+7 day"));
        }

        FileShareRepository::instance()->saveData($data);

        return $this->response->success([
            'link' => env('HOST') . '/s/' . $data['token'],
            'extracted_code' => $data['extracted_code'],
            'valid_time' => $data['valid_time']
        ]);
    }

    public function shareVerify()
    {
        $request = $this->request->inputs(['code', 'token']);
        $validator = $this->validationFactory->make(
            $request,
            [
                'code' => 'required',
                'token' => 'required'
            ],
            [
                'code.required' => '提取码必须',
                'token.required' => '验证失败'
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, $errorMessage);
        }
        $token = $this->auth->guard('shareapi')->attempt($request);

        return $this->response->success(['token' => $token]);
    }

    public function getFile($token)
    {
        $id = auth('shareapi')->user()->id;
        $data = FileShareRepository::instance()->find($id, ['files', 'token']);
        if ($data['token'] !== $token) {
            auth('shareapi')->logout();
            throw new BusinessException(100031);
        }
        $file = basename($data['files']);

        return $this->response->success(['file' => $file]);
    }

    public function getdownloadCode()
    {
        $data['id'] = auth('shareapi')->user()->id;
        $data['download_code'] = create_uniqid();

        FileShareRepository::instance()->saveData($data);

        return $this->response->success(['downloadUrl' => env('HOST') . '/wxapi/download/' . $data['download_code']]);
    }
}