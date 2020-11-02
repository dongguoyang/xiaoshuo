<?php

namespace App\Console\Commands;

use App\Logics\Traits\RedisCacheTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OSS\Core\OssException;

class WechatCollect extends Command
{
    use RedisCacheTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'WechatCollect:work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        // 1089篇小说
        //登录信息
        $token='oQCsjwTZY29w0vsS0eJdmrr09oPo';
        $referer="http://t12683249.kesbal.cn/fiction/1713/915323?lkk=33";
        $cookie='PHPSESSID=s6ada04p30rp9uencgtu6q6916';
        //
        $type_3url="http://t12683249.kesbal.cn/api/fictionList?pageSize=15&type=0&status=-1&sex=-1"; //都市 只有男友
        $type14b_url="http://t12683249.kesbal.cn/api/fictionList?pageSize=10&type=1&status=-1&sex=-1"; //历史穿越
        $type11g_url="http://t12683249.kesbal.cn/api/fictionList?pageSize=10&type=2&status=-1&sex=-1";  //总裁豪门
        $type10b_url="http://t12683249.kesbal.cn/api/fictionList?pageSize=10&type=3&status=-1&sex=0"; //其他男
        $type18g_url="http://t12683249.kesbal.cn/api/fictionList?pageSize=10&type=3&status=-1&sex=1"; //其他女
        $type4a_url="http://t12683249.kesbal.cn/api/fictionList?pageSize=10&type=4&status=-1&sex=-1"; //灵异
        $type3a_url="http://t12683249.kesbal.cn/api/fictionList?pageSize=10&type=5&status=-1&sex=-1"; //都市爽文
        $type6b_url="http://t12683249.kesbal.cn/api/fictionList?pageSize=10&type=6&status=-1&sex=-1";//仙侠男

