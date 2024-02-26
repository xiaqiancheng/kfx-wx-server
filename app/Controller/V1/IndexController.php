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
use App\Repositories\ArticleRepository;
use App\Repositories\LevelCostTemplateRepository;
use App\Repositories\ShopRepository;
use App\Services\FileService;
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
     *                          @OA\Property(property="redirect_type", type="integer", description="跳转类型 0不跳转 1小程序 2外链 3视频"),
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
                // [
                //     'image_url' => 'http://kfccdn.xiuyan.info/uploads/1@2x.png',
                //     'redirect_type' => 0,
                //     'redirect_url' => '',
                //     'appid' => '',
                //     'name' => '',
                //     'desc' => ''
                // ],
                [
                    'image_url' => 'http://kids.kfc.com.cn/images/qqly.png',
                    'redirect_type' => 3,
                    'redirect_url' => 'https://kfc.xiuyan.info/video/1.mp4',
                    'appid' => '',
                    'name' => '儿童乐园',
                    'desc' => '快乐肯德基欢乐庆生！'
                ],
                [
                    'image_url' => 'http://www.kfc.com.cn/kfccda/ImgFile/201202/121727_919079.jpg',
                    'redirect_type' => 3,
                    'redirect_url' => 'https://kfc.xiuyan.info/video/2.mp4',
                    'appid' => '',
                    'name' => '天天运动',
                    'desc' => '快来肯德基欢乐庆生！'
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/wxapi/news/list",
     *     summary="文章列表",
     *     description="文章列表",
     *     operationId="IndexController_newsList",
     *     @OA\Parameter(name="page", in="query", description="页码 1开始",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Parameter(name="page_size", in="query", description="每页数量",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Parameter(name="type", in="query", description="文章类型 1新手教学 2常见问题",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Response(response="200", description="文章列表返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"total_count", "list"},
     *                 @OA\Property(property="list", type="array", description="文章数据",
     *                     @OA\Items(type="object", 
     *                          required={"time", "name", "description", "url"},
     *                          @OA\Property(property="time", type="string", description="时间"),
     *                          @OA\Property(property="name", type="string", description="名称"),
     *                          @OA\Property(property="description", type="string", description="描述"),
     *                          @OA\Property(property="url", type="string", description="外链")
     *                      )
     *                 ),
     *                 @OA\Property(property="total_count", type="integer", description="总数量")
     *             )
     *         )
     *     )
     * )
     */
    public function newsList()
    {
        $page = $this->request->input('page', 1);
        $pageSize = $this->request->input('page_size', 20);

        $type = $this->request->input('type', 1);

        $list = ArticleRepository::instance()->getList(['type' => $type], ['id', 'name', 'description', 'created_at as time'], $page, $pageSize, ['id' => 'desc']);
        foreach ($list['list'] as &$value) {
            $value['url'] = env('HOST') . '/index/index/article?id=' . $value['id'];
        }

        return $this->response->success($list);
    }

    /**
     * @OA\Get(
     *     path="/wxapi/shop/list",
     *     tags={"店铺"},
     *     summary="店铺列表",
     *     description="店铺列表",
     *     operationId="IndexController_shopList",
     *     @OA\Parameter(name="page", in="query", description="页码 1开始",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Parameter(name="page_size", in="query", description="每页数量",
     *         @OA\Schema(type="interger")
     *     ),
     *     @OA\Response(response="200", description="店铺列表返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"total_count", "list"},
     *                 @OA\Property(property="list", type="array", description="店铺数据",
     *                     @OA\Items(type="object", 
     *                          required={"id", "name"},
     *                          @OA\Property(property="id", type="integer", description="唯一ID"),
     *                          @OA\Property(property="name", type="string", description="店铺名称")
     *                      )
     *                 ),
     *                 @OA\Property(property="total_count", type="integer", description="总数量")
     *             )
     *         )
     *     )
     * )
     */
    public function shopList()
    {
        $page = $this->request->input('page', 1);
        $pageSize = $this->request->input('page_size', 20);

        $filter['status'] = 1;

        $list = ShopRepository::instance()->getList($filter, ['id', 'name'], $page, $pageSize, ['id' => 'desc']);

        return $this->response->success($list);
    }

    /**
     * @OA\Post(
     *     path="/wxapi/upload",
     *     summary="文件上传",
     *     description="文件上传",
     *     operationId="IndexController_upload",
     *     @OA\RequestBody(description="请求body",
     *         @OA\JsonContent(type="object",
     *             required={"file"},
     *             @OA\Property(property="file", type="file", description="文件"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="信息返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={"path", "fullurl"},
     *                 @OA\Property(property="path", type="string", description="文件路径"),
     *                 @OA\Property(property="fullurl", type="string", description="文件url")
     *             )
     *         )
     *     )
     * )
     */
    public function upload()
    {
        $file = $this->request->file('file');
        
        if (empty($file)) {
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, '请选择文件');
        }

        $fileService = new FileService();
        $result = $fileService->upload($file);

        return $this->response->success($result);
    }


public function webhooks()
    {
        $param = $this->request->input('content');
         
return $this->response->success($param);
        if (empty($file)) {
            throw new BusinessException(ErrorCode::PARAMETER_ERROR, '请选择文件');
       }

        $fileService = new FileService();
        $result = $fileService->upload($file);

        return $this->response->success($result);
    }




    /**
     * @OA\Get(
     *     path="/wxapi/cost-template/list",
     *     summary="费用模板列表",
     *     description="费用模板列表",
     *     operationId="IndexController_getCostTemplateList",
     *     @OA\Response(response="200", description="信息返回",
     *         @OA\JsonContent(type="object",
     *             required={"errcode", "errmsg", "data"},
     *             @OA\Property(property="errcode", type="integer", description="错误码"),
     *             @OA\Property(property="errmsg", type="string", description="接口信息"),
     *             @OA\Property(property="data", type="object", description="信息返回",
     *                 required={""},
     *                 @OA\Property(property="", type="array", description="key为模板ID",
     *                     @OA\Items(type="object",
     *                         required={"level_id", "cost"},
     *                         @OA\Property(property="level_id", type="integer", description="级别ID"),
     *                         @OA\Property(property="cost", type="integer", description="费用（分）")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getCostTemplateList()
    {
        $list = LevelCostTemplateRepository::instance()->getList([], ['*'], 0, 0, [], [], false);

        $data = [];
        foreach ($list['list'] as $value) {
            $data[$value['template_id']][] = $value;
        }

        return $this->response->success($data);
    }
}
