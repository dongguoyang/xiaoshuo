<?php

namespace App\Logics\Services\src;

// use App\Logics\Repositories\src\CheckDomainRepository;
use App\Logics\Models\Test;
use App\Logics\Repositories\src\CommonSetRepository;
use App\Logics\Repositories\src\DomainCheckRepository;
use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\DomainTypeRepository;
use App\Logics\Repositories\src\PayWechatRepository;
use App\Logics\Repositories\src\PlatformRepository;
use App\Logics\Repositories\src\PlatformWechatRepository;
use App\Logics\Repositories\src\WechatRepository;
use App\Logics\Services\Service;
use App\Logics\Traits\SmsTrait;
use App\Logics\Traits\UseTimeTrait;
use Illuminate\Support\Facades\Cache;

class DomainService extends Service {
    use UseTimeTrait, SmsTrait;

    protected $domains;
    protected $domainTypes;
    protected $domainChecks;
    protected $commonSets;
    protected $platformWechats;
    protected $dplatforms;
    protected $wechats;
    protected $payWechats;

    public function Repositories() {
        return [
            'domains'      => DomainRepository::class,
            'domainTypes'  => DomainTypeRepository::class,
            'commonSets'   => CommonSetRepository::class,
            'platformWechats'  => PlatformWechatRepository::class,
            'platforms'    => PlatformRepository::class,
            'wechats'      => WechatRepository::class,
            'payWechats'   => PayWechatRepository::class,
            'domainChecks' => DomainCheckRepository::class,
        ];
    }

    public function test() {
        return $this->commonSets->values('test');
    }
    /**
     * 一般域名检测
     */
    public function domainCH($type_id) {
        if (isset($_GET['id'])) {
            $up = $this->domains->update(['status' => 0], $_GET['id']);
            $info = $this->domains->find($_GET['id'], ['id', 'app', 'type_id']);

            $key = config('app.name') . 'domain_list_'.$type_id.'_'.$info['app'];
            if (Cache::has($key))   Cache::forget($key);
            $key = config('app.name') . 'domain_list_'.$type_id.'_app_'. ($info['app'] ? $info['app'] : '0');
            if (Cache::has($key))   Cache::forget($key);
            $key = config('app.name') . 'domain_list_'.$type_id;
            if (Cache::has($key))   Cache::forget($key);
            $key = config('app.name') . 'domain_list_'.$info['type_id'].'_'.$info['app'];
            if (Cache::has($key))   Cache::forget($key);

            return $up;
        }

        $customers = $this->domains->toPluck($this->domains->model->distinct()->select(['app'])->get(), 'app');
        $type = $this->domainTypes->typeInfo($type_id);
        if (!$type) {
            dd('域名类型不存在！');
        }

        $list = [];
        foreach ($customers as $customer_id) {
            $hosts = $this->domains->hosts($type_id, $customer_id);
            foreach ($hosts as $host) {
                $list[] = [ 'id'=>$host['id'].'', 'url'=>$this->proDomain($host['url'], $type['pre']) ];
            }
        }

        return $list;
    }
    /**
     * 把域名设置成检测的格式
     * @param string $host
     * @param string $pre
     */
    private function proDomain($host, $pre = ''){
        if (strpos($host, 'http') === 0) {
            $arr = explode('://', $host);
            return $arr[1];
        }

        $pre = $pre ?: date('His');
        $pre = substr($pre, -1) == '.' ? $pre : $pre . '.';

        return $pre . $host;
    }
    /**
     * 仅检测域名
     * @param array $list
     * @return Array
     */
    public function justCheckDomain() {
        if (isset($_GET['id'])) {
            $this->domainChecks->updateHost($_GET['id']);
        }
        $list = $this->domainChecks->hosts();

        return $list;
    }
    /**
     * 接收仅检测域名
     * @param array $list
     * @return Array
     */
    public function insertCheckDomain() {
        $data = [];
        $api = request()->input('api');
        if ($api) {
            $hosts = request()->input('hosts');
            // 接收域名存入数据库
            $yes = $no = 0;
            foreach ($hosts as $v) {
                $data = ['c_id'=>$v['id'], 'host'=>$v['url'], 'api'=>$api];
                try{
                    $this->domainChecks->model->insert($data);
                    $yes++;
                } catch (\Exception $e) {
                    $no++;
                }
            }
            return 'y='.$yes . '; n='. $no;
        }

        if (isset($_GET['id']) && $_GET['id']) {
            // 域名停用
            $this->domains->update(['status'=>0], $_GET['id']);
            $info = $this->domains->find($_GET['id'], ['app', 'type_id']);
            $key = config('app.name') . 'domain_list_'.$info['type_id'].'_app_'. ($info['app'] ? $info['app'] : '0');
            if (Cache::has($key))   Cache::forget($key);
            $key = config('app.name') . 'domain_list_'.$info['type_id'];
            if (Cache::has($key))   Cache::forget($key);
            $key = config('app.name') . 'domain_list_'.$info['type_id'].'_'.($info['app'] ? $info['app'] : '0');
            if (Cache::has($key))   Cache::forget($key);
            return 'ok';
        }

        // 发送域名到造梦人检测
        $types = $this->domainTypes->allByMap([['status', 1]], ['id']);
        $list = [];
        foreach ($types as $type) {
            $list = array_merge($list, $this->domainCH($type['id']));
        }
        $url = $this->commonSets->values('domain', 'checkhost') . route('insert.check.domain', [], false);
        //dd($url,$list,request()->getSchemeAndHttpHost() . route('customer.check.domain', [], false));
        // $url = 'http://novel.cn' . route('insert.check.domain', [], false);

        $data = CallCurl($url, ['hosts'=>$list, 'api'=>request()->getSchemeAndHttpHost() . route('customer.check.domain', [], false)]);
        return $data;
    }







