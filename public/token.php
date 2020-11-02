<?php

$appkey = 'wx199ac064e26dfa28';
$appSecret = '69a35c215dd34768bfb512667ab6870c';

$token = get_token($appkey, $appSecret);
var_dump($token);
function get_token($appkey, $appSecret)
{

    $api_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appkey.'&secret='.$appSecret;
    $res = curl_request($api_url);

    $token = json_decode($res,true);
    return $token;
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