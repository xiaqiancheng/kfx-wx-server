<?php

declare(strict_types=1);

namespace App\Services;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\Writer\PngWriter;

class QRcodeService
{
    public function create($content)
    {
        $qrCode = EndroidQrCode::create($content)
        // 内容编码
        ->setEncoding(new Encoding('UTF-8'))
        // 内容区域大小
        ->setSize(200)
        // 内容区域外边距
        ->setMargin(10);
        // 生成二维码数据对象
        $result = (new PngWriter)->write($qrCode);

        $dataUri = $result->getDataUri();
        
        return $dataUri;
    }
}