    // 公众号域名检测
    public function wechatCH() {
        if (isset($_GET['id'])) {
            $info = $this->wechats->find($_GET['id'], ['redirect_uri', 'bak_host']);
            if ($info['bak_host'] && $info['bak_host']!=$info['redirect_uri']) {
                $this->wechats->update(['redirect_uri' => $info['bak_host']], $_GET['id']);
            }
            if ($this->DomainNotice($info['redirect_uri'])) {
                return 'ok';
            }
            return 0;
        }

        $list = $this->wechats->allByMap([
            ['status', '=', 1],
        ], ['id', 'redirect_uri']);

        $rel = [];
        foreach ($list as $v) {
            $temp['id']  = $v['id'] . '';
            $temp['url'] = $this->proDomain($v['redirect_uri']);
            $rel[] = $temp;
        }

        return $rel;
    }
    // 支付公众号域名检测
    public function payWechatCH() {
        if (isset($_GET['id'])) {
            $info = $this->payWechats->find($_GET['id'], ['redirect_uri', 'bak_host']);
            if ($info['bak_host'] && $info['bak_host']!=$info['redirect_uri']) {
                $this->payWechats->update(['redirect_uri' => $info['bak_host']], $_GET['id']);
            }
            if ($this->DomainNotice($info['redirect_uri'])) {
                return 'ok';
            }
            return 0;
        }

        $list = $this->payWechats->allByMap([
            ['status', '=', 1],
        ], ['id', 'redirect_uri']);

        $rel = [];
        foreach ($list as $v) {
            $temp['id']  = $v['id'] . '';
            $temp['url'] = $this->proDomain($v['redirect_uri']);
            $rel[] = $temp;
        }

        return $rel;
    }

