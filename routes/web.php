<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/*
Route::get('/', function () {
    // return view('welcome');/
    return redirect(config('admin.route.prefix'));
});
*/
// Route::redirect('/', '/administrator', 301);

// 开放平台路由
Route::any('/platform/auth', 'Platform\PlatformsContrller@wechatAuth')->name('platform.auth');         // 授权提示跳转页
Route::any('/platform/toauth', 'Platform\PlatformsController@wechatToAuth')->name('platform.toauth');   // 跳转授权页
Route::any('/platform/authed', 'Platform\PlatformsController@wechatAuthed')->name('platform.authed');   // 授权完成页
Route::any('/platform/server', 'Platform\PlatformsController@server')->name('platform.server');         // 授权事件接收URL
// Route::any('/platform/wechatevent', 'Platform\PlatformsController@wechatEvent')->name('platform.wechatevent'); // 公众号消息与事件接收URL
// 域名检测路由
Route::get('domaincheck/{type_id}', 'Platform\DomainCheckController@domain')->where('type_id', '[0-9]+');
Route::get('domaincheck/platform', 'Platform\DomainCheckController@platform');  // 检测开放平台域名
Route::get('domaincheck/wechat', 'Platform\DomainCheckController@wechat');      // 检测微信公众号域名
Route::get('domaincheck/plating', 'Platform\DomainCheckController@plating');    // 定时启用开放平台
Route::get('domaincheck/jumtcheckdomain', 'Platform\DomainCheckController@justCheckDomain');    // 检测域名


/* =====================================================================================================================
 * 小说不检测登录的路由
 *======================================================================================================================*/
Route::any('test/test', 'TestController@test');// 测试页面
Route::any('user/login/wechat', 'App\UserController@WechatLogin');// H5微信登录
Route::any('user/login/wechat/{wechat_id?}', 'App\UserController@WechatLogin');// H5微信登录网页授权返回地址
Route::any('userinfo/lgwechat', 'App\UserController@WechatLogin')->name('h5wechat.login');// H5微信登录
Route::any('userinfo/lgwechatnew', 'App\UserController@WechatLoginNew')->name('h5wechat.newlogin');// 新H5微信登录
Route::any('userinfo/lgwechat/{wechat_id?}', 'App\UserController@WechatLogin')->name('h5wechat.redirect');// H5微信登录网页授权返回地址
Route::any('userinfo/lgwechatnew/{wechat_id?}', 'App\UserController@WechatLoginNew')->name('h5wechat.newredirect');// 新H5微信登录网页授权返回地址

// 小说信息模板
Route::any('novel/toindex', 'App\NovelController@ToIndex')->name('novel.toindex');// 跳转对应客户的小说模板首页
Route::any('novel/tosection', 'App\NovelController@ToSection')->name('novel.tosection');// 跳转对应的小说章节阅读页
Route::any('novel/extendlink/{id}', 'App\NovelController@ExtentLink')->name('novel.extendlink');// 推广链接的入口
Route::any('novel/extendpage', 'App\NovelController@ExtentPage')->name('novel.extendpage');// 推广链接的落地页面
Route::any('novel/indexdata', 'App\NovelController@IndexData');// 获取首页数据
Route::any('novel/indexdatamore', 'App\NovelController@IndexDataMore');// 获取首页数据的更多数据
Route::any('novel/types', 'App\NovelController@NovelTypes');// 获取小说分类信息
Route::any('novel/info', 'App\NovelController@NovelInfo');// 获取小说信息
Route::any('novel/sectioninfo', 'App\NovelController@NovelSection');// 获取小说章节信息
Route::any('novel/sectionlist', 'App\NovelController@SectionList');// 获取小说章节列表
Route::any('novel/search', 'App\NovelController@SearchNovel');// 获取小说章节列表
Route::any('novel/sectionrecommend', 'App\NovelController@SectionRecommend');// 获取小说章节推荐的小说
Route::any('novel/section', 'App\NovelController@SectionInfo');// 获取小说章节信息，不包含正文内容
Route::any('novel/section/add','App\SectionController@addSections'); //添加小说章节
Route::any('novel/collectmp','App\CollectMpController@getNonel'); //小说采集测试

Route::any('operate/rewardlist', 'App\OperateController@RewardList');// 打赏记录
Route::any('operate/commentlist', 'App\OperateController@CommentList');// 评论记录
Route::any('operate/goodslist', 'App\OperateController@GoodsList');// 打赏商品列表
Route::any('operate/prizelist', 'App\OperateController@PrizeList');// 奖品列表
Route::any('operate/coinactinfo', 'App\OperateController@CoinActInfo');// 币活动信息

