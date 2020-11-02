<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/



Route::any('/payment/moneybtns', 'App\PaymentController@MoneyBtns'); // 金额按钮

Route::any('/common/test',  'App\CommonController@Test'); // 测试方法
Route::any('/common/sets',  'App\CommonController@CommonSets'); // 公共配置获取
Route::any('/common/texts', 'App\CommonController@CommonTexts'); // 公共长文本获取
Route::any('/common/wechatinfo', 'App\CommonController@WechatInfo'); // 获取公众号信息
Route::any('/novel/sync','App\NovelController@NovelSyncApi'); //小说外放数据同步接口
Route::any('/novel/section_content','App\NovelController@NovelSectionContent'); //小说外放章节内容获取接口
//Route::any('/getNovel','App\Http\NovelController@GetNovel'); //小说外放
Route::any('/del/del_book','DelController@DelBook'); //删除连载小说
Route::any('/tx/user','App\CommonController@TxGetInfo'); //头条推广信息获取



// 域名检测路由
Route::get('domaincheck/{type_id}', 'Platform\DomainCheckController@domain')->where('type_id', '[0-9]+')->name('api.check.domain'); // domains 表的域名检测
Route::get('domaincheck/justcheckdomain', 'Platform\DomainCheckController@justCheckDomain')->name('customer.check.domain');         // domain_checks 检测域名
Route::get('domaincheck/insertcheckdomain', 'Platform\DomainCheckController@insertCheckDomain')->name('insert.check.domain');       // 接收客户检测域名和停用域名
Route::get('domaincheck/wechat', 'Platform\DomainCheckController@wechat'); // 公众号授权域名检测
Route::get('domaincheck/paywechat', 'Platform\DomainCheckController@payWechat'); // 支付公众号授权域名检测

// 公众号模式事件处理
// http://dev.zmr029.com/api/officialaccount/wechatevent?appid=APPID 一定要跟当前公众号appid;并且不能开调试模式且只能直接输出；
Route::any('officialaccount/wechatevent', 'Platform\OfficialAccountsController@wechatEvent')->name('officialaccount.wechatevent');       // 公众号模式的事件处理
Route::any('officialaccount/test', 'Platform\OfficialAccountsController@test')->name('officialaccount.test');   