<?php
/**
 * Created by LWL.
 * User: LUWENLONG
 * Date: 2019/3/17
 * Time: 16:03
 */

namespace App\Libraries\Curl;


use App\Libraries\Share\Client;

class Curl
{
     protected $ch;
    /**
     * @param        $url    请求网址
     * @param bool   $params 请求参数
     * @param int    $ispost 请求方式
     * @param int    $https  https协议
     * @param string $type   参数格式
     * @return bool|mixed
     */
     public function callCurl($url, $params = false, $ispost = 0, $type = "url")
    {
        $option = [
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_USERAGENT       => Client::userAgent('iphoneWechat'),
            CURLOPT_CONNECTTIMEOUT  => 30,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_RETURNTRANSFER  => true,
        ];
        if (strpos($url, 'https:') !== false)
        {
            $option[CURLOPT_SSL_VERIFYPEER] = false;
            $option[CURLOPT_SSL_VERIFYHOST] = false;
        }
        if ($ispost)
        {
            $option[CURLOPT_POST] = true;
            if (is_array($params))
            {
                $params = $type == 'url' ? http_build_query($params) : json_encode($params);
            }
            $option[CURLOPT_POSTFIELDS] = $params;
            $option[CURLOPT_URL] = $url;
        }
        else
        {
            if ($params)
            {
                if (is_array($params))
                {
                    $params = $type == 'url' ? http_build_query($params) : json_encode($params);
                }
                $option[CURLOPT_URL] = $url.'?'.$params;
            }
            else
            {
                $option[CURLOPT_URL] = $url;
            }
        }
        $response = $this->initialization()->setopt($option)->exec();
        if ($response === false)
        {
            return false;
        }
        $this->close();
        return $response;
    }

    /**
     * 微信提现专用
     * @param       $url  请求网址
     * @param int   $type 请求方式
     * @param array $data 请求参数
     * @param cert 安装证书
     * @return bool|mixed
     */
    public function curlGetCash($url, $type = 'POST', $data = [], $cert = [])
    {
        $option = [
            CURLOPT_URL             => $url,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_RETURNTRANSFER  => 1,
        ];
        if (strtoupper($type) == 'POST') {
            $option[CURLOPT_POST] = 1;
            $option[CURLOPT_POSTFIELDS] = $data;
        }
        foreach ($cert as $key => $item) {
            $option[constant($key)] = $item;
        }
        $output = $this->initialization()->setopt($option)->exec();
        $this->close();
        $jsoninfo = json_decode($output, true);
        //返回未处理的内容
        return $output;
    }

    public function __construct()
    {

    }

    /**
     * CURL INIT
     * @return $this
     */
    public function initialization()
    {
        $this->ch = curl_init();
        return $this;
    }

    /**
     * SET OPTIONS
     * @param array $option
     * @return $this
     */
    public function setopt($option = [])
    {
        if (is_array($option))
        {
            foreach ($option as $key => $value)
            {
                curl_setopt($this->ch, $key, $value);
            }
        }
        return $this;
    }

    /**
     * EXEC
     * @return bool|string
     */
    public function exec()
    {
        return curl_exec($this->ch);
    }

    /**
     * GET INFO
     * @param null $opt
     * @return mixed
     */
    public function info($opt = null)
    {
        return curl_getinfo($this->ch, $opt);
    }

    /**
     * CLOSE
     */
    public function close()
    {
        curl_close($this->ch);
    }

    /**
     * ERROR MESSAGE
     * @return string
     */
    public function error()
    {
        return curl_error($this->ch);
    }


    /**
     * 并行执行curl
     * @param string $urls 请求地址
     * @param array $params 请求参数
     * @param array $options curl 请求配置
     * @return array
     */
    public function multiCurl($urls, array $params = [], array $options = []) {
        if (count($urls) <= 0) {
            return [];
        }

        $handles = array();
        if (!$options) // add default options
        {
            $options = array(
                // CURLOPT_HEADER => 1,
                // CURLOPT_NOBODY => 1,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT        => 30,
            );
        }

        if ($params)
        {
            if (is_array($params))
            {
                $options[CURLOPT_POSTFIELDS] = http_build_query($params);
            }
        }

        // add curl options to each handle
        foreach ($urls as $k => $url) {
            $ch{$k} = curl_init();

            $options[CURLOPT_URL] = $url;
            if (strpos($url, 'https:') !== false)
            {
                $options[CURLOPT_SSL_VERIFYPEER] = false;
                $options[CURLOPT_SSL_VERIFYHOST] = false;
            }

            $opt = curl_setopt_array($ch{$k}, $options);
            // var_dump($opt);
            $handles[$k] = $ch{$k};
        }

        $mh = curl_multi_init();
        // add handles
        foreach ($handles as $k => $handle) {
            $err = curl_multi_add_handle($mh, $handle);
        }
        $running_handles = null;
        do {
            curl_multi_exec($mh, $running_handles);
            curl_multi_select($mh);
        } while ($running_handles > 0);

        $rels = [];
        foreach ($urls as $k => $url) {
            $err = curl_error($handles[$k]);
            if (!empty($err)) {
                $rels[$k] = '';
            } else {
                $rels[$k] = curl_multi_getcontent($handles[$k]);
            }
            // get results

            // close current handler
            curl_multi_remove_handle($mh, $handles[$k]);
        }
        curl_multi_close($mh);
        return $rels; // return response
    }

    //参数1：访问的URL 的示例，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
    private function curl_request_example ($url, $post='', $cookie='', $returnCookie=0) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
            return $data;
        }
    }
}