<?php
namespace App\Http\Controllers\App;
/**
 * 所有的操作
 * 签到；评论；打赏
 */
use App\Http\Controllers\BaseController;
use App\Logics\Services\src\OperateService;
use App\Logics\Traits\LoginTrait;

class OperateController extends BaseController
{
    use LoginTrait;
    public function __construct(OperateService $service)
    {
        $this->service = $service;
    }

    /**
     * 获取用户的签到信息
     */
    public function SignInfo() {
        try
        {
            return $this->service->SignInfo();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 用户执行签到
     */
    public function DoSign() {
        try
        {
            return $this->service->DoSign();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 小说打赏记录
     */
    public function RewardList() {
        try
        {
            return $this->service->RewardList();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 小说评论记录
     */
    public function CommentList() {
        try
        {
            return $this->service->CommentList();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 打赏商品列表
     */
    public function GoodsList() {
        try
        {
            return $this->service->GoodsList();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 执行打赏操作
     */
    public function DoReward() {
        try
        {
            return $this->service->DoReward();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 执行评论操作
     */
    public function DoComment() {
        try
        {
            return $this->service->DoComment();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 执行加入书架操作
     */
    public function AddBookStores() {
        try
        {
            return $this->service->AddBookStores();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 用户优惠券列表
     */
    public function UserCoupons() {
        try
        {
            return $this->service->UserCoupons();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 用户充值记录
     */
    public function RechargeLogs() {
        try
        {
            return $this->service->RechargeLogs();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 用户书币消费记录
     */
    public function CoinLogs() {
        try
        {
            return $this->service->CoinLogs();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 奖品列表
     */
    public function PrizeList() {
        try
        {
            return $this->service->PrizeList();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 用户中奖记录列表
     */
    public function UserPrizeLog() {
        try
        {
            return $this->service->UserPrizeLog();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 执行抽奖
     */
    public function DoPrize() {
        try
        {
            return $this->service->DoPrize();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 执行反馈记录
     */
    public function DoFeedback() {
        try
        {
            return $this->service->DoFeedback();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 送书币活动
     */
    public function CoinAct() {
        try
        {
            return $this->service->CoinAct();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 书币活动的信息
     */
    public function CoinActInfo() {
        try
        {
            return $this->service->CoinActInfo();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
}