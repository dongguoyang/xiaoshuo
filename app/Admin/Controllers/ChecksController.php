<?php

namespace App\Admin\Controllers;

use App\Admin\Models\CommonSet;
use App\Admin\Models\Customer;
use App\Admin\Models\Domain;
use App\Admin\Models\DomainType;
use App\Admin\Models\Platform;
use App\Admin\Models\PlatWechat;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Platform\DomainCheckController;
use App\Libraries\Facades\Tools;
use App\Logics\Services\src\DomainService;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;

class ChecksController extends Controller {
    use ModelForm;


    public $url = 'http://admin.9lequ.com/checkApi/';

    /**
     * Index interface.
     *
     * @return Content
     */
    public function checkPage() {
        return Admin::content(function (Content $content) {
            $plan = request()->input('plan');
            $customers = Domain::distinct()->select(['app'])->get()->pluck('app');
            $customers = Customer::whereIn('id', $customers)->select(['id', 'name'])->get()->toArray();
            $domaintypes = DomainType::where('status', 1)->select(['id', 'name'])->get()->toArray();

            $content->body(view('admin.check.checkpage', compact('plan', 'customers', 'domaintypes')));
        });
    }

    public function domainNums() {
        $domainCheck = new DomainCheckController(new DomainService());
        $domainCheck->insertCheckDomain(); // 发送域名到我们服务器
        
        $customer = request()->input('customer');
        $types    = request()->input('types');

        foreach ($customer as $cid) {
            foreach ($types as $tid) {
                $rel[$cid][$tid]['n'] = Domain::where('status', 1)->where('type_id', $tid)->where('app', $cid)->count();
                $rel[$cid][$tid]['u'] = Domain::where('status', 0)->where('type_id', $tid)->where('app', $cid)->count();
            }
        }
        return $rel;
    }

