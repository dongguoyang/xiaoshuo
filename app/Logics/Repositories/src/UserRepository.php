<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\User;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;
use App\Logics\Services\src\UserService;
use App\Logics\Models\ToutiaoUser;

class UserRepository extends Repository {
	public function model() {
		return User::class;
	}
	/**
	 * 更新用户数据
     * @param int $user_id
     * @param array $updata 更新的信息字段
     */
	public function UpdateFromDB($user_id, array $updata) {
	    $user = $this->find($user_id, $this->model->fields);
	    $data = [];
	    foreach ($updata as $key => $val) {
	        if ($user[$key] !== $val) {
	            if (is_string($val) && is_numeric($user[$key]) && in_array(substr($val, 0, 1), ['+', '-'])) {
	                // 数值加减运算
	                if (substr($val, 0, 1) == '+') {
	                    $data[$key] = $user[$key] + intval(substr($val, 1));
                    } else {
                        $data[$key] = $user[$key] - intval(substr($val, 1));
                    }
                } else {
                    $data[$key] = $val;
                }
            }
        }
        if (!$data || !$this->update($data, $user_id)) {
            throw new \Exception('用户信息更新失败...！', 2000);
        }

        return $this->UserCacheInfo($user_id, true);
    }


    /**
     * @param int $id 用户ID
     * @param bool $up_cache 是否更新缓存
     * @return array
     */
    public function UserCacheInfo($id, $up_cache=false)
    {
        $default = ['id' => 0, 'name' => '', 'img' => '', 'tel' => ''];
        if (!$id) return $default;
        $key = $this->getCacheKey($id);
        if ($up_cache && Cache::has($key)) {
            Cache::forget($key);
        }
        $user = Cache::remember($key, 1440, function () use ($id, $default){
            $user = Optional($this->find($id, $this->model->fields))->toArray();
            if (!$user) {
                return $default;
            }
            /*if ($user['today_money_time'] < strtotime('today')) {

            }*/
            return $user;
        });
        return $user;
    }
    /**
     * 把用户信息保存到缓存里
     * @param array $user 最新的用户信息
     */
    public function UserToCache($user)
    {
        if (!$user['id']) return [];

        return $this->updateUserToDB($user);
    }
    /**
     * 更新用户信息，同时更新缓存
     * @param array $user 最新用户信息
     * @return mixed
     */
    public function updateUserToDB($user)
    {
        $cache_user = $this->UserCacheInfo($user['id']);
        $data = [];
        foreach ($cache_user as $k=>$v)
        {
            if ($v !== $user[$k]) {
                $data[$k] = $user[$k];
            }
        }

        if ($data && !$this->update($data, $user['id'])) {
            throw new \Exception('用户信息更新失败1！', 2000);
        }

        return $this->UserCacheInfo($user['id'], true);
    }
    /**
     * 清除用户缓存信息
     * @param int $id 用户id
     * @return bool
     */
    public function ClearCache($id)
    {
        $key = $this->getCacheKey($id);
        if (Cache::has($key)) Cache::forget($key);
        return true;
    }

    public function getCacheKey($id) {
        return config('app.name') . 'user_info_' . $id;
    }

    /**
     * 用户关注事件处理
     * @param int $platform_wechat_id
     * @param string $openid
     * @param string $event
     */
    public function SubscribeEvent($platform_wechat_id, $openid, $event) {
        if (!in_array($event, ['subscribe', 'unsubscribe'])) return false;
        $user = $this->model->where('platform_wechat_id', $platform_wechat_id)->where('openid', $openid)->select(['id', 'subscribe', 'name', 'customer_id', 'first_account', 'extend_link_id', 'created_at'])->first();
        if (!$user) return false;
        $this->UpdateFromDB($user['id'], ['subscribe'=>($event=='subscribe' ? 1 : 0)]);
        $user_id = $user['first_account'] > 0 ? $user['first_account'] : $user['id'];
        if ($event=='subscribe') {
            $statisticRep = new StatisticRepository();
            $statisticRep->AddSubscribeNum($user['customer_id']);

            $extendLinkRep = new ExtendLinkRepository();
            if ($user['extend_link_id'] && $user['created_at'] + $extendLinkRep->validTime > time()) {
                // 推广链接增加关注人数
                $extendLinkRep->UpdateInfo($user['extend_link_id'], ['subscribe'=>1]);
            }
        } else {
            $key = config('app.name') . 'push_second_msg_' . $user_id .'_' . $user['customer_id'];
            if (Cache::has($key)) Cache::forget($key);
            $key = config('app.name') . 'recharge_msg_' . $user_id .'_'.$user['customer_id'];
            if (Cache::has($key)) Cache::forget($key);
        }
        $key = config('app.name') . 'sub_user_info_'. $user_id .'_'.$user['customer_id'];
        //数据上报
        //(new UserService())->PutTouInfo(1,$user['id']);
        if (Cache::has($key)) Cache::forget($key);
    }

