<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    // 主页与登录
    $router->get('auth/login', 'AuthController@login');
    $router->post('auth/login', 'AuthController@loginCheck');
    $router->resource('auth/users', 'CustomerController');

    $router->get('/', 'HomeController@index')->name('admin.home');

    // 切换账号的路由
    $router->get('customers/changelogin', 'CustomerController@changeLogin');
    // 账号提现的路由
    $router->any('customers/withdrawalLog', 'CustomerController@withdrawalLog');
    // 控制器 resource 路由
    $router->resource('ads', 'AdsController');
    $router->resource('adpositions', 'AdPositionsController');
    $router->resource('types', 'TypesController');
    $router->resource('tags', 'TagsController');
    $router->resource('commonsets', 'CommonSetsController');
    $router->resource('commonsetimg', 'CommonSetImgController');
    $router->resource('commontexts', 'CommonTextsController');
    $router->resource('domaintypes', 'DomainTypesController');
    $router->resource('domains', 'DomainsController');
    $router->resource('checkdomains', 'CheckDomainsController');
    $router->resource('users', 'UserController');
    $router->resource('wechat_user_list', 'WechatsUserListController');
    $router->resource('novels', 'NovelController');
    $router->resource('pagelogs', 'PageLogController');
    $router->any('novel/clear_cache', 'NovelController@ClearCache')->name('novel.clear_cache');
    $router->any('novel/list', 'NovelController@novelList')->name('novelList');
    $router->get('novel/{novel_id}/promotion-list/{target}', 'NovelController@promotionList')->name('novelPromotionList')->where('novel_id', '[0-9]+');
    $router->any('novel/promotion/create', 'NovelController@createPromotionLink')->name('createPromotionLink');
    $router->any('novel/promotion/save', 'NovelController@saveDocumentTemplate')->name('saveDocumentTemplate');
    $router->get('novel/promotion/temp', 'NovelController@getPromotionTemp')->name('getPromotionTemp');
    $router->any('novel/promotion/temp/reset', 'NovelController@resetPromotionTemp')->name('resetPromotionTemp');
    $router->get('novel/document/{target}', 'NovelController@documentDisplay')->name('documentDisplay');
    $router->resource('customers', 'CustomerController');
    $router->resource('coinacts', 'CoinActsController');
    $router->resource('qrcodedatas', 'QrcodeDatasController');
    $router->any('qrcodedatas/setqrcodedatas', 'QrcodeDatasController@SetQrcodeDatas'); //重置数据

    $router->resource('reg','RegController');
    $router->resource('reg_error','RegErrorController');
    $router->resource('reg_null_data','RegNullDataController');


    $router->resource('novel-sections', 'NovelSectionController');
    $router->resource('novelsectionadds', 'NovelSectionAddController');
    $router->get('nsections/import', 'NovelSectionController@import')->name('novel_sections_import_page');// WARNING: never set a uri something that has a prefix which is just the resource uri... Holy shit, I have been working on this shit for so long . [ comments from Neptune ]
    $router->post('nsections/import/do', 'NovelSectionController@doImport')->name('novel_sections_import_do');
    $router->get('section/delchar', 'NovelSectionController@FreshSpecailChart'); // 更新小时章节信息；去除无用字符串
    $router->get('section/resetstyle', 'NovelSectionController@ResetStyle'); // 更新小时章节信息；增加段落样式
    $router->get('section/append2novel', 'NovelSectionController@AppendSection2Novel'); // 追加小说章节到另一本后面
    // $router->any('_handle_form_', '\Encore\Admin\Controllers\HandleController@handleForm')->name('admin.handle-form');// for debug
    $router->resource('customer-money-logs', 'CustomerMoneyLogController');
    $router->resource('recharge-logs', 'RechargeLogController');
    $router->resource('prize-logs', 'PrizeLogController');
    $router->resource('reward-logs', 'RewardLogController');
    $router->resource('sign-logs', 'SignLogController');
    $router->resource('read-logs', 'ReadLogController');
    $router->resource('coin-logs', 'CoinLogController');
    $router->resource('coupon-logs', 'CouponLogController');
    $router->resource('novel_pay_logs', 'NovelPayLogController');
    $router->resource('novel_pay_info', 'NovelPayInfoController');
    $router->resource('read_novel_logs', 'ReadNovelLogsController');
    $router->resource('read_novel_info', 'ReadNovelInfoController');
    $router->resource('coupons', 'CouponController');
    $router->resource('goods', 'GoodsController');
    $router->resource('prizes', 'PrizeController');
    $router->resource('authors', 'AuthorController');
    $router->resource('comments', 'CommentController');
    $router->resource('indexpages', 'IndexPagesController');
    $router->resource('extend-links', 'ExtendLinkController');
    //$router->redirect('extend-links', 'extend', 301);
    $router->resource('wechat_msg_replies', 'WechatMsgRepliesController');
    $router->resource('wechat_new_menu', 'WechatNewMenuController');
    $router->any('template_msgs/sendnow', 'TemplateMsgsController@SendNow'); // 立即发送模板消息
    $router->resource('template_msgs', 'TemplateMsgsController');
    $router->resource('group_manager','GroupManagerController');
    // $router->redirect('group_manager', 'customers', 301);
    $router->resource('interactivemsg','InteractMsgController');
    $router->any('extend/extendpage','ExtendController@extendPage'); // 推广文案编辑页面
    $router->any('extend/getnice','ExtendController@getnice')->name('html2canvas'); //
    $router->any('extend/dowloads','ExtendController@dowloads')->name('downloadMaterial');
    //链接管理
    $router->any('orderInfo_ex','ExtendController@orderInfo');
    $router->post('extend/delete_extend','ExtendController@deleteExtend');
    $router->resource('extend','ExtendController');
    $router->resource('paywechats','PayWechatsController');
    $router->resource('wechats','WechatsController');
    $router->resource('storageimgs','StorageImgsController');
    $router->resource('storagetitles','StorageTitlesController');





    //微信回复信息管理
    $router->any('wechat_msg_insert','WechatMsgRepliesController@wechat_msg_insert');
    $router->any('wechat_msg_blade_insert','WechatMsgRepliesController@insert');
    $router->any('wechat_msg_blade_getphoto','WechatMsgRepliesController@getphoto');
    $router->any('wechat_msg_blade_layeropen','WechatMsgRepliesController@layeropen');
    $router->any('wechat_msg_delete/{id}','WechatMsgRepliesController@delete');
    $router->any('wechat_reply_update','WechatMsgRepliesController@reply_update');
    //微信模板消息
    $router->post('template_msg/{id}', 'TemplateMsgsController@delete');
    //组员管理
    $router->post('delete_groupuser', 'GroupManagerController@deleteGroupUser');

    // 单路由配置
    // 缓存管理
    $router->get('/cache/index', 'CacheController@index');
    $router->delete('/cache/flushAll', 'CacheController@flushAll');// 清除所有缓存
    $router->put('/cache/getCacheInfo', 'CacheController@getCacheInfo');
    $router->delete('/cache/delCacheInfo', 'CacheController@delCacheInfo');// 缓存名删除缓存
    $router->put('/cache/getRedisInfo', 'CacheController@getRedisInfo');
    $router->delete('/cache/deleteRedisCache', 'CacheController@deleteRedisCache');// Redis删除
    $router->delete('/cache/delTagsCache', 'CacheController@DelTagsCache'); // 缓存标签删除缓存
    $router->put('/cache/getListInfo', 'CacheController@GetListInfo');// 获取队列的长度
    $router->delete('/cache/delListCache', 'CacheController@DelListCache');// 删除队列信息
    // 检测路由
    $router->any('checks/norepeatHost', 'ChecksController@norepeatHost'); //获取不重复域名
    $router->any('checks/baiduTongji', 'ChecksController@baiduTongji'); //百度统计信息
    $router->get('checks/checkpage', 'ChecksController@checkPage'); //域名检测页面
    $router->get('checks/checkJiexiBeian', 'ChecksController@checkJiexiBeian'); //检测域名解析备案信息
    $router->get('checks/prodomain', 'ChecksController@proDomain'); //生成域名列表
    $router->get('checks/normalInfo', 'ChecksController@normalInfo'); //生成域名列表
    // 域名路由
    $router->post('/domains/dels', 'DomainsController@Dels');// 删除域名
    $router->post('/checkdomains/dels', 'CheckDomainsController@Dels');// 删除域名
    $router->resource('platforms', PlatformsController::class);// 开放平台信息
    $router->resource('platformwechats', PlatformWechatsController::class);// 开放平台信息
    $router->resource('moneybtns', MoneyBtnsController::class);// 金额按钮信息
    $router->resource('wechatqrcodes', WechatQrcodesController::class);// 金额按钮信息
    // API
    $router->any('api/users', 'ApiController@users');
    $router->any('api/customers', 'ApiController@customers');
    $router->any('api/extend-links', 'ApiController@extendLinks');
    $router->any('api/platform-wechats', 'ApiController@platformWechats');
    $router->any('api/authors', 'ApiController@authors');
    $router->any('api/types', 'ApiController@types');
    $router->any('api/novels', 'ApiController@novels');
    $router->any('api/prizes', 'ApiController@prizes');
    $router->any('api/goods', 'ApiController@goods');
    $router->any('api/novel-sections', 'ApiController@novelSections');
    $router->any('api/novel-sections-rel', 'ApiController@novelSectionsRel');
    $router->any('api/coupons', 'ApiController@coupons');
    $router->any('api/material/images', 'ApiController@imageTitles');
    $router->any('chapter/get', 'NovelController@chapterInfo')->name('getChapterInfo');
    $router->any('api/chapter', 'ApiController@getChapter')->name('getChapter');
    $router->any('api/chapter/index', 'ApiController@getChapterByIndex')->name('getChapterByIndex');
    $router->any('api/pre-chapters', 'ApiController@getChapters')->name('getPreChapters');
    // 公众号配置路由
    $router->any('wechatconfigs/index', 'WechatConfigsController@index');
    $router->any('wechatconfigs/subscribe', 'WechatConfigsController@subscribe');
    $router->any('wechatconfigs/subscribe-edit', 'WechatConfigsController@subscribeEdit');
    $router->any('wechatconfigs/subscribenext', 'WechatConfigsController@subscribeNext');
    $router->any('wechatconfigs/subscribenextedit', 'WechatConfigsController@subscribeNextEdit');
    $router->any('wechatconfigs/keyword', 'WechatConfigsController@keyword');
    $router->any('wechatconfigs/menulist', 'WechatConfigsController@menulist');
    $router->any('wechatconfigs/newmenulist', 'WechatConfigsController@newmenulist');
    $router->any('wechatconfigs/centreMenuList', 'WechatConfigsController@centreMenuList');  //中部菜单栏设置
    $router->any('wechatconfigs/centreMenuedit', 'WechatConfigsController@centreMenuView');  //中部菜单栏编辑
    $router->any('wechatconfigs/publish24','WechatConfigsController@publish24');
    $router->any('wechatconfigs/NovelSelect','WechatConfigsController@NovelSelect');
    $router->any('wechatconfigs/pushconf', 'WechatConfigsController@pushconf');
    $router->any('wechatconfigs/dailypush', 'WechatConfigsController@dailypush');
    $router->any('wechatconfigs/searchsub', 'WechatConfigsController@searchsub');
    $router->any('wechatconfigs/searchsubedit', 'WechatConfigsController@searchSubEdit');
    $router->any('wechatconfigs/userhpush', 'WechatConfigsController@userHPush');
    $router->any('wechatconfigs/userhpushedit', 'WechatConfigsController@userHPushEdit');
    $router->any('wechatconfigs/usertags', 'WechatConfigsController@userTags');
    $router->any('wechatconfigs/newmenulistedit', 'WechatConfigsController@newmenulistEdit');
    $router->any('wechatconfigs/shorturl', 'WechatConfigsController@shorturl');
    $router->any('wechat_user_list/updated_user', 'WechatsUserListController@updatedUser');
    $router->any('wechatconfigs/send_message', 'WechatConfigsController@sendMessage');
    $router->any('interactive-msg/index', 'InteractMsgController@index');
    $router->any('interactive-msg/list', 'InteractMsgController@msgListT');
    $router->any('interactive-msg/delete_list', 'InteractMsgController@delete_list');
    $router->any('interactive-msg/add', 'InteractMsgController@add');
    $router->any('interactive-msg/test', 'InteractMsgController@test');
    $router->any('interactive-msg/doadd', 'InteractMsgController@doAdd');
    $router->any('otherdo/customermsg', 'OtherDoController@customerMsg'); // 推送客服消息
    
    // 数据统计
    $router->any('statistics/index', 'StatisticController@index');
    $router->any('statistics/export', 'StatisticController@export');

    //小说外放
    $router->any('get_novel','GetNovelApiController@index');
    
    //小说采集
    $router->any('novelscollect', 'NovelCollectController@index');

    // 域名检测
    $router->get('check', 'ChecksController@checkPage');
    $router->any('checks/domainnums', 'ChecksController@domainNums');

    $router->resource('getcash-logs', 'GetcashLogController');
    $router->any('getcash-logs/money2user', 'GetcashLogController@Money2User');
    //提现审核
    $router->any('getcash-logs/through', 'GetcashLogController@through');


});
