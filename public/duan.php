<?php
$appkey = 'wxb6507ac73751cb66';
$appSecret = 'ed8499338e34dffc0c432c3aab07887a';
$token = get_token($appkey, $appSecret);
$url = 'http://tuytjhgjhgjh.oss-cn-hangzhou.aliyuncs.com/?temp=jinganglang_index&';
$shot_url = get_short_url($token,$url);
var_dump($shot_url);
echo $shot_url;
function get_token($appkey, $appSecret)
{

    $api_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appkey.'&secret='.$appSecret;
    $res = curl_request($api_url);
    $token = json_decode($res,true)['access_token'];
    return $token;
}

function get_short_url($token, $longUrl)
{
    $data = [
        'access_token'=>$token,
        'action'=>'long2short',
        'long_url'=>$longUrl,
    ];
    $url = 'https://api.weixin.qq.com/cgi-bin/shorturl?access_token='.$token;
    $data = json_encode($data);
    $res = curl_request($url, $data);
    $shot_url = json_decode($res, true)['short_url'];
    return $shot_url;
}

function curl_request($url, $data=[])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}