// 支付通知相关路由
Route::any('payment/topay0/{type?}', 'App\PaymentController@ToPay')->name('payment_topay0');     // 发起支付
Route::any('payment/find0',          'App\PaymentController@FindSucc')->name('payment_find0');   // 查询是否支付成功
Route::any('payment/notify/{type?}/{id?}/{end?}', 'App\PaymentController@NotifyUrl')->name('payment_notify'); // 支付宝异步通知地址
Route::any('payment/return/{type?}', 'App\PaymentController@ReturnUrl')->name('payment_return'); // 支付成功后同步通知地址
Route::any('payment/outtradenocheck','App\PaymentController@OutTradeNoCheck'); // 订单号查询接口
Route::any('payment/login2unifiedorder','App\PaymentController@Login2Unifiedorder')->name('paymen.login2unifiedorder'); // 单独的支付公众号进行支付下单
Route::any('payment/money_btns', 'App\PaymentController@MoneyBtns'); // 充值金额按钮
Route::any('payment/empty_money_today', 'App\PaymentController@EmptyMoneyToday'); // 每天清空支付号的今日支付金额


Route::any('common/jumpto', 'App\CommonController@JumpTo');// 页面跳转
Route::any('jiaoyu/weivip', 'App\CommonController@JumpTo')->name('jumpto');// 页面跳转
Route::any('common/productqrcode', 'App\CommonController@ProductQRCode')->name('productqrcode');// 生成带参数的二维码
Route::any('common/getlongqrcode', 'App\CommonController@GetLongQRCode');// 生成带参数的永久二维码
Route::any('common/wechatparamsqrcode', 'App\CommonController@WechatParamsQrcode');// 获取已经生成好的带参数的二维码
Route::any('user/redirectlogin', 'App\UserController@RedirectLogin')->name('user.redirectlogin');// 域名异常登录后切换域名

/**
 * =====================================================================================================================
 *  下面的路由全部增加了checklogin 中间件 必须登录才能执行
 * =====================================================================================================================
 */
Route::group([
    'middleware' => ['checklogin'],
], function (\Illuminate\Routing\Router $router) {
    Route::any('operate/signinfo', 'App\OperateController@SignInfo');  // 最近一次签到数据
    Route::any('operate/dosing', 'App\OperateController@DoSign');  // 执行签到
    Route::any('operate/doreward', 'App\OperateController@DoReward');  // 执行打赏
    Route::any('operate/docomment', 'App\OperateController@DoComment');  // 执行评论
    Route::any('operate/addbookstore', 'App\OperateController@AddBookStores');  // 执行添加书架
    Route::any('operate/usercoupons', 'App\OperateController@UserCoupons');  // 优惠券列表
    Route::any('operate/rechargelogs', 'App\OperateController@RechargeLogs');  // 充值记录
    Route::any('operate/coinlogs', 'App\OperateController@CoinLogs');  // 书币消费记录
    Route::any('operate/userprizelog', 'App\OperateController@UserPrizeLog');  // 用户中奖记录
    Route::any('operate/doprize', 'App\OperateController@DoPrize');  // 执行抽奖
    Route::any('operate/dofeedback', 'App\OperateController@DoFeedback');  // 保存反馈信息
    Route::any('operate/coinact', 'App\OperateController@CoinAct')->name('operate.coinact');// 书币活动领取书币

    Route::any('novel/bookstore', 'App\NovelController@BookStore');  // 获取书架信息
    Route::any('novel/delbookstore', 'App\NovelController@DelBookStore');  // 删除书架小说
    Route::any('novel/readlist', 'App\NovelController@ReadList');  // 阅读历史记录
    Route::any('novel/delreadlog', 'App\NovelController@DelReadLog');  // 删除阅读历史记录
    Route::any('novel/balanceenough', 'App\NovelController@BalanceEnough');  // 查询余额是否足够阅读下一章

    Route::any('user/userinfo', 'App\UserController@UserInfo');  // 获取用户信息

    Route::any('payment/topay/{type?}', 'App\PaymentController@ToPay')->name('payment_topay'); // 统一下单发起支付
    Route::any('payment/find',          'App\PaymentController@FindSucc')->name('payment_find');      // 查询是否支付成功
});