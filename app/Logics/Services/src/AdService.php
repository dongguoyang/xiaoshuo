<?php

namespace App\Logics\Services\src;

use App\Libraries\Facades\Tools;
use App\Logics\Repositories\src\AdRepository;
use App\Logics\Repositories\src\CommonSetRepository;
use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\DomainTypeRepository;
use App\Logics\Services\Service;
use Illuminate\Support\Facades\Cache;

class AdService extends Service {
	public function Repositories() {
		return [
			'domain'    => DomainRepository::class,
			'domainType'=> DomainTypeRepository::class,
			'commonSet' => CommonSetRepository::class,
            'ad'        => AdRepository::class,
		];
	}

    public function Pageapi() {
        //随机广告
        $re = request()->input('re', 0);  // 第几反

        $now_id = request()->input('aid', 0);
        if (!$now_id) {
            $now_id = request()->input('id', 0);
        }
        $id = $this->ad->RandId($now_id, $re);

        $url = '';
        $refer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if ($refer)
        {
            $hosts = parse_url($refer);
            if (!$this->domain->typeDomain(2, $hosts['host']))
            {
                $url = 'http://' . $hosts['host'] . $hosts['path'] . '?id=' . $id . '&re=' . (++$re);
            }
        }
        if ($url == ''){
            //获取域名
            $host = $this->domain->randOne(2);
            $url = $host . route('ad.toshow', ['id'=>$id, 're'=>$re], false);
        }

        return $this->result(['url' => $url]);
    }

    // 广告中转页
    public function ToShow(){
        $this->rel404(); // 不是微信和移动端返回404
        $id = request()->input('id');
        $id = $id>0 ? $id : request()->input('aid');
        if (!$id) {
            $now_id = request()->input('now_id');
            $id = $this->ad->RandId($now_id);
        }

        $host = $this->domain->randOne(2);

        $re = request()->input('re', 0);
        $re++;

        $rel['url'] = $host . route('ad.show', ['id'=>$id, 're'=>$re], false);
        $rel['time'] = time();
        return $rel;
    }
    // 广告落地页
    public function Show(){
        $this->rel404(); // 不是微信和移动端返回404
        $id = request()->input('id');
        $id = $id>0 ? $id : request()->input('aid');
        if (!$id)
        {
            throw new \Exception('参数异常-id', 2000);
        }

        $info = $this->ad->Ad2TplInfo($id);
        if (!$info)
        {
            throw new \Exception('广告异常了！！！', 2000);
        }
        $info = $info['AdTpl'];
        if (!$info)
        {
            throw new \Exception('广告模板异常了！！！', 2000);
        }
        $info = $this->resetTplInfo($info);
        if (strpos($info['tpl'], 'qrcode') !== false){
            $info['backto'] = $this->commonSet->values('qrcode_backto', $info['tpl']);
        }

        return $info;
    }
    /**
     * 广告页的其他广告
     */
    public function ShowPageAd(){
        $re = request()->input('re', 0);
        if ($re < 1) return [];

        $re = $re > 3 ? 3 : $re;

        $key = config('app.name') . 'ad_page_ads_egt'.$re;

        return Cache::remember($key, 1440, function () use ($re) {
            $confs = $this->commonSet->values('adpage_ad');
            $conf2 = [];
            foreach ($confs as $k=>$v) {
                if ($v <= $re) {
                    $conf2[] = $k;
                }
            }
            $ads = $this->ad->AdPageAds($conf2);
            $rel = [];
            foreach ($ads as $v) {
                $rel[$v['remark']] = $v;
            }
            return $rel;
        });
    }
    // 获取一条广告链接接口
    public function ALink()
    {
        $origin = request()->input('origin');
        if (!$origin)
        {
            throw new \Exception('origin 参数缺失！！！', 2000);
        }

        $conf = $this->commonSet->values('ad_alink', $origin);

        if (!$conf)
        {
            throw new \Exception('origin 参数异常！！！', 2000);
        }


        $type = request()->input('type');

        /*if ($type == 'qrcode') {
            // 吸粉二维码页面
            $url = $this->domain->randOne(3) . route('ad.qrcode', [], false);
        } else {
            $url = $this->linkRel($origin, $type);
        }*/
        $url = $this->linkRel($origin, $conf, $type);

        $rel = ['url' => $url];

        return $this->result($rel);
    }
    // 广告地址
    private function linkRel($origin, $conf, $type)
    {
        $ad_no = $this->commonSet->values('ad_alink', $origin.'_rand');
        $ad_no = is_numeric($ad_no) ? intval($ad_no) : -1;

        $adlist = $this->ad->ALinkAdList($origin, $conf);

        // 获取广告页面链接地址
        if ($type && isset($adlist[$type])) {
            $list = $adlist[$type];
        } else {
            $list = [];
            foreach ($adlist as $k => $v) {
                // 随机获取的时候不取吸粉二维码页面
                if ($k == 'qrcode') continue;
                $list = array_merge($list, $v);
            }
        }

        if (!$list) {
            $url = '';
        } else {
            if ($ad_no < 0) {
                $url = $list[array_rand($list)];
            } else {
                $url = $list[$ad_no];
            }
            if (is_numeric($url)) {
                $host = $this->domain->randOne(1);
                $url = $host . route('ad.toshow', ['id'=>$url], false);
            }
        }

        return $url;
    }

    // 重组广告模板内容数据
    private function resetTplInfo($info)
    {
        $oss_host = Tools::cloudPreDomain();
        if ($info['content_tip']) {
            $info['content_tip'] = json_decode($info['content_tip'], 1);
        }
        $imgs = json_decode($info['content'], 1); // 所有图片信息
        // 将模板内容规整好
        $contents = [];
        if ($info['content_rule']) {
            $contents = json_decode($info['content_rule'], 1); // 图片显示规则定义
            foreach ($contents as $k => $v) {
                $contents[$k] = $oss_host . $imgs[$v - 1];
            }
        } else {
            foreach ($imgs as $k => $v) {
                $contents['img' . ($k + 1)] = $oss_host . $v;
            }
        }
        //$contents['default'] = $oss_host . $imgs[0];
        $info['content_img'] = $contents;
        return $info;
    }
    // 返回 404
    public function rel404(){
//        return true;
        if (!Tools::IsMobile() || !Tools::IsWechat()) {
            header('HTTP/1.1 404 Not Found');
            header("status: 404 Not Found");
            die(); exit('页面走丢了！');
        }
    }
}