    public function platformCH() {
        if (isset($_GET['id'])) {
            return $this->stopPlatform($_GET['id']);
        }

        $list = $this->platforms->allByMap([
            ['status', '=', 1],
            ['domain_status', '=', 1],
        ], ['id', 'auth_domain', 'host_pre']);

        $rel = [];
        foreach ($list as $v) {
            $domains = ExplodeStr($v['auth_domain']);
            $rel = array_merge($rel, $this->authHosts($domains, $v['host_pre'], $v['id']));
        }

        return $rel;
    }
    /**
     * 开放平台定时切换
     */
    public function plating() {
        $list = $this->commonSets->allByMap([
            ['type', '=', 'platform_random_time'],
            ['status', '=', 1],
        ], ['value', 'name']);
        $up_num = $had_ing = $bad = 0;
        foreach ($list as $k => $v) {
            // 执行对应api 的开放平台切换
            $ing = $this->platformIng(intval(substr($v['name'], 3)), intval($v['value']));
            if ($ing === true) {
                $up_num++;
            } else if ($ing === 'had_ing') {
                $had_ing++;
            } else {
                $bad++;
            }
        }

        $url = 'up_num-' . $up_num . '.had_ing-' . $had_ing . '.bad-' . $bad . '.com.cn';
        return [['id' => '1000000000', 'url' => $url]];
    }
    // 执行对应api 的开放平台切换
    private function platformIng($api, $use_time) {
        list($use_time_start, $use_time_end) = $this->getUseTimeBT($use_time);

        // 查询有没有当前使用公众号
        $plat = $this->platforms->findByMap([
            ['api_type', '=', $api],
            ['status', '=', 1],
            ['use_time', '>', $use_time_start],
            ['use_time', '<=', $use_time_end],
        ], ['id', 'wechat_random', 'appid', 'use_time']);
        if ($plat) {
            return 'had_ing';
        }

        $list = $this->platforms->model->where([
            ['status', '=', 0],
            ['domain_status', '=', 1],
            ['api_type', '=', $api],
        ])->orderBy('use_time')
            ->select(['id', 'wechat_random', 'appid', 'use_time'])
            ->get(); // 查询可用公众号列表
        if ($list) {
            // 有可用公众号就转为数组
            $list = $list->toArray();
            $tem = 0;
            foreach ($list as $v) {
                if (!$v['use_time'] || $tem === 1) {
                    $plat = $v;
                    break;
                }
                if ($this->getCheckOkUseTime($use_time, $v['use_time'], $use_time_end)) {
                    // 标记下一个公众号符合要求
                    $tem = 1;
                }
            }
            if (!$plat) {
                if ($list) {
                    $plat = $list[0];
                } else {
                    $plat = $this->platforms->findByMap([
                        ['api_type', '=', $api],
                        ['status', '=', 0],
                        ['domain_status', '=', 1],
                    ], ['id', 'wechat_random', 'appid', 'use_time']);
                }
            }
        }

        if (!$plat) {
            return false;
        }
        $up = $up2 = false;
        $lastPlat = $this->platforms->findByMap([
            ['status', '=', 1],
            ['api_type', '=', $api],
            ['domain_status', '=', 1],
        ], ['id', 'status', 'appid']);
        if ($lastPlat) {
            $upW = $this->platformWechats->updateByMap(['status' => 1], [
                ['component_appid', '=', $plat['appid']],
                ['api_type', '=', $api],
            ]);
            $up = $this->platforms->update(['use_time' => $use_time_end, 'status' => 1, 'wechat_num' => 5], $plat['id']);
            if ($up) {
                /*$this->platformWechats->updateByMap(['status' => 0], [
                                        ['component_appid', '=', $lastPlat['appid']],
                                        ['api_type', '=', $api],
                */
                $up2 = $this->platforms->update(['status' => 0], $lastPlat['id']);
            }
        }
        return $up && $up2;
    }

    private function stopPlatform($id) {
        $nowP = $this->platforms->find($id);
        $rel = $this->platforms->findByMap([
            ['id', '>', $id],
            ['api_type', '=', $nowP['api_type']],
            ['domain_status', '=', 1],
        ]);
        $en_update['status'] = 1;
        $en_update['wechat_num'] = 1;
        if (!$rel) {
            $rel = $this->platforms->findByMap([
                ['id', '<', $id],
                ['api_type', '=', $nowP['api_type']],
                ['domain_status', '=', 1],
            ]);
        }
        if (!$rel['host_pre']) {
            $en_update['host_pre'] = RandCode(5, 12);
        }

        $start = $this->platforms->update($en_update, $rel['id']); // 启用开放平台
        if ($start) {
            // 启用公众号
            $wechat = $this->platformWechats->findBy('component_appid', $rel['appid']);
            $this->platformWechats->update(['status' => 1], $wechat['id']);
        }
        $stop = $this->platforms->update(['status' => 0, 'domain_status' => 0, 'wechat_num' => 0], $id); // 停用域名异常开放平台
        if ($stop) {
            // 停用开放平台对应的公众号
            $this->platformWechats->update(['status' => 0], $nowP['appid'], 'component_appid');
        }
        return 'start : ' . $start . '__stop : ' . $stop;
    }
    /**
     * 去除协议；只返回域名列表
     * @param array $list
     * @return Array
     */
    public function justHosts($list, $pre) {
        $rel = [];
        $now = $pre ? $pre : (date('His', time()) . '.');
        if (substr($now, -1) !== '.') {
            $now .= '.';
        }

        foreach ($list as $k => $v) {
            $url = isset($v['url']) ? $v['url'] : $v['host'];
            if (strpos($url, 'http') === 0) {
                $v2 = explode('://', $url);
                $v2 = $v2[1];
                $v2 = explode('/', $v2);
                $url = $v2[0];
            } else {
                $url = $now . $url;
            }
            $temp['id'] = $v['id'] . '';
            $temp['url'] = $url;
            $rel[] = $temp;
        }
        return $rel;
    }
    /**
     * 去除协议；只返回域名列表
     * @param array $list
     * @return Array
     */
    public function authHosts($list, $pre, $id) {
        $rel = [];
        $now = $pre ? $pre : (date('His', time()) . '.');
        if (substr($now, -1) !== '.') {
            $now .= '.';
        }

        foreach ($list as $v) {
            if (strpos($v, 'http') === 0) {
                $v2 = explode('://', $v);
                $v2 = $v2[1];
                $v2 = explode('/', $v2);
                $url = $v2[0];
            } else {
                $url = $now . $v;
            }
            $temp['id'] = $id . '';
            $temp['url'] = $url;
            $rel[] = $temp;
        }
        return $rel;
    }

