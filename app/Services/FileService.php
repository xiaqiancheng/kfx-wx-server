<?php

declare(strict_types=1);

namespace App\Services;

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

        $result = [];
        if ($driver == 'local') {
            //获取域名
            $result['path'] = '/upload/'.$name;
            $urlArr = explode('//', request()->url());
            $result['fullurl'] = $urlArr[0].'//'.explode('/', $urlArr[1])[0] . $result['path'];
        }
        
        return $result;
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