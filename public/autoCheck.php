<?php
function check(){
    swoole_timer_tick(5000, function (){
        $result = httpGet();
        var_dump($result);
    });
}

function httpGet(){
    $url = "127.0.0.1:81/api/domaincheck/newcheckdomain";
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_TIMEOUT, 2);
    curl_setopt($curl,CURLOPT_URL,$url);
    $res = curl_exec($curl);
    var_dump(curl_error($curl));
    curl_close($curl);
    return $res;
}
check();

