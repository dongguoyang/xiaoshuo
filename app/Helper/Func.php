<?php

if (! function_exists('IsUrl')) {
    function IsUrl($v)
    {
        $pattern = "#(http|https)://(.*\.)?.*\..*#i";
        if (preg_match($pattern, $v)) {
            return true;
        } else {
            return false;
        }
    }
}

if(!function_exists('CheckUrl')) {
    // 检测域名格式
    function CheckUrl($url){
        $str="/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/";
        if (!preg_match($str, $url)){
            return false;
        }else{
            return true;
        }
    }
}

if(!function_exists('get_file_type')) {
    /**
     * 通过文件头字节，获取文件类型
     * @param string $binary 二进制字符串
     * @return bool|string
     */
    function get_file_type(string $binary) {
        if(empty($binary)) {
            return false;
        }
        $strInfo = @unpack("C2chars", $binary);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
        switch ($typeCode) {
            case 7790:
                $fileType = 'exe';
                break;
            case 7784:
                $fileType = 'midi';
                break;
            case 8297:
                $fileType = 'rar';
                break;
            case 255216:
                $fileType = 'jpg';
                break;
            case 7173:
                $fileType = 'gif';
                break;
            case 6677:
                $fileType = 'bmp';
                break;
            case 13780:
                $fileType = 'png';
                break;
            default:
                $fileType = 'unknown';
        }
        return $fileType;
    }
}