    /**
     * 域名 公众号 正常数量
     */
    public function normalInfo() {
        $wechat = $this->wechatList('string');
        $rel['wechat'] = $wechat;
        $domains = $this->domainInfo();
        $rel['domain'] = $domains;
        return $rel;
    }
    /**
     * 公众号或者开放平台正常信息
     */
    private function domainInfo() {
        $list = Domain::where('status', 1)->select(['app', 'host', 'type_id'])->get();
        foreach ($list as $v) {
            $rel[$v['app']] = isset($rel[$v['app']]) ? ($rel[$v['app']]+1) : 1;
        }
        return $rel;
    }
    /**
     * 公众号或者开放平台正常信息
     */
    private function wechatList($type = 'string') {
        $plat = Platform::where('status', 1)->get();

        if ($type == 'string') {
            $rel = '';
        } else {
            $rel = [];
        }

        foreach ($plat as $v) {
            if ($type == 'string') {
                $rel .= $v['title'].' | ';
            } else {
                $temp['name'] = $v['title'];
                $temp['id'] = $v['id'];
                $rel[] = $temp;
            }
        }
        if ($type == 'string') {
            $rel .= count($plat).' 个正常';
        }


        return $rel;
    }
    /**
     * 检测域名解析备案信息
     */
    public function checkJiexiBeian() {
        set_time_limit(0);
        $results = [];
        $domain = request()->input('hostlist');

        $domain = explode("\n", $domain);
        $jiexi_ip = request()->input('jiexi_ip');
        $inend = "check";
        $hosts[$inend] = [];
        foreach ($domain as $v) {
            $v = trim($v);
            if (!$v) {
                continue;
            }
            $hosts[$inend][] = $v;
        }
        $beian = request()->input('beian');
        foreach ($hosts as $lk => $list) {
            $this->doCurl($results, $list, $lk, $beian);
        }

        return $results;
    }
    /**
     * 生成域名列表
     */
    public function proDomain() {
        set_time_limit(0);
        date_default_timezone_set('Asia/Shanghai');

        $dir = 'domains/';
        $proded = '';

        $d = intval(date('d'));
        if ($d > 25) {
            $d = $d - 25;
        }
        $allfile = $dir . date('Ym') . $d . 'all.txt';
        if (is_file($allfile)) {
            $proded = file_get_contents($allfile);
        }

        $start = 97;
        $m = chr($start + intval(date('m')));
        $d = chr($start + $d);
        if ($d > 25) {
            $d = $d . $d;
        }

        $i = 0;
        $needNum = isset($_GET['num']) ? $_GET['num'] : 100;
        $pre = isset($_GET['pre']) ? $_GET['pre'] : '';
        $type = isset($_GET['type']) ? $_GET['type'] : '.cn';
        if (substr($type, 0, 1) != '.') {
            $type = '.' . $type;
        }

        $list = [];
        while ($i < $needNum) {
            $tem = $m . Tools::RandCode(2, 2) . $d . Tools::RandCode(3, 2) . $type;
            if ($pre) {
                $tem = Tools::RandCode(1, 2) . $pre . $tem;
            }
            if (in_array($tem, $list) || strpos($proded, $tem) !== false) {
                continue;
            }
            $i++;
            $list[] = $tem;
        }

        $fp = fopen($dir . date('Y-m-d_H') . '_' . $needNum . 'domain.txt', 'w+');
        $dostr = implode("\r\n", $list);
        $dostr .= "\r\n";

        fwrite($fp, $dostr);
        fclose($fp);

        $fp = fopen($allfile, 'a+');
        fwrite($fp, $dostr);
        fclose($fp);

        return $dostr;
    }
    /**
     * 获取不重复域名进行点击
     * 公众号js安全域名或者开放平台域名
     */
    public function norepeatHost() {
        //$wechat = $this->othW->where('num', '<', 3)->where('domain', 0)->get()->toArray();
        $wechat = Platform::where('status', 1)->where('domain_status', 1)->select('auth_domain')->get()->toArray();
        $hosts = [];
        foreach ($wechat as $k => $v) {
            $tem = Tools::ExplodeStr($v['auth_domain']);
            foreach ($tem as $tv) {
                $tv = trim($tv);
                $hosts[$tv] = $tv;
            }

            //$v['luodi'] = trim($v['luodi']);
            //$hosts[$v['luodi']] = $v['luodi'];
        }
        $str = implode("\r\n", $hosts);
        return $str;
    }
    /**
     * 带登录信息 获取数据信息
     */
    public function baiduTongji() {
        $strCookie = request()->input('Cookie') . '; path=/';
        $url = request()->input('baiduurl');
        $data = request()->all();
        $option = [];
        $option[CURLOPT_POST] = 1;
        $option[CURLOPT_COOKIE] = $strCookie;
        $option[CURLOPT_POSTFIELDS] = $data;
        $option[CURLOPT_URL] = $url; //请求url地址
        $option[CURLOPT_FOLLOWLOCATION] = TRUE; //是否重定向
        $option[CURLOPT_MAXREDIRS] = 4; //最大重定向次数
        $option[CURLOPT_RETURNTRANSFER] = 1; //是否将结果作为字符串返回，而不是直接输出
        $option[CURLOPT_TIMEOUT] = 15;
        $option[CURLOPT_USERAGENT] = request()->input('User-Agent');
        $option[CURLOPT_REFERER] = request()->input('Referer');
        $option[CURLOPT_SSL_VERIFYPEER] = 0;
        $ch = curl_init();
        curl_setopt_array($ch, $option);
        $rel = curl_exec($ch);
        $curl_no = curl_errno($ch);
        $curl_err = curl_error($ch);
        curl_close($ch);
        // dd($rel);
        return $rel;
    }




