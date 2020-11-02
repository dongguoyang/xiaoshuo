<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\PlatformWechat;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class PlatformWechatRepository extends Repository {
	public function model() {
		return PlatformWechat::class;
	}
    /**
     * 通过客户ID获取公众号信息
     * @param int $customer_id
     * @return array
     */
    public function getWechatForCustomer($customer_id) {
        $key = $this->getCacheKey($customer_id);
        return Cache::remember($key, 10, function() use ($customer_id) {
            $info = $this->findBy('customer_id', $customer_id, $this->model->fields);
            return $this->toArr($info);
        });
    }
    /**
     * 通过客户ID获取公众号信息
     * @param int $customer_id
     * @return array
     */
    public function getWechatForId($id) {
        $key = config('app.name') . 'platformwechat_for_id_' . $id;
        return Cache::remember($key, 10, function() use ($id) {
            $info = $this->find($id, $this->model->fields);
            return $this->toArr($info);
        });
    }
    /**
     * 清除缓存
     */
    public function ClearCache($customer_id, $platform_wechat_id = 0) {
        Cache::forget($this->getCacheKey($customer_id));
        Cache::forget(config('app.name') . 'platformwechat_for_id_' . $platform_wechat_id);
    }

    public function getCacheKey($customer_id) {
	    return config('app.name') . 'platformwechat_for_customer_id_' . $customer_id;
    }

    /**
     * 通过开放平台APPID和appid获取公众号信息
     * @param int $customer_id
     * @return array
     */
    public function getWechatForCAPPID2APPID($component_appid, $app_id) {
        $key = config('app.name') . 'platformwechat_for_'.$component_appid.'_app_id_' . $app_id;
        return Cache::remember($key, 1440, function() use ($component_appid, $app_id) {
            $info = $this->findByMap([
                ['component_appid', $component_appid],
                ['appid', $app_id],
            ], ['id', 'customer_id', 'platform_id', 'app_name', 'img']);
            return $this->toArr($info);
        });
    }
}