        $dtype=[
             '3'=>$type3a_url,
             '3.1'=>$type_3url,
             '4'=>$type4a_url,
            '6'=>$type6b_url,
            '10'=>$type10b_url,
            '11'=>$type11g_url,
            '14'=>$type14b_url,
            '18'=>$type18g_url,


        ];
        foreach ($dtype as  $dtype_key=>$dtype_v){
            for($i=1;$i<=10;$i++) { //每个系列15x10章
                $pageUrl=$dtype_v."&pageNumber=".$i;
                while (true) {
                    $allData = $this->get($pageUrl, $referer, $cookie, $token);
                    if ($allData) {
                        break;
                    } else {
                        sleep(2);
                    }
                }
                if ($allData) {
                    $allData = \GuzzleHttp\json_decode($allData, 1);
                    // $allNum=$allData['totalFiction']; //总num  每页最大为15
                    $resource = $allData['content']; //获取内容相关
                    foreach ($resource as $resource_v) {
                        $title = $resource_v['title'];
                        $desc = $resource_v['desc'];
                        $img = @file_get_contents($resource_v['img']);
                        $img_name = time() . mt_rand(1000, 9999999) . '.jpg';
                        $imgurl = $this->uploadOss($img_name, $img);
                        //http://t12683249.kesbal.cn/api/FictionDetails?fictionid=996
                        $remaskUrl = 'http://t12683249.kesbal.cn/api/FictionDetails?fictionid=' . $resource_v['id'];
                        $resmk = $this->get($remaskUrl, $referer, $cookie, $token);
                        if (!$resmk) {
                            $this->error_msg(0, $remaskUrl . '--不让进入');
                            continue;
                        }
                        $resmk = \GuzzleHttp\json_decode($resmk, 1);
                        if ($resmk['statusCode'] == 200) {
                            $resmkData = $resmk['content']['tabList'];
                        } else {
                            $this->error_msg(1, $remaskUrl . '--接口频率被限');
                            continue;
                        }
                        $rs = DB::table('novels')->where('title', $title)->where('author_name', '公众号1')->get();
                        if (!$rs) {
                            //已经存在该小说
                            continue;
                        }
                        $insertNovel = [
                            'title' => $title,
                            'img' => $imgurl,
                            'author_id' => 197,
                            'author_name' => '公众号1',
                            'serial_status' => 1,
                            'word_count' => 130,
                            'suitable_sex' => 1,
                            'type_ids' => (int)floor($dtype_key),
                            'tags' => '',
                            'desc' => $desc,
                            'sections' => count($resmkData),
                            'status' => 1,
                            'created_at' => time(),
                            'updated_at' => time()
                        ];
                        $novel_id = DB::table('novels')->insertGetId($insertNovel);
                        $muteUrl = [];
                        $i = 1; //章节 一般无误
                        // $url='http://t12683249.kesbal.cn/api/fictionDetailsText?fictionid='.$resource_v['id'].'&fictionPage='.$resmkDatum_v['id'].'&lkk=33';
                        foreach ($resmkData as $resmkData_v) {
                            $muteUrl[$i] = 'http://t12683249.kesbal.cn/api/fictionDetailsText?fictionid=' . $resource_v['id'] . '&fictionPage=' . $resmkData_v['id'] . '&lkk=33';
                            $i++;
                        }

                        $muteUrl = $this->sliceArr($muteUrl, 10);
                        //$testData=[$muteUrl[0]];

                        $dx = [];
                        foreach ($muteUrl as $muteUrl_v) {
                            $this->lock(); //multe_curl加锁
                            $dx[] = $this->multe_curl($muteUrl_v, $token, $cookie, $referer);  //获取数据
                            $this->delock();
                        }
                        unset($muteUrl_v);
                        foreach ($dx as $dx_v) {
                            if (isset($dx_v['needmoney'])) {
                                $this->needmoney($dx_v['needmoney'], $novel_id);
                            }
                            if (isset($dx_v['right'])) { //获取到的数据
                                $this->creeData($dx_v['right'], $resmkData, $novel_id);
                            }
                            if (isset($dx_v['error'])) {  //请求不到的数据  //返回的是header[$k]
                                //['章节'=>'header信息']
                                $notData = $this->CountReplay($dx_v['error'], $novel_id, $token, $cookie, $referer, $resmkData);
                                if (count($notData)) {
                                    $this->hasError($notData, $novel_id);
                                }
                            }

                        }
                        unset($dx_v);
                        $this->info('ojbk');
                        #$allData=$this->multe_curl($muteUrl);
                    }
                }

            }
        }




    }
    public function error_msg($code,$msg){
        $das = [
            'code' => $code,
            'msg' => $msg,
            'created_at' => Carbon::now()->timestamp , 'updated_at' => Carbon::now()->timestamp
        ];
        DB::table('reg_error_msg')->insert($das); //正则匹配错误
    }


    public function CountReplay($data,$novel_id,$token,$cookie,$referer,$resmkData,$count=3){  //对error重复进行
        $i=1;
        while($i<=$count) {
            $dx = [];
            foreach ($data as $data_key => $data_v) {
                $dx[$data_key] = $data_v['url'];
            }
            $muteUrl = $this->sliceArr($dx, 10);
            foreach ($muteUrl as $muteUrl_v) {
                $this->lock(); //multe_curl加锁
                $dx[] = $this->multe_curl($muteUrl_v, $token, $cookie, $referer);  //获取数据
                $this->delock();
            }
            unset($muteUrl_v);
            $data_A = [];
            $data = [];
            foreach ($dx as $dx_v) {
                if (isset($dx_v['needmoney'])) {
                    //[{"statusCode":303,"message":"need_charge","url":"http:\/\/t12683249.kesbal.cn\/recharge?token=oQCsjwTZY29w0vsS0eJdmrr09oPo"},]
                    $this->needmoney($dx_v['needmoney'], $novel_id);
                }
                if (isset($dx_v['right'])) { //获取到的数据
                    $this->creeData($dx_v['right'], $resmkData, $novel_id);
                }
                if (isset($dx_v['error'])) {  //请求不到的数据  //返回的是header[$k]
                    //['章节'=>'header信息']
                    // $this->hasError($dx_v['error'],$novel_id);
                    $data_A[] = $dx_v['error'];
                }
            }
            unset($dx_v);
            foreach ($data_A as $data_A_v) {
                foreach ($data_A_v as $data_A_v_key => $data_A_v_v) {
                    $data[$data_A_v_key] = $data_A_v_v;
                }
                unset($data_A_v_key);
                unset($data_A_v_v);
            }
            unset($data_A_v);
            $i++;
            sleep(2);
        }
        return $data;

    }
    public function needmoney($data,$novel_id){ //要钱的 暂时不会发生
       //  dd($data); //
    }
    public function hasError($data,$novel_id){
        foreach ($data as $data_key=>$data_v){
             $dx=[
                 'num'=>$data_key,
                 'url'=>$data_v['url'],
                 'novel_id'=>$novel_id,
                 'created_at'=>time(),
                 'updated_at'=>time(),
                 'reg_id'=>'119' //公众号1
             ];
             $this->insertNullData($dx);
        }unset($data_key);unset($data_v);
    }

    public function insertNullData(array $data){
        $res=DB::table('reg_null_data')->insert($data);
        return $res;
    }


    public function creeData($data,$numData,$novel_id){ //data下标 1开始 numdata 下标0开始
        foreach ($data as $data_key=>$data_v){
            $title=$numData[$data_key-1]['title'];
            $name=time().mt_rand(1000,50000).'.html';
            $content_url=$this->uploadOss($name,$data_v);
            $ds=[
                'novel_id'=>$novel_id,
                'num'=>$data_key,
                'title'=>$title,
                'content'=>$content_url,
                'updated_at'=>time(),
                'created_at'=>time()
            ];
            DB::table('novel_sections')->insert($ds);
        }
    }


    public  function get($url, $referer, $cookie,$token) {
        $header = array();
        $header[] = 'Accept: image/gif, image/jpeg, image/pjpeg, image/pjpeg, application/x-ms-application, application/x-ms-xbap, application/vnd.ms-xpsdocument, application/xaml+xml, */*';
        $header[] = 'Connection: Keep-Alive';
        $header[] = 'Accept-Language: zh-cn';
        $header[] = 'Cache-Control: no-cache';
        $header[]= 'token:'.$token;
        $header[]='Host:t12683249.kesbal.cn';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 5_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Mobile/9B176 MicroMessenger/4.3.2');
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpCode==200){
            return $result;
        }else{
            return false;
        }

    }
    public function uploadOss($name,$novalContentHtml){
        try {
            Storage::disk('oss')->put($name, $novalContentHtml);
            $url = Storage::disk('oss')->url($name);
        }catch (OssException $e){
            $url='';
            $das = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'created_at' => Carbon::now()->timestamp , 'updated_at' => Carbon::now()->timestamp
            ];
            DB::table('reg_error_msg')->insert($das); //正则匹配错误
        }catch (\Exception $e){
            $url='';
            $das = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'created_at' => Carbon::now()->timestamp , 'updated_at' => Carbon::now()->timestamp
            ];
            DB::table('reg_error_msg')->insert($das); //正则匹配错误
        }
        return $url;
    }

    /**多进程curl函数
     * @param array $connomains
     * @return array
     */
    public function multe_curl(array $connomains,$token,$cookie,$referer){
        $res = array();
        $mh = curl_multi_init();//创建多个curl语柄
        $conn=array();
        $header = array();
        $header[] = 'Accept: image/gif, image/jpeg, image/pjpeg, image/pjpeg, application/x-ms-application, application/x-ms-xbap, application/vnd.ms-xpsdocument, application/xaml+xml, */*';
        $header[] = 'Connection: Keep-Alive';
        $header[] = 'Accept-Language: zh-cn';
        $header[] = 'Cache-Control: no-cache';
        $header[]= 'token:'.$token;
        $header[]='Host:t12683249.kesbal.cn';
        foreach($connomains as $k=>$url){
            $conn[$k]=curl_init($url);
            curl_setopt($conn[$k], CURLOPT_TIMEOUT, 10);//设置超时时间
            curl_setopt($conn[$k], CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 5_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Mobile/9B176 MicroMessenger/4.3.2');
            if (strpos($url, 'https') === false) {
                curl_setopt($conn[$k],CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt($conn[$k],CURLOPT_SSL_VERIFYHOST,false);
            }
            curl_setopt($conn[$k], CURLOPT_HTTPHEADER, $header);
            curl_setopt($conn[$k], CURLOPT_COOKIE, $cookie);
            curl_setopt($conn[$k], CURLOPT_REFERER, $referer);
            curl_setopt($conn[$k], CURLOPT_MAXREDIRS, 7);//HTTp定向级别
            curl_setopt($conn[$k], CURLOPT_HEADER, 0);//这里不要header，加块效率
            curl_setopt($conn[$k], CURLOPT_FOLLOWLOCATION, 1); // 302 redirect
            curl_setopt($conn[$k],CURLOPT_RETURNTRANSFER,1);
            curl_multi_add_handle ($mh,$conn[$k]);
        }

        // 执行批处理句柄
        $active = null;
        do{
            $mrc = curl_multi_exec($mh,$active);//当无数据，active=true
        }while($mrc == CURLM_CALL_MULTI_PERFORM);//当正在接受数据时
        while($active && $mrc == CURLM_OK){//当无数据时或请求暂停时，active=true
//        if(curl_multi_select($mh) != -1){
            do{
                $mrc = curl_multi_exec($mh, $active);
            }while($mrc == CURLM_CALL_MULTI_PERFORM);
//        }
        }

        foreach ($connomains as $k => $url) {
            curl_error($conn[$k]);
            $header[$k]=curl_getinfo($conn[$k]);//返回头信息
            if($header[$k]['http_code'] == 200){
                //$res['right'][$k]=curl_multi_getcontent($conn[$k]);//获得返回信息
                $dat=curl_multi_getcontent($conn[$k]);
                $data=\GuzzleHttp\json_decode($dat,1);
                if($data['statusCode'] == 200){
                    $res['right'][$k]=$data['content']['detail'];
                }else{ //需要钱的
                    $res['needmoney'][$k]=$dat;
                }
            }else{
                $res['error'][$k]=$header[$k];
            }
            curl_multi_remove_handle($mh  , $conn[$k]);//释放资源
            curl_close($conn[$k]);//关闭语柄
        }unset($k);unset($url);

        curl_multi_close($mh);
        return $res;

    }
    public function  sliceArr($arr, $num)
    {
        $listcount = count($arr);
        $lastarr=$listcount%$num;
        $arring=$listcount-$lastarr;
        $xsk=$arring/$num;
        $data=[];
        for($xms=1;$xms<=$xsk;$xms++){
            $data[]=array_slice($arr,($xms-1)*$num,$num,true);
        }
        $data[]=array_slice($arr,($xms-1)*$num,$num,true);

        return $data;
    }
    public function lock(){
        $lockName='multe_curl'; //因为只能同时运行一个mutel curl
        while (true){
            $lock=$this->setLock($lockName);
            if(!$lock) { //表示已经在锁当中
                sleep(10); //等10秒
                continue;
            }else{
                break;
            }
        }
    }

    /**
     * 解锁mutel_curl
     */
    public function delock(){
        $lockName='multe_curl'; //因为只能同时运行一个mutel curl
        $this->delLock($lockName);
    }



}
