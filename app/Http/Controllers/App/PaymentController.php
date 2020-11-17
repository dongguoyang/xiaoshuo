<?php
namespace App\Http\Controllers\App;
/**
 * 支付金钱相关
 * 充值；金额按钮；支付回调
 */
use App\Http\Controllers\BaseController;
// use App\Logics\Services\src\PaymentService;
use App\Logics\Services\src\PaymentWechatService;

class PaymentController extends BaseController {
    public function __construct(PaymentWechatService $service)
    {
        $this->service = $service;
    }

    /**
     * 获取充值金额按钮列表
     */
    public function MoneyBtns() {
        try
        {
            return $this->service->MoneyBtns();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 登录并统一下单
     */
    public function Login2Unifiedorder() {
        try
        {
            return $this->service->Login2Unifiedorder();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 支付宝异步通知地址
     */
    public function NotifyUrl($type = 'wechat', $id = 0)
    {
        try
        {
            return $this->service->NotifyUrl($type,$id);
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 支付成功后同步通知地址
     */
    public function ReturnUrl($type = 'wechat')
    {
        try
        {
            return $this->service->ReturnUrl($type);
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 查询是否支付成功
     */
    public function FindSucc()
    {
        try
        {
            return $this->service->FindSucc();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 查询是否支付成功
     */
    public function OutTradeNoCheck()
    {
        try
        {
            return $this->service->OutTradeNoCheck();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }

    //每天清空支付号的今日支付金额
    public function EmptyMoneyToday(){
        try
        {
            return $this->service->empty_money_today();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }

}