    /**
     * 执行检测
     */
    private function doCurl(&$results, $list, $lk, $beian = 1) {
        $reruls = $checks = [];
        if (!isset($results[$lk])) {
            $results[$lk] = [];
        }
        $ips = ['in' => request()->input('inip'), 'end' => request()->input('endip'), 'ewm' => request()->input('ewmip')];
        $ips['justend'] = $ips['end'];
        $ips['check'] = request()->input('jiexi_ip');
        foreach ($list as $k => $v) {
            // 检测域名解析 ip
            $ip = gethostbyname($v);
            if ($ips[$lk] != $ip) {
                $rv['msg'] = '解析ip=' . $ip . ' 异常';
                $rv['url'] = $v;
                $results[$lk][$k] = $rv;
                continue;
            }
            // 检测备案信息
            if ($beian == 1 && $k > 0 && $k % 50 == 0) {
                $rel = $this->multiCurl($checks);
                // $results[$lk] = array_merge($results[$lk], $rel);
                foreach ($rel as $rk => $rv) {
                    if ($rv['err']) {
                        $reruls[$rk]['url'] = $rv['url'];
                    }
                    if (strpos($rv['data'], '<title>TestPage184</title>')) {
                        $rv['msg'] = '未备案';
                        $results[$lk][$rk] = $rv;
                    }
                }
                $checks = [];
            }
            $checks[$k]['url'] = $v;
        }
        // 检测最后不足 50 条的备案信息
        if ($beian == 1) {
            $rel = $this->multiCurl($checks);
            foreach ($rel as $rk => $rv) {
                if ($rv['err']) {
                    $reruls[$rk]['url'] = $rv['url'];
                }
                if (strpos($rv['data'], '<title>TestPage184</title>')) {
                    $rv['msg'] = '未备案';
                    $results[$lk][$rk] = $rv;
                }
            }
        }
        // $results[$lk] = array_merge($results[$lk], $rel);

        if ($beian == 1 && $reruls) {
            $rel = $this->multiCurl($reruls);
            $reruls = [];
            foreach ($rel as $rk => $rv) {
                if ($rv['err']) {
                    $rv['msg'] = '检测失败';
                    $results[$lk][$rk] = $rv;
                }
                if (strpos($rv['data'], '<title>TestPage184</title>')) {
                    $rv['msg'] = '未备案';
                    $results[$lk][$rk] = $rv;
                }
            }
        }
        // $results[$lk] = array_merge($results[$lk], $rel);
    }

    /**
     * 执行curl请求
     */
    private function singleCurl($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //将curl_exec()获取的信息以字符串返回，而不是直接输出。
        //执行curl操作
        $res['data'] = curl_exec($ch);
        $res['err'] = curl_errno($ch);

        curl_close($ch);
        return $res;
    }

    /**
     * 并行执行curl
     */
    private function multiCurl($res, $options = "") {
        if (count($res) <= 0) {
            return [];
        }
        $handles = array();
        if (!$options) // add default options
        {
            $options = array(
                // CURLOPT_HEADER => 1,
                // CURLOPT_NOBODY => 1,
                CURLOPT_RETURNTRANSFER => 1,
            );
        }
        // add curl options to each handle
        foreach ($res as $k => $row) {
            //$ch{$k} = curl_init();
            $ch[$k] = curl_init();
            $options[CURLOPT_URL] = $row['url'];
            $opt = curl_setopt_array($ch[$k], $options);
            // var_dump($opt);
            $handles[$k] = $ch[$k];
        }

        $mh = curl_multi_init();
        // add handles
        foreach ($handles as $k => $handle) {
            $err = curl_multi_add_handle($mh, $handle);
        }
        $running_handles = null;
        do {
            curl_multi_exec($mh, $running_handles);
            curl_multi_select($mh);
        } while ($running_handles > 0);

        foreach ($res as $k => $row) {
            $res[$k]['err'] = curl_error($handles[$k]);
            if (!empty($res[$k]['err'])) {
                $res[$k]['data'] = '';
            } else {
                $res[$k]['data'] = curl_multi_getcontent($handles[$k]);
            }
            // get results

            // close current handler
            curl_multi_remove_handle($mh, $handles[$k]);
        }
        curl_multi_close($mh);
        return $res; // return response
    }
}