    public function new_check(){
        $list =(new Domain())->where([
            ['status', 1],
        ])->orderBy('id', 'asc')->select(['id', 'host as url'])->get()->toArray();
        if($list){
            $check_url = [];
            foreach ($list as $key=>$url){
                $check_url[] = $url['url'];
            }
            $check_url_unique = array_unique($check_url);
            $data = $this->get_request_wx_new($check_url_unique);
            if($data){
                $keys = [];
                foreach ($data as $key=>$value){
                    foreach ($list as $kk=>$vv){
                        if($vv['url'] == $key){
                            $keys[] =  $vv['id'];
                        }
                    }
                }
                if($keys){
                    foreach ($keys as $k=>$v){
                        // 域名停用
                        $this->domains->update(['status'=>0], $v);
                        $info = $this->domains->find($v, ['app', 'type_id']);
                        $key = config('app.name') . 'domain_list_'.$info['type_id'].'_app_'. ($info['app'] ? $info['app'] : '0');
                        if (Cache::has($key))   Cache::forget($key);
                        $key = config('app.name') . 'domain_list_'.$info['type_id'];
                        if (Cache::has($key))   Cache::forget($key);
                        $key = config('app.name') . 'domain_list_'.$info['type_id'].'_'.($info['app'] ? $info['app'] : '0');
                        if (Cache::has($key))   Cache::forget($key);
                    }
                }
            }
            dd($data,$check_url);
        }
    }

    /*
     * 域名检测接口
     */
    private function get_request_wx_new($urlList){

        $chArr=[];
        $urlList= array_values($urlList);
        $result = [];
        foreach ($urlList as $key=>$url){

            $chArr[$key]=curl_init();
            curl_setopt($chArr[$key], CURLOPT_URL, "http://mp.weixinbridge.com/mp/wapredirect?url=http%3A%2F%2F".$url);
            curl_setopt($chArr[$key], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($chArr[$key], CURLOPT_HEADER, 0);
            curl_setopt($chArr[$key], CURLOPT_TIMEOUT, 3);
            curl_setopt($chArr[$key], CURLOPT_AUTOREFERER, 1);
            curl_setopt($chArr[$key], CURLOPT_SSL_VERIFYPEER,0);
            curl_setopt($chArr[$key], CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 7.0; MI 5s Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/64.0.3282.137 Mobile Safari/537.36 wxwork/2.4.16  NetType/WIFI Language/zh');
            curl_setopt($chArr[$key], CURLOPT_FOLLOWLOCATION, 0);

        }
        $mh = curl_multi_init();
        foreach($chArr as $k => $ch){
            curl_multi_add_handle($mh, $ch); //2 增加句柄
        }
        $active = null;

        do {
            while (($mrc = curl_multi_exec($mh, $active)) == CURLM_CALL_MULTI_PERFORM) ;

            if ($mrc != CURLM_OK) { break; }

            // a request was just completed -- find out which one
            while ($done = curl_multi_info_read($mh)) {
                // get the info and content returned on the request
                $info = curl_getinfo($done['handle']);

                $error = curl_error($done['handle']);

                if (strpos($info['redirect_url'],'weixin110.qq.com') !== false){
                    $re = ['code'=>200,'data'=>2,'msg'=>'域名已封杀'];
                    parse_str(parse_url($info['url'], PHP_URL_QUERY), $real_url);

                    if(!empty($real_url)){
                        $real_url = parse_url($real_url['url'], PHP_URL_HOST);

                        $result[$real_url] = $re;
                    }
                }

                curl_multi_remove_handle($mh, $done['handle']);
                curl_close($done['handle']);
            }
            // Block for data in / output; error handling is done by curl_multi_exec
            if ($active > 0) {
                curl_multi_select($mh);
            }

        } while ($active);
        return $result;
    }
}
