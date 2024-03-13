<?php

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use HyperfExt\Auth\Contracts\AuthManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/*
 * 获取Container
 */
if (! function_exists('di')) {
    /**
     * Finds an entry of the container by its identifier and returns it.
     * @param null|mixed $id
     * @return mixed|\Psr\Container\ContainerInterface
     */
    function di($id = null)
    {
        $container = ApplicationContext::getContainer();
        if ($id) {
            return $container->get($id);
        }
        return $container;
    }
}

if (! function_exists('request')) {
    /**
     * request请求
     */
    function request() 
    {
        return di(RequestInterface::class);
    }
}

if (!function_exists('auth')) {
    /**
     * Auth认证辅助方法
     * @param string|null $guard
     * @return \HyperfExt\Auth\Contracts\GuardInterface|\HyperfExt\Auth\Contracts\StatefulGuardInterface|\HyperfExt\Auth\Contracts\StatelessGuardInterface
     */
    function auth(string $guard = null)
    {
        if (is_null($guard)) $guard = config('auth.default.guard');
        return make(AuthManagerInterface::class)->guard($guard);
    }
}

/*
 * 文件日志
 */
if (! function_exists('logger')) {
    function logger($name = 'hyperf', $group = 'default')
    {
        return di()->get(LoggerFactory::class)->get($name, $group);
    }
}

if (! function_exists('format_throwable')) {
    /**
     * Format a throwable to string.
     */
    function format_throwable(Throwable $throwable): string
    {
        return di()->get(FormatterInterface::class)->format($throwable);
    }
}

if (!function_exists('get_client_ip')) {
    function get_client_ip()
    {
        return \Hyperf\Utils\Network::ip();
    }
}

if (!function_exists('get_real_ip')) {
    function get_real_ip(RequestInterface $request): string
    {
        $headers = $request->getHeaders();

        if(isset($headers['x-forwarded-for'][0]) && !empty($headers['x-forwarded-for'][0])) {
            return $headers['x-forwarded-for'][0];
        } elseif (isset($headers['x-real-ip'][0]) && !empty($headers['x-real-ip'][0])) {
            return $headers['x-real-ip'][0];
        }

        $serverParams = $request->getServerParams();
        return $serverParams['remote_addr'] ?? '';
    }
}

if (! function_exists('setCache')) {
    /**
     * setCache
     * 设置缓存
     * @param $key
     * @param $value
     * @param null $ttl
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    function setCache($key, $value, $ttl = null)
    {
        return di()->get(\Psr\SimpleCache\CacheInterface::class)->set($key, $value, $ttl);
    }
}

if (! function_exists('getCache')) {
    /**
     * getCache
     * 获取缓存
     * @param $key
     * @param null $default
     * @return iterable
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    function getCache($key, $default = null)
    {
        return di()->get(\Psr\SimpleCache\CacheInterface::class)->get($key, $default);
    }
}

if (! function_exists('clearPrefixCache')) {
    /**
     * clearPrefixCache
     * 根据前缀清楚缓存
     * 函数的含义说明
     * @param string $prefix
     * @return bool
     */
    function clearPrefixCache($prefix = '')
    {
        di()->get(\Psr\SimpleCache\CacheInterface::class)->clearPrefix($prefix);
        return true;
    }
}

if (! function_exists('delCache')) {
    /**
     * delCache
     * 删除缓存，1条/多条
     * @param array $keys
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    function delCache($keys = [])
    {
        $cache = di()->get(\Psr\SimpleCache\CacheInterface::class);

        if (is_array($keys)) {
            $cache->deleteMultiple($keys);
        } else {
            $cache->delete($keys);
        }

        return true;
    }
}

if(!function_exists('handleTreeList')) {
    /**
     * handleTreeList
     * 建立数组树结构列表
     * @access public
     * @param $arr
     * @param int $pid 父级id
     * @param int $depth 增加深度标识
     * @param string $p_sub 父级别名
     * @param string $d_sub 深度别名
     * @param string $c_sub 子集别名
     * @return array
     */
    function handleTreeList($arr, $pid=0, $depth=0, $p_sub='parent_id', $c_sub='children' ,$d_sub='depth')
    {
        $returnArray = [];
        if(is_array($arr) && $arr) {
            foreach($arr as $k => $v) {
                if($v[$p_sub] == $pid) {
                    $v[$d_sub] = $depth;
                    $tempInfo = $v;
                    unset($arr[$k]); // 减少数组长度，提高递归的效率，否则数组很大时肯定会变慢
                    $temp = handleTreeList($arr,$v['id'],$depth+1,$p_sub,$c_sub,$d_sub);
                    if ($temp) {
                        $tempInfo['component'] = $pid == 0 ? 'Layout': 'views/'. $v['view'];
                        $tempInfo['redirect'] = $temp[0]['path'];
                        $tempInfo[$c_sub] = $temp;
                    }else {
                        $tempInfo['component'] = 'views/'. $v['view'];
                        if (!is_url($v['path'])) {
                            $tempInfo['path'] = '/'. $v['view'];
                        }
                        $tempInfo[$c_sub] = $temp;
                    }
                    $returnArray[] = $tempInfo;
                }
            }
        }
        return $returnArray;
    }
}

