<?php

return [
    // 阿里云短信所需配置信息
    'aliyun' => [
        'access_key_id' => env('ALIYUN_SMS_AK', ''),
        'access_key_secret' => env('ALIYUN_SMS_AS', ''),
        'sign_name' => env('ALIYUN_SMS_SIGN_NAME', ''),
    ],
];
