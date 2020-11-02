<?php
// 获取 公众号 token 的代码；一般都走这里获取

$func = $_GET['func'];

$rel = $func();

exit($rel);

/**
 * 获取 网页授权登录access token
 * @return type
 */
function getAccessToken() {
	$appid = $_GET['appid'];
	$app_secret = $_GET['app_secret'];
	$passwd = $_GET['passwd'];
	if (md5($appid . 'zmr-37892jkfds' . $app_secret) != $passwd) {
		exit('passwd err');
	}

	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$app_secret}";
	$rel = file_get_contents($url);

	return $rel;
}

/**
 * 获取 开放平台模式的公众号网页授权登录access token
 * @return type
 */
function getPlatWechatAccessToken() {
    $component_access_token = $_GET['component_access_token'];
    $component_appid = $_GET['component_appid'];
    $authorizer_refresh_token = $_GET['authorizer_refresh_token'];
    $authorizer_appid = $_GET['authorizer_appid'];
    $passwd = $_GET['passwd'];
    if (md5($authorizer_appid . $component_appid . 'zmr-37892jkfds' . $component_access_token) != $passwd) {
        exit('passwd err');
    }

    $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=' . $component_access_token;
    $data = [
        'component_appid' => $component_appid,
        'authorizer_appid' => $authorizer_appid,
        'authorizer_refresh_token' => $authorizer_refresh_token,
    ];
    $rel = CallCurl($url, $data, 1, 'json');

    return $rel;
}

/**
 * 获取 开放平台的 token
 * @return type
 */
function getComponentToken() {
    $component_appid = $_GET['component_appid'];
    $component_appsecret = $_GET['component_appsecret'];
    $component_verify_ticket = $_GET['component_verify_ticket'];
    $passwd = $_GET['passwd'];
    if (md5($component_appid . 'zmr-37892jkfds' . $component_appsecret) != $passwd) {
        exit('passwd err');
    }

    $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
    $data = [
        'component_appid' => $component_appid,
        'component_appsecret' => $component_appsecret,
        'component_verify_ticket' => $component_verify_ticket,
    ];
    $rel = CallCurl($url, $data, 1, 'json');

    return $rel;
}
/**
 * 根据 access_token 获取 jsapi_ticket
 * @return type
 */
function getJsApiTicket() {
	$access_token = $_GET['access_token'];
	$passwd = $_GET['passwd'];
	if (md5($access_token . 'zmr-37892jkfds') != $passwd) {
		exit('passwd err');
	}
	$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";

	$rel = file_get_contents($url);

	return $rel;
}


// 执行curl 请求
function CallCurl($url, $params = false, $ispost = 0, $type = "url", $json_option = '', $header = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($header) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    if (strpos($url, 'https:') !== false) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
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
                if (!$header) curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
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
        return false;
    }
    curl_close($ch);
    return $response;
}