if(!function_exists('handleTreeList2')) {
    /**
     * handleTreeList
     * 建立数组树结构列表
     * @access public
     * @param $arr
     * @param int $pid 父级id
     * @param int $depth 增加深度标识
     * @param string $p_sub 父级别名
     * @param string $d_sub 深度别名
     * @param string $c_sub 子集别名
     * @return array
     */
    function handleTreeList2($arr, $pid=0, $depth=0, $p_sub='parent_id', $c_sub='children', $d_sub='depth')
    {
        $returnArray = [];
        if(is_array($arr) && $arr) {
            foreach($arr as $k => $v) {
                if($v[$p_sub] == $pid) {
                    $v[$d_sub] = $depth;
                    $tempInfo = $v;
                    unset($arr[$k]); // 减少数组长度，提高递归的效率，否则数组很大时肯定会变慢
                    $temp = handleTreeList2($arr,$v['id'],$depth+1,$p_sub,$c_sub,$d_sub);
                    if ($pid == 0) {
                        $tempInfo['component'] = 'Layout';
                        $tempInfo['redirect'] = !empty($temp[0]['path'])?$temp[0]['path']:'';
                        $tempInfo[$c_sub] = $temp;
                    }else {
                        $tempInfo['component'] = 'views/'. $v['view'];
                        $tempInfo['path'] = $v['path'];
                        $tempInfo[$c_sub] = $temp;
                    }
                    $returnArray[] = $tempInfo;
                }
            }
        }
        return $returnArray;
    }
}

if(!function_exists('handleTreeList3')) {
    /**
     * handleTreeList
     * 建立数组树结构列表
     * @access public
     * @param $arr
     * @param int $pid 父级id
     * @param int $depth 增加深度标识
     * @param string $p_sub 父级别名
     * @param string $d_sub 深度别名
     * @param string $c_sub 子集别名
     * @return array
     */
    function handleTreeList3($arr, $pid=0, $depth=0, $p_sub='parent_id', $c_sub='children', $d_sub='depth')
    {
        $returnArray = [];
        if(is_array($arr) && $arr) {
            foreach($arr as $k => $v) {
                if($v[$p_sub] == $pid) {
                    $v[$d_sub] = $depth;
                    $tempInfo = $v;
                    unset($arr[$k]); // 减少数组长度，提高递归的效率，否则数组很大时肯定会变慢
                    $temp = handleTreeList3($arr, $v['id'], $depth+1, $p_sub, $c_sub, $d_sub);
                    if ($temp) {
                        $tempInfo[$c_sub] = $temp;
                    }
                    $returnArray[] = $tempInfo;
                }
            }
        }
        return $returnArray;
    }
}


if (! function_exists('array_pluck')) {
    /**
     * Pluck an array of values from an array.
     * 从数组中提取值组成新数组
     *
     * @param  array   $array
     * @param  string|array  $value
     * @param  string|array|null  $key
     * @return array
     */
    function array_pluck($array, $value, $key = null)
    {
        return Arr::pluck($array, $value, $key);
    }
}

if (! function_exists('flushAnnotationCache')) {
    /**
     * flushAnnotationCache
     * 刷新注解缓存，清楚注解缓存
     * @param string $listener
     * @param array $keys
     * @return bool
     */
    function flushAnnotationCache($listener = '', $keys = [])
    {
        if (!$listener || !$keys) {
            throw new \RuntimeException('参数不正确！');
        }
        $keys = is_array($keys)?$keys:[$keys];
        $dispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
        foreach ($keys as $key) {
            $dispatcher->dispatch(new DeleteListenerEvent($listener, [$key]));
        }
        return true;
    }
}

if (!function_exists('is_url')) {
    /**
     * 检测是否为合法url
     */
    function is_url($url){
        if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
            return true;
        }else{
            return false;
        }
    }
}

/*
 * redis 客户端实例
 */
if (! function_exists('redis')) {
    function redis($db = 'default')
    {
        return di()->get(RedisFactory::class)->get($db);
    }
}