if (! function_exists('IsJson')) {
    /**
     * 判断指定字符串是否为json
     * @param string $string 要判定的字符串
     * @return bool
     */
    function IsJson($string = '') {
        try{
            $rel = json_decode($string, 1);
            if ($rel && is_array($rel)) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (! function_exists('readZippedXML')) {
    /**
     * extract contents from some ZipArchive file
     * @param string $archiveFile
     * @param string $dataFile
     * @return string
     */
    function readZippedXML($archiveFile, $dataFile)
    {
        // Create new ZIP archive
        $zip = new ZipArchive;

        // Open received archive file
        if (true === $zip->open($archiveFile)) {
            // If done, search for the data file in the archive
            if (($index = $zip->locateName($dataFile)) !== false) {
                // If found, read it to the string
                $data = $zip->getFromIndex($index);
                // Close archive file
                $zip->close();
                // Load XML from a string
                // Skip errors and warnings
                $xml = new DOMDocument();
                $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                // Return data without XML formatting tags
                return strip_tags($xml->saveXML());
            }
            $zip->close();
        }

        // In case of failure return empty string
        return "";
    }
}

if (! function_exists('detect_encoding')) {
    /**
     * 检测文件编码
     * @param string $file 文件路径
     * @return string|null 返回 编码名 或 null
     */
    function detect_encoding($file)
    {
        $list = ['GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1'];
        $str = file_get_contents($file);
        foreach ($list as $item) {
            $tmp = mb_convert_encoding($str, $item, $item);
            if (md5($tmp) == md5($str)) {
                return $item;
            }
        }
        return null;
    }
}

if (! function_exists('get_string_encoding')) {
    /**
     * 检测字符串编码
     * @param string $string 字符串
     * @return string|null 返回 编码名 或 null
     */
    function get_string_encoding($string)
    {
        $list = ['GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1'];
        foreach ($list as $item) {
            $tmp = mb_convert_encoding($string, $item, $item);
            if (md5($tmp) == md5($string)) {
                return $item;
            }
        }
        return null;
    }
}

if (! function_exists('auto_read')) {
    /**
     * 自动解析编码读入文件
     * @param string $file 文件路径
     * @param string $charset 读取编码
     * @return string 返回读取内容
     */
    function auto_read($file, $charset = 'UTF-8')
    {
        $list = ['GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1'];
        $str = file_get_contents($file);
        foreach ($list as $item) {
            $tmp = mb_convert_encoding($str, $item, $item);
            if (md5($tmp) == md5($str)) {
                return mb_convert_encoding($str, $charset, $item);
            }
        }
        return "";
    }
}

if (! function_exists('convert_string_encoding')) {
    /**
     * 转换字符串为目标编码
     * @param string $string 字符串
     * @param string $charset 目标编码
     * @return string 返回内容
     */
    function convert_string_encoding($string, $charset = 'UTF-8')
    {
        $list = ['GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1'];
        foreach ($list as $item) {
            $tmp = mb_convert_encoding($string, $item, $item);
            if (md5($tmp) == md5($string)) {
                return mb_convert_encoding($string, $charset, $item);
            }
        }
        return "";
    }
}

if (! function_exists('getNumber')) {
    /**
     * 从字符串提取数字
     * @param string $str 字符串
     * @return string|string[]|null
     */
    function getNumber($str) {
        return preg_replace('/\D/s', '', $str);
    }
}

if(! function_exists('is_number')) {
    /**
     * 判断字符串是否为数字
     * @param string $str 字符串
     * @return false|int
     */
    function is_number($str) {
        return preg_match('/^\d{1,}$/', $str);
    }
}

if(!function_exists('getPageList')) {
    /**
     * 获取分页页码配置，页码列表过长则缩略部分页码
     * @param int $current_page 当前页码
     * @param int $total_count 总记录数
     * @param string $query 查询（不包含页码），形如：a=1&b=2
     * @param int $page_size  分页大小
     * @return array 结果
     */
    function getPageList(int $current_page, int $total_count, string $query = '', int $page_size = 10) {
        $max_page = max(1, ceil($total_count / $page_size));
        $current_page = min($current_page, $max_page);
        $page_arr = [];
        // 上一页
        if(($current_page - 1) >= 1) {
            $page_arr[] = ['class' => '', 'href' => '?p='.($current_page - 1).($query ? '&'.$query : ''), 'text' => '上一页'];
        } else {
            $page_arr[] = ['class' => 'disabled', 'href' => '', 'text' => '上一页'];
        }
        // 中间页码
        $endpoint_length = 2;
        $left_omit_set = false;
        $right_omit_set = false;
        $omit = ['class' => 'disabled', 'href' => '', 'text' => '...'];
        for($i = 1; $i <= $max_page; $i++) {
            // 自身页码处左右预留各3页码，若超过，则于左右端距离为2处缩略部分页码
            if($i < ($current_page - 3) && $i > $endpoint_length) {
                if(!$left_omit_set) {
                    // 左缩略
                    $left_omit_set = true;
                    $page_arr[] = $omit;
                }
                continue;
            } elseif($i > ($current_page + 3) && $i < ($max_page - $endpoint_length + 1)) {
                if(!$right_omit_set) {
                    // 右缩略
                    $right_omit_set = true;
                    $page_arr[] = $omit;
                }
                continue;
            } else {
                // 正常页码
                if($i != $current_page) {
                    $page_arr[] = ['class' => '', 'href' => '?p='.$i.($query ? '&'.$query : ''), 'text' => $i];
                } else {
                    $page_arr[] = ['class' => 'active', 'href' => '', 'text' => $i];
                }
            }
        }
        // 下一页
        if(($current_page + 1) <= $max_page) {
            $page_arr[] = ['class' => '', 'href' => '?p='.($current_page + 1).($query ? '&'.$query : ''), 'text' => '下一页'];
        } else {
            $page_arr[] = ['class' => 'disabled', 'href' => '', 'text' => '下一页'];
        }
        return ['page_list' => $page_arr, 'current_page' => $current_page];
    }
}

if (! function_exists('Ctr2Act')) {
    /**
     * 获取Controller 和 Action
     * @param string $type  c 获取控制器； a 获取action； default c&a
     */
    function Ctr2Act($type = '')
    {
        $ca = \Route::current()->getActionName();
        list($class, $rel['a']) = explode('@', $ca);
        $temp = explode('\\', $class);
        $rel['c'] = strtolower(substr(array_pop($temp), 0, -10));

        if ($type) {
            return $rel[$type];
        }
        return $rel;
    }
}

if (!function_exists('FileExists')) {
    /**
     * 判断文件是否存在 不管远图片还是本地
     * @param string $file 文件地址
     */
    function FileExists($file) {
        // if (preg_match('/^http:\/\//', $file)) {
        if (strpos($file, 'http://') !== false || strpos($file, 'https://') !== false) {
            //远程文件
            if (ini_get('allow_url_fopen')) {
                if (@fopen($file, 'r')) {
                    return true;
                }

            } else {
                $parseurl = parse_url($file);
                $host = $parseurl['host'];
                $path = $parseurl['path'];
                $fp = fsockopen($host, 80, $errno, $errstr, 10);
                if (!$fp) {
                    return false;
                }

                fputs($fp, "GET {$path} HTTP/1.1 \r\nhost:{$host}\r\n\r\n");
                if (preg_match('/HTTP\/1.1 200/', fgets($fp, 1024))) {
                    return true;
                }

            }
            return false;
        }
        return file_exists($file);
    }
}

if (!function_exists('CloudPreDomain')) {
    /**
     * 云文件存储前缀
     * @param string $disk 存储盘类型 oss qiniu local 等等类型
     */
    function CloudPreDomain($disk = '') {
        if (!$disk) {
            $disk = config('admin.upload.disk');
        }

        $domain = '/';
        switch ($disk) {
            case 'oss':
                $domain = OssPreDomain();
                break;
            case 'qiniu':
                $domain = QiniuPreDomain();
                break;
            default:
                break;
        }
        return $domain;
    }
}

if (!function_exists('OssPreDomain')) {
    /**
     * 阿里云文件存储前缀
     */
    function OssPreDomain() {
        return (config('filesystems.disks.oss.ssl') ? 'https://' : 'http://') .
            config('filesystems.disks.oss.bucket') . '.' .
            config('filesystems.disks.oss.endpoint') . '/';
    }
}

if (!function_exists('QiniuPreDomain')) {
    /**
     * 七牛云文件存储前缀
     */
    function QiniuPreDomain() {
        return config('filesystems.disks.qiniu.url');
    }
}

if (!function_exists('Md5Passwd')) {
    /**
     * 获取md5后的用户密码
     */
    function Md5Passwd($str, $passwd = '', $pre = '') {
        if (!$pre) {
            $pre = config('app.key');
        }

        if (!$passwd) {
            return md5($pre . $str);
        }

        if ($passwd && strlen($passwd) == 32) {
            return $passwd == md5($pre . $str);
        }

        return null;
    }
}

if (!function_exists('XmlToArray')) {
    /**
     * xml 转 数组
     * @param $xml
     * @return array
     */
    function XmlToArray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
}

if (!function_exists('ArrayToXml')) {
    /**
     * 数组转XML
     * @param $arr 需要转xml的数组
     * @return xml
     */
    function ArrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

}


if (! function_exists('Optional')) {
    /**
     * Provide access to Optional objects.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function Optional($value = null)
    {
        return new \Illuminate\Support\Optional($value);
    }
}

if (!function_exists('IsWechatBrowser')) {
    // 判断是否微信浏览器
    function IsWechatBrowser()
    {
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        //     不是微信浏览器
        if (strpos($ua, 'MicroMessenger'))
        {
            return true;
        }
        return false;
    }
}
if (!function_exists('IsAndroid')) {
    // 判断是否Android系统
    function IsAndroid()
    {
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        //     不是微信浏览器
        if (strpos($ua, 'Android')!==false || strpos($ua, 'Linux')!==false)
        {
            return true;
        }
        return false;
    }
}
if (!function_exists('IsIOS')) {
    // 判断是否IOS系统
    function IsIOS()
    {
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        //     不是微信浏览器
        if (strpos($ua, 'iPhone')!==false || strpos($ua, 'iPad')!==false)
        {
            return true;
        }
        return false;
        /*if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            echo 'systerm is IOS';
        }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
            echo 'systerm is Android';
        }else{
            echo 'systerm is other';
        }*/
    }
}

if (!function_exists('RandCode')) {
    /**
     * 生成随机字符串
     * @param int       $length  要生成的随机字符串长度
     * @param string    $type    随机码类型：0，数字+大小写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
     * @return string
     */
    function RandCode($length = 10, $type = 0) {
        $arr = [1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|"];
        switch ($type) {
            case 0:
                array_pop($arr);
                $string = implode("", $arr);
                break;
            case -1:
                $string = implode("", $arr);
                break;
            case 5:
                $string = '0123456789abcdef';
                break;
            default:
                $string = '';
                $types = str_split($type);
                foreach ($types as $v) {
                    $string .= $arr[$v];
                }
                break;
        }

        $count = strlen($string) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $string[rand(0, $count)];
        }
        return $code;
    }
}

if (!function_exists('CallCurl')) {
    /**
     * @param $url 请求网址
     * @param bool $params 请求参数
     * @param int $ispost 请求方式
     * @param int $https https协议
     * @param string $type 参数格式
     * @param string $json_option 参数格式 JSON_UNESCAPED_UNICODE
     * @return bool|mixed
     */
    function CallCurl($url, $params = false, $ispost = 0, $type = "url", $json_option = '') {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, UserAgent('magic2Wechat'));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (strpos($url, 'https:') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if (isset($header) && $header) {
            //              ajax 模拟                             设置 refer                                            设置语言
            //$header = ['X-Requested-With: XMLHttpRequest', 'Referer: http://d301967.jxtgkn.cn/chapter/bid/1994', 'Accept-Language: zh-CN,en-US;q=0.9'];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            if (is_array($params)) {
                if ($type == 'url') {
                    $params = http_build_query($params);
                } else {
                    if ($json_option) {
                        $params = json_encode($params, $json_option);
                    } else {
                        $params = json_encode($params);
                    }
                }

            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                if (is_array($params)) {
                    if ($type == 'url') {
                        $params = http_build_query($params);
                    } else {
                        $params = json_encode($params);
                    }

                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);

        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }
}

if (!function_exists('ExplodeStr')) {
    /**
     * 分割字符串
     * @param $str
     * @param bool $char
     * @return array
     */
    function ExplodeStr($str, $char = false) {
        $str = trim($str);
        $arr = [];
        if ($char === false) {
            if (strpos($str, ',')) {
                $arr = explode(',', $str);
            } else if (strpos($str, ';')) {
                $arr = explode(';', $str);
            } else if (strpos($str, ';')) {
                $arr = explode("\n", $str);
            } else {
                $arr = explode('|', $str);
            }
        } else {
            $arr = explode($char, $str);
        }
        foreach ($arr as $k => $v) {
            $arr[$k] = trim($v);
        }
        return $arr;
    }
}
if (!function_exists('TrimAll')) {
    /**
     * 去除所有空格字符
     * @param $str
     * @param bool $char
     * @return array
     */


    /**
     * 过滤字符串的所有空格
     * @param string $str 要过滤的字符串
     * @param array $chars 要替换的字符数组
     * @return string
     */
    function TrimAll($str, $chars = []) {
        if ($chars) {
            if (!in_array($chars)) {
                $search = [$chars];
            } else {
                $search = $chars;
            }
        } else {
            $search = [" ", "　", "\t", "\n", "\r", " "];
        }
        $replace = '';

        return str_replace($search, $replace, $str);
    }
}

if (!function_exists('SockPush')) {
    /**
     * 非阻塞推送模版消息
     * 正常调用成功的
     */
    function SockPush($url, $data) {
        $query = http_build_query($data);

        $params = json_encode($data, JSON_UNESCAPED_UNICODE);

        $host   = parse_url($url,PHP_URL_HOST);
        $scheme = parse_url($url,PHP_URL_SCHEME);
        $port   = parse_url($url,PHP_URL_PORT);
        $port   = $port ? $port : ($scheme=='https' ? 443 : 80);
        $path   = parse_url($url,PHP_URL_PATH);
        // $query = parse_url($url,PHP_URL_QUERY);
        if($query) $path .= '?'.$query;
        if($scheme == 'https') {
            $host = 'ssl://' . $host;
        }

        $fp = fsockopen($host, $port, $error_code, $error_msg, 1);

        if(!$fp) {
            return array('error_code' => $error_code,'error_msg' => $error_msg);
        } else {
            stream_set_blocking($fp,true);//开启了手册上说的非阻塞模式
            stream_set_timeout($fp,1);//设置超时
            $header = "POST $path HTTP/1.1\r\n";
            $header.="Host: $host\r\n";
            $header.="Content-type: application/json\r\n";
            // $header.="Content-type: application/x-www-form-urlencoded\r\n";
            $header.="Content-length: ".strlen($params)."\r\n";
            $header.="Connection: close\r\n\r\n";//长连接关闭
            $header.=$params."\r\n\r\n";//长连接关闭
            fwrite($fp, $header);
            usleep(10000); // 这一句也是关键，如果没有这延时，可能在nginx服务器上就无法执行成功；添加usleep执行会稍微慢一点
            fclose($fp);
            if (ob_get_length() > 0) {
                ob_clean();
            }
            return array('error_code' => 0);
        }
    }
}

if (!function_exists('SockPushTemplate')) {
    /**
     * 推送公众号模板消息
     * @param string $url
     * @param array $content 模板消息内容
     */
    function SockPushTemplate($url, $params) {
        if (is_array($params)) {
            $params = json_encode($params,JSON_UNESCAPED_UNICODE);
        }

        $arr = explode('qq.com', $url);
        $path = $arr[1];
        $arr = explode('://', ($arr[0] . 'qq.com'));
        $host = $arr[1];
        $fp     = fsockopen('ssl://api.weixin.qq.com', 443, $error, $errstr, 1);
        $http   = "POST $path HTTP/1.1\r\nHost: $host\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($params) . "\r\nConnection:close\r\n\r\n$params\r\n\r\n";
        //$http   = "POST HTTP/1.1\r\nHost: $host/$path\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($params) . "\r\nConnection:close\r\n\r\n$params\r\n\r\n";
        fwrite($fp, $http);
        fclose($fp);
    }
}

if (!function_exists('GetBaseDomain')) {
    /**
     * 取得根域名
     * @param type $domain 域名
     * @return string 返回根域名
     */
    function GetBaseDomain($domain) {
        $re_domain = '';
        $domain_postfix_cn_array = array("com", "net", "org", "gov", "edu", "com.cn", "cn");
        $array_domain = explode(".", $domain);
        $array_num = count($array_domain) - 1;
        if ($array_domain[$array_num] == 'cn') {
            if (in_array($array_domain[$array_num - 1], $domain_postfix_cn_array)) {
                $re_domain = $array_domain[$array_num - 2] . "." . $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
            } else {
                $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
            }
        } else {
            $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
        }
        return $re_domain;
    }
}


if (!function_exists('UserAgent')) {
    /**
     * 获取返回已知浏览器 UA
     * @param string $key 浏览器类型
     * @return string ua
     */
    function UserAgent($key = 'iphoneWechat') {
        $ua['mate10p'] = 'Mozilla/5.0 (Linux; U; Android 8.1.0; zh-cn; BLA-AL00 Build/HUAWEIBLA-AL00) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/8.9 Mobile Safari/537.36';
        $ua['nova3'] = 'Mozilla/5.0 (Linux; Android 8.1; PAR-AL00 Build/HUAWEIPAR-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/6.2 TBS/044304 Mobile Safari/537.36 MicroMessenger/6.7.3.1360(0x26070333) NetType/WIFI Language/zh_CN Process/tools';
        $ua['OPPOA57'] = 'Mozilla/5.0 (Linux; Android 6.0.1; OPPO A57 Build/MMB29M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/63.0.3239.83 Mobile Safari/537.36 T7/10.13 baiduboxapp/10.13.0.10 (Baidu; P1 6.0.1)';
        $ua['huaweiP20'] = 'Mozilla/5.0 (Linux; Android 8.1; EML-AL00 Build/HUAWEIEML-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/53.0.2785.143 Crosswalk/24.53.595.0 XWEB/358 MMWEBSDK/23 Mobile Safari/537.36 MicroMessenger/6.7.2.1340(0x2607023A) NetType/4G Language/zh_CN';
        $ua['honorV9'] = 'Mozilla/5.0 (Linux; Android 8.0; DUK-AL20 Build/HUAWEIDUK-AL20; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/6.2 TBS/044353 Mobile Safari/537.36 MicroMessenger/6.7.3.1360(0x26070333) NetType/WIFI Language/zh_CN Process/tools';
        $ua['vivoX6SA'] = 'Mozilla/5.0 (Linux; Android 5.1.1; vivo X6S A Build/LMY47V; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/6.2 TBS/044207 Mobile Safari/537.36 MicroMessenger/6.7.3.1340(0x26070332) NetType/4G Language/zh_CN Process/tools';

        $ua['windows'] = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36';

        $ua['iphoneWechat'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/16A366 MicroMessenger/6.7.3(0x16070321) NetType/WIFI Language/zh_CN';
        $ua['iphone6sQQ'] = 'Mozilla/5.0 (iPhone 6s; CPU iPhone OS 11_4_1 like Mac OS X) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0 MQQBrowser/8.3.0 Mobile/15B87 Safari/604.1 MttCustomUA/2 QBWebViewType/1 WKType/1';

        $ua['galaxyS9'] = 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G9650 Build/R16NW; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/63.0.3239.83 Mobile Safari/537.36 T7/10.13 baiduboxapp/10.13.0.11 (Baidu; P1 8.0.0)';
        $ua['galaxyNote8'] = 'Mozilla/5.0 (Linux; Android 8.0.0; SM-N9500 Build/R16NW; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/63.0.3239.83 Mobile Safari/537.36 T7/10.13 baiduboxapp/10.13.0.11 (Baidu; P1 8.0.0)';
        $ua['magic2Wechat'] = 'Mozilla/5.0 (Linux; Android 9; TNY-AL00 Build/HUAWEITNY-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/66.0.3359.126 MQQBrowser/6.2 TBS/45016 Mobile Safari/537.36 MMWEBID/6862 MicroMessenger/7.0.6.1500(0x2700063E) Process/tools NetType/4G Language/zh_CN
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,image/wxpic,image/sharpp,image/apng,image/tpg,*/*;q=0.8';
        return $ua[$key];
    }
}

if (!function_exists('Array2String')) {

    /**
     * 获取state信息
     * @param array $state
     * @param bool $urlencode 是否才有url编码
     */
    function Array2String($state, $urlencode = false) {
        if (is_array($state)) {
            // 转换state信息
            if (is_array($state)) {
                $str = '';
                foreach ($state as $k => $v) {
                    $str .= $k . '=' . $v . ',';
                }
                $state = substr($str, 0, -1);
            }
            if ($urlencode) {
                $state = urlencode($state);
            }
        } else {
            // 解码state信息
            if (stripos($state, '%3D')) {
                $state = urldecode($state);
            }
            $arr = explode(',', $state);
            $data = [];
            foreach ($arr as $va) {
                $tem = explode('=', $va);
                $data[$tem[0]] = isset($tem[1]) ? $tem[1] : '';
            }
            $state = $data;
        }

        return $state;
    }
}
if (!function_exists('GetUrlParams')) {

    /**
     * 添加url参数；主要区别已有参数
     * @param string $url
     * @param array $params // 新增的参数
     * @param string $type hash:表示获取hash参数；url:表示获取url参数
     */
    function GetUrlParams($url, $type = 'hash') {
        // 获取参数和 ？ 之前的部分
        if ($type == 'url') {
            $paramStr = parse_url($url, PHP_URL_QUERY);
        } else {
            $arr = explode('?', $url);
            $paramStr = isset($arr[1]) ? $arr[1] : null;
        }

        // 合并所有参数；$params 优先级高
        $paramStr = explode('&', $paramStr);
        foreach ($paramStr as $v) {
            $temp = explode('=', $v);
            if (!isset($params[$temp[0]])) {
                $params[$temp[0]] = isset($temp[1]) ? $temp[1] : '';
            }
        }

        return $params;
    }
}
if (!function_exists('AddUrlParams')) {

    /**
     * 添加url参数；主要区别已有参数
     * @param string $url
     * @param array $params // 新增的参数
     * @param string $type hash:表示获取hash参数；url:表示获取url参数
     */
    function AddUrlParams($url, $params = [], $type = 'hash') {
        if (!$params) return $url;

        /*if (strpos($url, '?')) {
            $url .= '&';
        } else {
            $url .= '?';
        }*/

        // 获取参数和 ？ 之前的部分
        if ($type == 'url') {
            $paramStr = parse_url($url, PHP_URL_QUERY);
            $arr = explode('?', $url);
            $url = $arr[0];
        } else {
            $arr = explode('?', $url);
            $url = $arr[0];
            $paramStr = isset($arr[1]) ? $arr[1] : null;
        }

        // 合并所有参数；$params 优先级高
        $paramStr = explode('&', $paramStr);
        foreach ($paramStr as $v) {
            $temp = explode('=', $v);
            if (!isset($params[$temp[0]])) {
                $params[$temp[0]] = isset($temp[1]) ? $temp[1] : '';
            }
        }

        // 将参数添加到 url 后
        $url .= '?' . http_build_query($params);

        return $url;
    }
}

if (!function_exists('WechatStateStr')) {

    /**
     * 获取state信息
     * @param string $str
     * @param string $type encode   decode
     */
    function WechatStateStr($str, $type = 'encode') {
        if (!$str) return $str;

        if ($type == 'encode') {
            $codes = [
                '=' => '0v11v0',
                '#' => '0v12v0',
                '/' => '0v13v0',
                '?' => '0v14v0',
                '-' => '0v15v0',
                '_' => '0v16v0',
                '%' => '0v17v0',
                '.' => '0v18v0',
                ':' => '0v19v0',
                '&' => '0v20v0',
            ];
            if (stripos($str, '%2F') !== false ||
                stripos($str, '%3F') !== false ||
                stripos($str, '%3D') !== false
            ) { // url 解码
                $str = urldecode($str);
            }
            foreach ($codes as $k=>$v) {
                $str = str_replace($k, $v, $str);
            }
        } else {
            $codes = [
                '0v11v0' => '=',
                '0v12v0' => '#',
                '0v13v0' => '/',
                '0v14v0' => '?',
                '0v15v0' => '-',
                '0v16v0' => '_',
                '0v17v0' => '%',
                '0v18v0' => '.',
                '0v19v0' => ':',
                '0v20v0' => '&',
            ];
            foreach ($codes as $k=>$v) {
                $str = str_replace($k, $v, $str);
            }
        }

        return $str;
    }
}

if (!function_exists('getOS')) {

    /**
     * 获取user_agent信息
     * @param string $user_agent
     */
    function getOS($user_agent){
        if (strpos($user_agent, 'Android') !== false) {  //strpos()定位出第一次出现字符串的位置，这里定位为0
            preg_match("/(?<=Android )[\d\.]{1,}/", $user_agent, $version);
            $platform['os_platform'] = 'Android';
            $platform['os_version'] = $version[0];
        } elseif (strpos($user_agent, 'iPhone') !== false) {
            preg_match("/(?<=CPU iPhone OS )[\d\_]{1,}/", $user_agent, $version);
            $platform['os_platform'] = 'iPhone';
            $platform['os_version'] = str_replace('_', '.', $version[0]);
        } elseif (strpos($user_agent, 'iPad') !== false) {
            preg_match("/(?<=CPU OS )[\d\_]{1,}/", $user_agent, $version);
            $platform['os_platform'] = 'iPad';
            $platform['os_version'] = str_replace('_', '.', $version[0]);
        } else{
            $platform['os_platform'] = 'unknown';
            $platform['os_version'] = 'unknown';
        }
        return $platform;
    }
}
