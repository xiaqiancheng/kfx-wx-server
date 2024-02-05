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
use OpenApi\Annotations as OA;

class IndexController extends AbstractController
{
    /**
     * @OA\Get(
     *     path="/wxapi/get-ads",
     *     summary="广告图片列表",
     *     description="广告图片列表",
     *     operationId="IndexController_images",
     *     @OA\Response(response="200", description="广告列表返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"top_banner", "featured_ad"},
     *                 @OA\Property(property="top_banner", type="array", description="首页顶部轮播",
     *                     @OA\Items(type="object", 
     *                          required={"image_url", "redirect_type", "redirect_url", "appid", "name", "desc"},
     *                          @OA\Property(property="image_url", type="string", description="图片url"),
     *                          @OA\Property(property="redirect_type", type="integer", description="跳转类型 0不跳转 1小程序 2外链"),
     *                          @OA\Property(property="redirect_url", type="string", description="小程序页面或链接"),
     *                          @OA\Property(property="appid", type="string", description="小程序APPID"),
     *                          @OA\Property(property="name", type="string", description="标题"),
     *                          @OA\Property(property="desc", type="string", description="描述")
     *                      )
     *                 ),
     *                 @OA\Property(property="featured_ad", type="array", description="首页特色广告列表",
     *                     @OA\Items(type="object", 
     *                          required={"image_url", "redirect_type", "redirect_url", "appid", "name", "desc"},
     *                          @OA\Property(property="image_url", type="string", description="图片url"),
     *                          @OA\Property(property="redirect_type", type="integer", description="跳转类型 0不跳转 1小程序 2外链"),
     *                          @OA\Property(property="redirect_url", type="string", description="小程序页面或链接"),
     *                          @OA\Property(property="appid", type="string", description="小程序APPID"),
     *                          @OA\Property(property="name", type="string", description="标题"),
     *                          @OA\Property(property="desc", type="string", description="描述")
     *                      )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function images()
    {
        return $this->response->success([
            'top_banner' => [
                [
                    'image_url' => 'https://kfc.xiuyan.info/images/11.jpeg',
                    'redirect_type' => 0,
                    'redirect_url' => '',
                    'appid' => '',
                    'name' => '',
                    'desc' => ''
                ],
                [
                    'image_url' => 'https://kfc.xiuyan.info/images/22.jpeg',
                    'redirect_type' => 0,
                    'redirect_url' => '',
                    'appid' => '',
                    'name' => '',
                    'desc' => ''
                ],
                [
                    'image_url' => 'https://kfc.xiuyan.info/images/33.jpeg',
                    'redirect_type' => 0,
                    'redirect_url' => '',
                    'appid' => '',
                    'name' => '',
                    'desc' => ''
                ],
            ],
            'featured_ad' => [
                [
                    'image_url' => 'http://kfccdn.xiuyan.info/uploads/1@2x.png',
                    'redirect_type' => 0,
                    'redirect_url' => '',
                    'appid' => '',
                    'name' => '',
                    'desc' => ''
                ],
                [
                    'image_url' => 'http://kids.kfc.com.cn/images/qqly.png',
                    'redirect_type' => 0,
                    'redirect_url' => '',
                    'appid' => '',
                    'name' => '儿童乐园',
                    'desc' => '快乐肯德基欢乐庆生！'
                ],
                [
                    'image_url' => 'http://www.kfc.com.cn/kfccda/ImgFile/201202/121727_919079.jpg',
                    'redirect_type' => 0,
                    'redirect_url' => '',
                    'appid' => '',
                    'name' => '天天运动',
                    'desc' => '快来肯德基欢乐庆生！'
                ]
            ]
        ]);
    }
}
