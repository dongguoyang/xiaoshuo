<?php
namespace App\Http\Controllers\App;
/**
 * 所有的操作
 * 签到；评论；打赏
 */
use App\Http\Controllers\BaseController;
use App\Logics\Services\src\UserService;
use App\Logics\Traits\LoginTrait;

class UserController extends BaseController
{
    use LoginTrait;
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * H5 微信授权登录
     */
    public function WechatLogin($wechat_id = 0) {
        try
        {
            //return $this->service->WechatLogin();
            return $this->service->OfficialAccountLogin();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }

    /**
     * H5 微信授权登录域名不对的跳转登录
     */
    public function RedirectLogin() {
        try
        {
            return $this->service->RedirectLogin();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取用户信息
     */
    public function UserInfo() {
        try
        {
            return $this->service->UserInfo();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
}