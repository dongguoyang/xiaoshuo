<?php
namespace App\Http\Controllers\App;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Log;
use App\Logics\Traits\CollectTrait;
use Illuminate\Http\Request;
use App\Logics\Services\src\UserService;
use App\Logics\Repositories\src\UserRepository;
/**
 * Created by PhpStorm.
 * User: Raytine
 * Date: 2020/8/6
 * Time: 18:24
 */
class CollectMpController extends BaseController{
    use CollectTrait;
    protected $cookie='aliyungf_tc11=AQAAAImUnCcIoQEA8VQSG8vSmLWIkRAx; __ZID=05eaf8be-3a66-4a15-a839-35b36db18182; __ZAID=173b1c6ce-9f96-6455-2326-e582-1596706242; Hm_lvt_d5e4918c33053db244a223b60906a4d3=1596706242; member_id=1078580151; Hm_lpvt_d5e4918c33053db244a223b60906a4d3=1596706993';  
    public function Rand_IP(){

        $ip2id= round(rand(600000, 2550000) / 10000); //第一种方法，直接生成
        $ip3id= round(rand(600000, 2550000) / 10000);
        $ip4id= round(rand(600000, 2550000) / 10000);
        //下面是第二种方法，在以下数据中随机抽取
        $arr_1 = array("218","218","66","66","218","218","60","60","202","204","66","66","66","59","61","60","222","221","66","59","60","60","66","218","218","62","63","64","66","66","122","211");
        $randarr= mt_rand(0,count($arr_1)-1);
        $ip1id = $arr_1[$randarr];
        return $ip1id.".".$ip2id.".".$ip3id.".".$ip4id;
    }

    public function getNonel(Request $request){
        $result = (new UserService())->PutTouInfo(2,38857);
        //$result1 = (new UserRepository())->SubscribeEvent(1, 1,1);// 修改用户的关注与否状态
        dd($result);
        $data = $request->session()->all();
        $cookie = '';
        $referer = 'https://c110246.818tu.com/m/read/12219310';
        $url = 'https://m.52bqg.net/book_84747';
        $result = $this->mp_get($url,$referer,$cookie);
        var_dump($result);
    }    

    function mp_get($url, $referer, $cookie) {
        $header = array();
        $header[] = 'Accept: image/gif, image/jpeg, image/pjpeg, image/pjpeg, application/x-ms-application, application/x-ms-xbap, application/vnd.ms-xpsdocument, application/xaml+xml, */*';
        $header[] = 'Connection: Keep-Alive';
        $header[] = 'Accept-Language: zh-cn';
        $header[] = 'Cache-Control: no-cache';
        $header[] = 'Authorization:Bearer bde4e5a15fa947a6a1919d9c8c495ba7';
        //$header[] = 'X-FORWARDED-FOR:'.$this->Rand_IP();
        //$header[] = 'CLIENT-IP:'.$this->Rand_IP();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}