<?php

namespace App\Console\Commands;

use App\Logics\Traits\RedisCacheTrait;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContinueError extends Command
{
    use RedisCacheTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ContinueError:work';

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
        //
        $rs= DB::table('reg_null_data')->get()->groupBy('novel_id');
        $rs=\GuzzleHttp\json_encode($rs);
        $rs=\GuzzleHttp\json_decode($rs,1);
        foreach ($rs as $keys=> $vs){
            $data=DB::table('reg_null_data')->where('novel_id',$keys)->get()->toarray();
            if(count($data) ==0 ){
                continue;
            }
            $regid=$data[0]->reg_id;
            $commonset = DB::table('reg')->where('status', 1)->where('id', $regid)->first();
            if (!$commonset) {
                exit('请设置了在进行！');
            }
            $data=\GuzzleHttp\json_encode($data);
            $data=\GuzzleHttp\json_decode($data,1);
            $dsj=[]; //['id'=>'url']
            foreach ($data as $v){
                $dsj[$v['id']]=$v['url'];
            }unset($v);
            $dsj=$this->sliceArr($dsj,5);
            $old_cho=[];
            foreach ($dsj as $dsj_v){
                $this->lock();
                $old_cho[]=$this->multe_curl($dsj_v); //['id'=>'内容']
                $this->delock();
            }
            $cho=[];
            foreach ($old_cho as $old_cho_key=>$old_cho_v){
                 foreach ($old_cho_v as $old_cho_v_key=>$old_cho_v_v){
                     if($old_cho_v_key=="error"){
                         foreach ($old_cho_v_v as $old_cho_v_v_key=>$old_cho_v_v_v){
                             $cho['error'][$old_cho_v_v_key]=$old_cho_v_v_v;
                         }

                     }
                     if($old_cho_v_key=="right"){
                         foreach ($old_cho_v_v as $old_cho_v_v_key=>$old_cho_v_v_v){
                             $cho['right'][$old_cho_v_v_key]=$old_cho_v_v_v;
                         }
                     }
                 }unset($old_cho_v_key);unset($old_cho_v_v);
            }unset($old_cho_key);
            if(isset($cho['right'])){ //表示返回码为200
                $null_ids=[];
                foreach ($cho['right'] as $cho_right_key=>$cho_right_v){
                     $null_ids[]=$cho_right_key;
                }unset($cho_right_key);unset($cho_right_v);
                $dwt=$this->creeData($keys,$commonset,$cho['right']); //返回的没取到null_data的ID ['id'=>'id']
                if(count($dwt)==0){ //表示已经补充完整
                   # DB::table('reg_null_data')->whereIn('novel_id',$keys)->delete();
                     DB::table('reg_null_data')->whereIn('id',$null_ids)->delete();
                }else{  //表示还有剩下
                    $notID=[];
                    foreach ($dwt as $vt){
                        $notID[]=$vt;
                    }unset($vt);
                    DB::table('reg_null_data')->whereNotIn('id',$notID)->where('novel_id',$keys)->delete();
                }
            }
            $this->info('小说ID为:'.$keys.'的重新获取成功');
            sleep(1);
        }
        unset($keys);
        unset($vs);
    }

    /** mutel_curl进入时截取50个数组进入
     * @param $arr
     * @param $num
     * @return array
     */
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
    /**
     * @param $re
     * @param $commonset
     * @param array $allData  ['id' => '类容']  null_data表的id
     * @param int $count
     * @return array
     */
    private function creeData($re,$commonset,array $allData,$count=0){
        $notFound=array();//没有找到的数据
        $time = Carbon::now()->timestamp;
        foreach ($allData as $key=> $bs) { //key表示ID  bs表示内容
            try {
                $bs = mb_convert_encoding($bs, 'utf-8', 'GB2312');
                $novalContentHtml = $this->notHtml($bs);
                $reg = $commonset->reg1_content_title;
                $titlearr = $this->reg($reg, $bs); //获取章节名称

                if(!$titlearr){ //不规则
                    $notFound[$key]=$key;
                    logger($re.'的小说第'.$key.'章节不规则'); //经常是因为报500之类的服务器错误
                    continue;
                }
                if(!isset($titlearr[0][1])){
                    $titlearr[0][1]='未知章节';
                }


                $title = $titlearr[0][1];

                $reg = $commonset->reg1_content_num;
                $numarr = $this->reg($reg, $bs);
                if(!$numarr){
                    //exit('内容章节正则表达错误');
                }
                if(!isset($numarr[0][1])){
                    $numarr[0][1]=00; //默认值为**
                }
                $num = $this->chrtonum($numarr[0][1]); //章数

                //传至oss
                //$name=$re.'号章节为('.$num.')';
                $name = md5($re . '|' . $num);
                $name = $name . '.html';
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

                $dlk = [
                    'novel_id' => $re,
                    'num' => $num,
                    'title' => $title,
                    'updated_num' => 0,
                    'created_at' => $time,
                    'updated_at' => $time,
                    'content' => $url
                ];
                DB::table('novel_sections')->insert($dlk);

            }catch (QueryException $e) {
                logger('ID为' . $re . "的小说第" . $title . "插入失败数据为" . \GuzzleHttp\json_encode($dlk));
                continue;
            }catch (\Exception $e) {  //
                continue;
            }
        }
        unset($bs);
        unset($key);
        return $notFound; //返回未获取到的数据
    }
    public function notHtml($bs){
        $novalContentHtml = preg_replace("/<a[^>]*>.*?<\/a>/is", "", $bs);
        $novalContentHtml=$this->deleteScript($novalContentHtml);
        $novalContentHtml = preg_replace("/<meta[^>]*>.*?\/>/is", "", $novalContentHtml);
        #$novalContentHtml = preg_replace("/<div[^>]*>.*?\/>/is", "", $novalContentHtml);
        $novalContentHtml = str_replace('gbk', 'utf-8', $novalContentHtml); //防止乱码
        $sr='<div id="htmlContent" class="contentbox clear">[内容]</div><div class="banner">';
        $novalContentHtml=$this->get_section_data($novalContentHtml,$sr);
        return $novalContentHtml;
    }
    public function multe_curl(array $connomains){
        $res = array();
        $mh = curl_multi_init();//创建多个curl语柄
        $conn=array();
        foreach($connomains as $k=>$url){
            $conn[$k]=curl_init($url);
            curl_setopt($conn[$k], CURLOPT_TIMEOUT, 10);//设置超时时间
            curl_setopt($conn[$k], CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            if (strpos($url, 'https') === false) {
                curl_setopt($conn[$k],CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt($conn[$k],CURLOPT_SSL_VERIFYHOST,false);
            }
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
                $res['right'][$k]=curl_multi_getcontent($conn[$k]);//获得返回信息
            }else{
                $res['error'][$k]=$header[$k];
            }
            curl_multi_remove_handle($mh  , $conn[$k]);//释放资源
            curl_close($conn[$k]);//关闭语柄
        }unset($k);unset($url);

        curl_multi_close($mh);
        return $res;

    }
    public function get_section_data($str, $section){
        if(!$section || !$str){
            return $str;
        }
        $section = explode('[内容]', $section);
        if (empty($section[0]) || empty($section[1])){
            return $str;
        }
        $str = explode($section[0], $str);
        if(empty($str[0]) || empty($str[1])){
            return $str[0];
        }
        $str = explode($section[1], $str[1]);
        return $str[0];
    }
    public function reg($reg,$str){ //目前作者 海报 状态 字数 分类 标签  描述 章节数 阅读人数匹配
        preg_match_all($reg,$str,$data,PREG_SET_ORDER);
        if(!is_array($data)){
            return false;
        }
        return $data;
    }
    private function chrtonum($str){
        $str=trim($str);
        if(is_numeric($str)){
            return $str;
        }

        $map = array(
            '一' => '1','二' => '2','三' => '3','四' => '4','五' => '5','六' => '6','七' => '7','八' => '8','九' => '9',
            '壹' => '1','贰' => '2','叁' => '3','肆' => '4','伍' => '5','陆' => '6','柒' => '7','捌' => '8','玖' => '9',
            '零' => '0','两' => '2',
            '仟' => '千','佰' => '百','拾' => '十',
            '万万' => '亿',
        );

        $str = str_replace(array_keys($map), array_values($map), $str);
        $str =$this->checkString($str, '/([\d亿万千百十]+)/u');

        $func_c2i = function ($str, $plus = false) use(&$func_c2i) {
            if(false === $plus) {
                $plus = array('亿' => 100000000,'万' => 10000,'千' => 1000,'百' => 100,'十' => 10,);
            }

            $i = 0;
            if($plus)
                foreach($plus as $k => $v) {
                    $i++;
                    if(strpos($str, $k) !== false) {
                        $ex = explode($k, $str, 2);
                        $new_plus = array_slice($plus, $i, null, true);
                        $l = $func_c2i($ex[0], $new_plus);
                        $r = $func_c2i($ex[1], $new_plus);
                        if($l == 0) $l = 1;
                        return $l * $v + $r;
                    }
                }

            return (int)$str;
        };
        return $func_c2i($str);

    }
    public function checkString($var, $check = '', $default = '') {
        if (!is_string($var)) {
            if(is_numeric($var)) {
                $var = (string)$var;
            }
            else {
                return $default;
            }
        }
        if ($check) {
            return (preg_match($check, $var, $ret) ? $ret[1] : $default);
        }

        return $var;
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
    public function delock(){
        $lockName='multe_curl'; //因为只能同时运行一个mutel curl
        $this->delLock($lockName);
    }
    /**去除script标签
     * @param $string
     * @return mixed
     */
    public function deleteScript($string){
        $pregfind = array("/<script.*>.*<\/script>/siU",'/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i');
        $pregreplace = array('','');
        $string = preg_replace($pregfind, $pregreplace, $string);
        return $string;
    }
}
