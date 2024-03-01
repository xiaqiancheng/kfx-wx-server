<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Repositories\MediumRepository;
use Hyperf\Di\Annotation\Inject;
use League\Flysystem\Filesystem;

class FileService
{
    /**
     * @Inject
     * @var Filesystem
     */
    public $filesystem;

    public function upload($file)
    {
        //资源
        $stream = fopen($file->getRealPath(), 'r+');
        $extension = $file->getExtension();
        $fileName = $file->getClientFilename();

        $name = sprintf('%s/%s', date('Y-m-d'), $this->getFileName($fileName, $extension));

        $this->filesystem->writeStream($name, $stream);

        fclose($stream);

        $driver = config('file.default');

        $mediaId = '';
        if ($driver == 'local') {
            $mediaId = create_uniqid();
            MediumRepository::instance()->saveData([
                'media_id'=> $mediaId,
                'path' => 'public/upload/'.$name,
                'file_type' => $extension
            ]);
        }
        
        return $mediaId;
    }

    public function uploadVideo($file)
    {
        //资源
        $stream = fopen($file->getRealPath(), 'r+');
        $extension = $file->getExtension();
        
        if (!in_array($extension, ['mp4', 'webm'])) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '文件必须是mp4、webm视频格式');
        }

        $fileName = $file->getClientFilename();
        $name = sprintf('%s/%s', 'video', $this->getFileName($fileName, $extension));
        
        $this->filesystem->writeStream($name, $stream);

        fclose($stream);

        $driver = config('file.default');

        $mediaId = '';
        if ($driver == 'local') {
            $mediaId = create_uniqid();
            MediumRepository::instance()->saveData([
                'media_id'=> $mediaId,
                'path' => 'public/upload/'.$name,
                'file_type' => $extension
            ]);
        }
        
        return $mediaId;
    }

    /**
     * 生成文件名.
     *
     * @param string $fileName 文件名
     * @param string $extension 文件扩展名
     * 
     * @return string
     */
    protected function getFileName(string $fileName , string $extension)
    {
        return md5($fileName . time()). '.' .$extension;
    }
}