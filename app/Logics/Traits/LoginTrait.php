<?php
namespace App\Logics\Traits;

use App\Logics\Repositories\src\UserRepository;
use Illuminate\Database\Eloquent\Model;

trait LoginTrait
{
    /**
     * 实例化/获取 UserRepository
     */
    private function getUserInLoginTrait()
    {
        if (isset($this->user) && $this->user) {
            return $this->user;
        } else {
            $user = new UserRepository();
            return $user;
        }
    }
    /**
     * 保存登录状态
     * @param $user
     * @return mixed
     */
    public function saveLogin($user)
    {
        $data = $this->loginSaveToSession($user);

        return $data;
    }
    /**
     * 重新设定用的view_cid
     * @param int $customer_id
     * @param array $sess 用户缓存信息
     */
    public function resetUserViewCid($customer_id,$sess = []) {
        if (!$sess) {
            $sess = $this->loginGetSession(true);
        }
        if (!$sess || !$sess['id']) { // 没有缓存就返回
            return false;
        }
        if (!$customer_id) {
            $customer_id = $sess['customer_id'];
        }
        //dd($sess = session('novelsys_database_novelsys_cache:CxBlc0bkpxYGhOXV7n0k2RBlTtpamBHlTVD5GHOF'));
        if ($sess['view_cid'] == $customer_id) {
            // view_cid 没变；就返回
            return true;
        }
        $sess['view_cid'] = $customer_id;
        $this->loginSaveToSession($sess);
    }

    /**
     * 保存登录信息到session里面
     * @param $user
     * @return array
     */
    public function loginSaveToSession($user)
    {
        $data = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'balance'   => $user['balance'],
            'view_cid'  => (isset($user['view_cid']) && $user['view_cid']) ? $user['view_cid'] : $user['customer_id'],
        ];

        try {
            // 更新子账户的一个无用字段；去更新updated_at；用于推送数据
            $_unionid = date('_Ymd');
            if (isset($user['unionid']) && $_unionid != $user['unionid']) {
                $this->getUserInLoginTrait()->updateByMap(['unionid' => $_unionid], [
                    ['first_account', $user['id']],
                    ['customer_id', $user['view_cid']],
                ]);
            }
            $user = $this->getUserInLoginTrait()->UserToCache($user);
            if ($user['view_cid'] != $data['view_cid']) $this->getUserInLoginTrait()->ClearCache($user['id']);
        } catch (\Exception $e) {
            $this->getUserInLoginTrait()->ClearCache($user['id']);
        }

        session(['is_login' => $data]);
        return ['name'=>$data['name'], 'balance'=>$data['balance'], 'view_cid'=>$data['view_cid']];
    }
    /**
     * 获取session 登录信息
     * @param bool $cache 是否获取用户缓存信息
     * @return array
     */
    public function loginGetSession($cache = false)
    {
        $default = [
            'id'    => 0,
            'name'  => '',
            'balance'   => '',
            'view_cid'  => '',
        ];
        /*if ((isset($_SERVER['HTTP_ORIGIN']) && strpos($_SERVER['HTTP_ORIGIN'], '127.0.0.1')) || request()->ip() == '127.0.0.1' || request()->input('zmrtest')=='abcd666520') {
            // 本地调试随机获取一个用户做登录用户
            $map = [['id', '>', 0]];
            if ($cid = request()->input('customer_id')) $map[] = ['customer_id', $cid];
            $default = $this->getUserInLoginTrait()->findByMap($map, $this->getUserInLoginTrait()->model->fields);
	    }*/
        $sess = session('is_login', $default);
        $sess = ($sess instanceof Model) ? $sess->toArray() : $sess;
        return $cache ? $this->getUserInLoginTrait()->UserCacheInfo($sess['id']) : $sess;
    }

    /**
     * 退出登录
     */
    public function logoutToSession()
    {
        $sess = $this->loginGetSession();
        $this->getUserInLoginTrait()->UserClearCache($sess['id']);   // 删除用户缓存信息

        return session()->forget('is_login');
    }
}