if (! function_exists('create_uniqid')) {
    function create_uniqid() {
        return substr(md5(uniqid(mt_rand(100000, 999999) . microtime())), 8, 16);
    }
}

if (!function_exists('create_zip_archive')) {
    function create_zip_archive($files, $outputPath, $baseOutputDir) {
        if (!is_dir($baseOutputDir)) {
            mkdir($baseOutputDir, 0777, true);
        }
        
        // 创建一个 ZipArchive 实例
        $zip = new ZipArchive();

        // 打开要输出的 ZIP 文件，如果文件不存在则创建它
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '无法创建ZIP文件');
        }
        
        // 将文件和子目录添加到 ZIP 文件中
        foreach ($files as $file) {
            $filePath = BASE_PATH . '/public/' . $file['path'];
            
            // 将文件添加到 ZIP 文件中，并使用相对路径作为文件名
            $zip->addFile($filePath, basename($filePath));
        }
        
        return $outputPath;
    }
}

if (!function_exists('generate_random_code')) {
    function generate_random_code() {
        // 生成随机字节序列
        $randomBytes = random_bytes(3); // 3 bytes = 6 characters in hex
        // 将字节序列转换为十六进制字符串
        $hexString = bin2hex($randomBytes);
        // 截取字符串为六位长度并返回
        return substr($hexString, 0, 6);
    }
}

if (!function_exists('getMilliseconds')) {
    function getMilliseconds() {
        $microseconds = microtime(true);
        $milliseconds = round(($microseconds - floor($microseconds)) * 1000);
        return (int)(microtime(true) * 1000);
    }
}

if (!function_exists('get_redirect_url')) {
    function get_redirect_url($url) {
        $header = get_headers($url, 1);
        if (strpos($header[0], '301') !== false || strpos($header[0], '302') !== false) {
            if (is_array($header['Location'])) {
                return $header['Location'][count($header['Location']) - 1];
            } else {
                return $header['Location'];
            }
        } else {
            return $url;
        }
    }
}

if (!function_exists('feiguaUrl')) {
    function feiguaUrl($url){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: zh-CN,zh;q=0.9',
            'Connection: keep-alive',
            'Referer: https://dy.feigua.cn/app/',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Site: same-origin',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'sec-ch-ua: "Chromium";v="122", "Not(A:Brand";v="24", "Google Chrome";v="122"',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "macOS"',
        ]);
        curl_setopt($ch, CURLOPT_COOKIE, 'SameSite=none; secure=; body_collapsed=0; ASP.NET_SessionId=dktol3zwmaoyd1irgl1ntow1; Hm_lvt_66d640e56887bf1b336aec47361698ca=1709737944,1710308113; Hm_lpvt_66d640e56887bf1b336aec47361698ca=1710308113; FEIGUADY=24F5F6914C51DA2BD70C8CB5BB83A150291BECEC18E849B19BC0D2045612CC882C06FF06EEEE4544F7686C5224B3A7E29113F6078A98A9C98485C12EFD2EBB7B977D91B2EE0998CC3171F4AC87FFBFA4554489DA7B7088DC7E27CA115CF2E73E04DD9F8CB4F7A65EBB3905B4B26BF4FEFC6E6F353B6AE5D6E3D440145583F9D1A7E921980E3EA57A6DDC8D875094ABE5B092C99DEA6CE71A25F08F6AE03B130744DE03447CFCDF694CCC5051E2638B78772D2410104067F5BD79003B70D594BBD4B4597E416A9335; 7b064e0a8e1e32c2302f440c3d87aa2c=11c014ebad67002f9ba5bc3a4abd90b69fedf8d3093b6882beb962e7e7e712df2e28737c7c57db3cfb51f922fce8e8f35c7229939276b1f8fbd5d74991ddb0b1f9934bf8067fefb22b443d0eb8955fffe630146fb24849fa133c3ee8228d823eeb744b6119251f3ab7eb7c9e173d18d4; SaveUserName=; tfstk=eN-9RgOmEve9V3FP-c3HuytCrBDhXhpaACJ7msfglBdpadQXoA8gMoOcthbcfVJXh61V1sbcjtEXGL_s3IjgMSdknN6m7Kcv9CAHmFqDDervUK-iQiDNbdSVcbcoq4vwQiyDfUpsqCjp03coZ0mhk0ZO-bYG-5GdxHFYBfM_mnxPC47cUO5zSFWOWsEc9oUZnOQONd1Kmmxd4NCWC6Ef4mxk21RaZ_B05vHLur71LgGF1XMkcj6lp_Dn-rzVJ9WdZvh7ur71C9CoLaU4uwNl.');
    
        $response = curl_exec($ch);
    
        curl_close($ch);
        return json_decode($response, true);
    }
}


