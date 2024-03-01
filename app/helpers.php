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