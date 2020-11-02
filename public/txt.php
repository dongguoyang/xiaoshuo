<?php
// 微信txt访问文件；服务器配置重写规则后可以直接访问
// rewrite ^/(.*?)\.txt$   /txt.php?txt=$1.txt last;

$file = isset($_GET['txt']) ? $_GET['txt'] : '';
if (!$file) {
	die('文件名不存在！请确认！');
}

$url = "https://moviefiless.oss-cn-hangzhou.aliyuncs.com/txt/" . $file;

$str = @file_get_contents($url);


if (!$str) {
	$url = "https://xiaoshuo01.oss-cn-chengdu.aliyuncs.com/txt/" . $file;
	$str = @file_get_contents($url);
}

if (!$str) {
	die('OSS 里文件不存在！');
}

die($str);

$url = "https://moviefiless.oss-cn-hangzhou.aliyuncs.com/txt/" . $_GET['txt'];
$ch = curl_init();
$timeout = 5;
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
$contents = curl_exec($ch);
curl_close($ch);
echo $contents;

?>