    /**
     * 记录/获取 用户互动情况
     * @param array $wechat
     * @param string $openid 没有 openid 表示获取
     */
    public function NoteInteractInfo($wechat, $openid = '') {
        $key = config('app.name') . 'interact_info' . $wechat['customer_id'] . '_'. $wechat['id'];
        $had = Cache::get($key);
        if (IsJson($had)) {
            $had = json_decode($had, 1);
        }
        if (!$openid) {
            // 获取值
            return $had;
        }
        // 查看用户是否正常
        $user = $this->model->where('platform_wechat_id', $wechat['id'])->where('openid', $openid)->select(['id', 'openid'])->first();
        if (!$user) {
            return false;
        }

        // 存储值
        if ($had) {
            Cache::forget($key);
            if (!Cache::get($key.'check')) {
                $now = time();
                Cache::put($key.'check', 1, ((strtotime(date('Y-m-d', $now)) + 86400) / 60));
                foreach ($had as $k=>$v) {
                    if ($v[1] < $now) {
                        unset($had[$k]);
                    }
                }
            }
        } else {
            $had = [];
        }
        $had[$user['id']] = [$user['openid'], (time()+86400*2)];
        return Cache::remember($key, 2880, function ()use ($had){
            return $had;
        });
    }


    /**
     * 通过 openid 查询用户信息
     */
    public function FindByOpenid($openid, $fields = []) {
        if (!$fields) $fields = $this->model->fields;
        $user = $this->model
            ->where('openid', $openid)
            ->orderBy('id', 'asc')
            ->first($fields);

        return $this->toArr($user);
    }
    /**
     * 通过 pay openid 查询用户信息
     */
    public function FindByPayOpenid($pay_openid) {
        $user = $this->model
            ->where('pay_openid', $pay_openid)
            ->orderBy('id', 'asc')
            ->first($this->model->fields);

        return $this->toArr($user);
    }
    /**
     * 查询用户当前公众号下的openid
     */
    public function GerRealOpenid($user_id, $customer_id) {
        $info = $this->findByMap([
            ['first_account', $user_id],
            ['customer_id', $customer_id],
        ], ['openid']);
        if ($info)
            return $info['openid'];
        else
            return  null;
    }

    /**
     * 子用户的用户信息
     * @param int $first_account 主用户ID
     * @param int $fir$customer_id 客户ID
     * @param bool $up_cache 是否更新缓存
     * @return array
     */
    public function SubUserCacheInfo($first_account, $customer_id, $up_cache=false)
    {
        if ($first_account <= 0 || !$customer_id) {
            return false;
        }

        $key = config('app.name') . 'sub_user_info_'.$first_account .'_'.$customer_id;
        if ($up_cache) {
            Cache::forget($key);
        }
        return Cache::remember($key, 1440, function () use ($first_account, $customer_id){
            $user = $this->findByMap([
                ['customer_id', $customer_id],
                ['first_account', $first_account],
            ], ['id', 'subscribe', 'customer_id', 'platform_wechat_id', 'first_account', 'created_at', 'extend_link_id', 'wechat_qrcode_id']);

            if ($user) {
                return $this->toArr($user);
            }

            return $user;
        });
    }
    
}