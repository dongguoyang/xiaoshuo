<?php

return [
    'first_recharge'    => '/#/activity-recharge.html',  // 首充优惠页面
    'coin_act'          => '/#/activity-recharge2.html', // 书币活动页面
    'recharge_act'      => '/#/activity-recharge3.html', // 充值优惠活动页面
    'recharge'          => '/#/pay.html',
    'read_log'          => '/#/book-history.html',
    'read_novel_log'    => '/#/read/{novel_id}-0.html',
    'index'             => '/#/index.html',
    // 'index'             => '/#/index1.html', // 新首页
    'sign'              => '/#/sign-in.html',
    'hot_rank'          => '/#/list/weekly-man.html',
    'rank'              => '/#/top-list.html',
    'week_news'         => '/#/list/news-man.html',
    'center'            => '/#/mine.html',
    'contact'           => '/#/contact.html',
    'novel'             => '/#/detail/novel-{novel_id}.html',
    // 'section0'          => '/#/read3/{novel_id}-{section_num}.html', // 新阅读页
    'section0'          => '/#/read/{novel_id}-{section_num}.html',
    'section'           => '/home/inner-section.html?#novel_id={novel_id}&section={section_num}',  // 阅读页面的单独页
    // 'section_out'       => '/#/read2/{novel_id}-{section_num}.html',    // 外推链接的阅读页面
    'section_out'       => '/home/out-section.html?#novel_id={novel_id}&section={section_num}',    // 外推链接的单独的阅读页面
];
