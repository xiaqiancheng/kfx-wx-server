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

use App\Controller\AbstractController;
use App\Repositories\FileShareRepository;
use App\Repositories\MediumRepository;
use App\Repositories\VideoRepository;

class ShareController  extends AbstractController
{
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
            $data['extracted_code'] = 'wx';
        }

        if ($data['valid_time'] == 1) {
            // 7天
            $data['expiration_time'] = date('Y-m-d H:i:s', strtotime("+7"));
        }

        FileShareRepository::instance()->saveData($data);

        return $this->response->success([
            'link' => 'http://www.baidu.com',
            'extracted_code' => $data['extracted_code'],
            'valid_time' => $data['valid_time']
        ]);
    }
}