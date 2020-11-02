<?php
/**
 * 小说采集的命令
 */

namespace App\Console\Commands;

use App\Libraries\Facades\Curl;
use App\Logics\Traits\RedisCacheTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException;
use Mockery\Exception;
use OSS\Core\OssException;


class CollectNovel extends Command
{
    use RedisCacheTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CollectNovel:work {id}';

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
        $Id = $this->argument('id');
        if (!$Id) {
            exit('请输入ID');
        }

        $types=DB::table('reg_type')->where('reg_id',$Id)->get()->toarray();
        if(!$types){
            exit('请设置了type在进行！');
        }
        $commonset = DB::table('reg')->where('status', 1)->where('id', $Id)->first();
        if (!$commonset) {
            exit('请设置了在进行！');
        }
        $time = time();
        set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期
        foreach ($types as $kjh) {
            $jk = $commonset->page; //表示页码
            for ($ipage = 1; $ipage <= $jk; $ipage++) {
                $url = $commonset->site_url .$kjh->name. $ipage . $commonset->site_end_url;
                $this->info('进入地址:'.$url.' 页面');
                $rs = Curl::callCurl($url);
                $encode = mb_detect_encoding($rs, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
                if($encode != "UTF-8"){
                    $rs = mb_convert_encoding($rs, 'utf-8', 'GB2312'); //转码
                }
                $info = $commonset->list_reg; //；列表行起始位置
                $data = $this->get_section_data($rs, $info);
                $data = str_replace(array("\r\n", "\r", "\n","\t"), "", $data);
                preg_match_all($commonset->list_tag, $data, $dlop, PREG_SET_ORDER);
                if (count($dlop) == 0) {
                    exit('请你设置正确-list_tag');
                }
                $data = array();
                foreach ($dlop as $lku) {
                    $data[] = $lku[1];
                }
                unset($lku);
                $statusBy=DB::table('reg_endoff_novel')->orderBy('id','desc')->first();
                if(!empty($statusBy)){
                    if($statusBy->status == 0){
                        array_unshift($data,$statusBy->novel_url);
                    }
                }

                //$data表示一页所有的小说地址
                foreach ($data as  $v) { //目前作者 海报 状态 字数 分类 标签   章节数 阅读人数 在此获取 匹配对应的正则表达式获取
                    if(strpos($v,'http') === false){
                        $v=$commonset->domian.$v;
                    }
                    $date=date('Y-m-d/His',time());
                    DB::beginTransaction(); //开启事务
                    try {
                        $this->info('进入地址:'.$v.' 的小说');
                        $res = Curl::callCurl($v);
                        $encode = mb_detect_encoding($res, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
                        if($encode != "UTF-8"){
                            $res = mb_convert_encoding($res, 'utf-8', 'GB2312'); //转码
                        }
                        $res = str_replace(array("\r\n", "\r", "\n","\t"), "", $res);
                        if (empty($res)) {
                            throw new \Exception('小说地址'.$v . ' --的curl没有进入到', 99);
                        }
                        //匹配标题
                        $reg2_title = $commonset->reg1_title;  //标题正则
                        $titlearr = $this->reg($reg2_title, $res);
                        if (!$titlearr) {
                            //throw new \Exception('标题正则错误', 100); 没获取到标题不用管
                        }
                        if (!isset($titlearr[0][1])) {
                            $titlearr[0][1] = "未取到标题";
                        }

                        $title = $titlearr[0][1];

                        //匹配作者
                        $reg2_author = $commonset->reg1_author;
                        $authorarr = $this->reg($reg2_author, $res);
                        if (!$authorarr) {
                            //throw new \Exception('作者正则匹配错误!',101);
                        }
                        if (!isset($authorarr[0][1])) {
                            $titlearr[0][1] = "未取到作者";
                        }
                        $author = $authorarr[0][1];
                       //匹配标签(可选) tag
                        if(isset($commonset->reg2_tag)){
                            $reg2_tag=$commonset->reg2_tag;
                            $tagarr=$this->reg($reg2_tag,$res);
                            if(!isset($tagarr[0][1])){
                                $tag="";
                            }else{
                                $tag=$tagarr[0][1];
                            }
                        }else{
                            $tag="";
                        }

                        //匹配海报
                        $reg2_headeUrl = $commonset->reg1_img;
                        $headerUrlarr = $this->reg($reg2_headeUrl, $res);
                        if (!$headerUrlarr) {
                            //throw new \Exception('海报未获取到',102);
                            //exit('海报未获取到');
                        }
                        if (!isset($headerUrlarr[0][1])) {
                            $headerUrl="";
                        }else{ //保存图片到本地
                            $headerUrl = $headerUrlarr[0][1];
                            if(strpos($headerUrl,'http') ===false){
                                $headerUrl=$commonset->domian.$headerUrl;
                            }
                            $headerdata=@file_get_contents($headerUrl);
                            $imgname=$date.'/'.Carbon::now()->timestamp.mt_rand(10,100000).'.jpg';
                            $headerUrl=$this->uploadOss($imgname,$headerdata);
                        }
                        if (strpos($headerUrl, 'http') === false) {
                            $headerUrl = $commonset->domian . $headerUrl;
                        }
                        //匹配简介

                        $reg2_remark = $commonset->reg1_remark;
                        $remarkarr = $this->reg($reg2_remark, $res);
                        if (!$reg2_remark) {
                            //exit('简介正则匹配错误');
                        }
                        if (!isset($remarkarr[0][1])) {
                            $remarkarr[0][1] = "您要是觉得该小说还不错的话请不要忘记向您QQ群和微博微信里的朋友推荐哦！";
                        }
                        $remark = strip_tags($remarkarr[0][1]);

                        $remark=preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags($remark));
                        $remark=mb_substr($remark,0,255);
                        //状态匹配
                        $reg_status = $commonset->reg1_status;
                        $statusarr = $this->reg($reg_status, $res);
                        if (!$statusarr) {
                            //exit('状态匹配错误');
                        }
                        if (!isset($statusarr[0][1])) {
                            $statusarr[0][1] = '连载中';
                        }
                        $status = $statusarr[0][1];
                        $authorsRemark=DB::table('authors')->where('name',$author)->first();
                        if(!empty($authorsRemark)){
                            $author_id=$authorsRemark->id;
                        }else {
                            $ds = ['name' => $author, 'status' => 1, 'updated_at' => $time, 'created_at' => $time];
                            $author_id = DB::table('authors')->insertGetId($ds);
                        }
                        $status = $this->serial_status($status);
                        $novelData=DB::table('novels')->where('title',$title)->where('author_name',$author)->first();


                        $reg = $commonset->reg1_content;  //章节行的正则 //第几章匹配以及它的地址匹配
                        preg_match_all($reg, $res, $contenturl, PREG_SET_ORDER);
                        if (isset($contenturl[0])) {
                            if (count($contenturl[0]) != 3) {  //正则表达不清楚回归不录入
                                DB::rollback();  //回滚
                                continue; //到下一篇小说
                            }
                        } else {
                            DB::rollback();  //回滚
                            continue;
                        }

                        $num=count($contenturl);
                        if(!empty($novelData)){ //小说已经有了
                           $dsj=['sections'=>$num];
                           DB::table('novels')->where('id',$novelData->id)->update($dsj); //更新章节
                            $re=$novelData->id;
                        }else {
                            $dsj = [
                                'desc' => $remark,
                                'created_at' => $time,
                                'updated_at' => $time,
                                'word_count' => '130',
                                'author_name' => $author,
                                'title' => $title,
                                'author_id' => $author_id,
                                'img' => $headerUrl,
                                'serial_status' => $status,
                                'sections' => $num,
                                'suitable_sex' => $kjh->sex,
                                'tags' => $tag,
                                'type_ids' => $kjh->typesid
                            ];
                            $re = DB::table('novels')->insertGetId($dsj);
                        }
                        //标记是否断裂的小说
                        $reg_endOff_novel=[
                            'novel_id'=>$re,
                            'novel_url'=>$v,
                            'reg_id'=>$commonset->id,
                            'status'=>0,
                            'created_at'=>time(),
                            'updated_at'=>$time
                        ];
                       $reg_statusID= DB::table('reg_endoff_novel')->insertGetId($reg_endOff_novel); //标记的id记录


                        DB::commit();  //提交


                    } catch (QueryException $e) {
                        DB::rollback();  //回滚
                        throw new \Exception($e->getMessage(),110);
                    } catch (\Exception $e) { //致命错误采集的数据不规则舍去
                        DB::rollback();  //回滚
                        $das = [
                            'code' => $e->getCode(),
                            'msg' => $e->getMessage(),
                            'created_at' => Carbon::now()->timestamp , 'updated_at' => Carbon::now()->timestamp
                        ];
                        DB::table('reg_error_msg')->insert($das); //正则匹配错误
                        continue;
                    }
                    $cons = array();
                    foreach ($contenturl as $l) {
                        $l[2] = $this->chrtonum($l[2]);
                        if($commonset->reg1_add_v==1){ //有点特殊表示不加
                            $cons[(int)$l[2] . '--' . mt_rand(100, 999999)] = $commonset->domian.$l[1];
                        }else {
                            $cons[(int)$l[2] . '--' . mt_rand(100, 999999)] = $v . $l[1];  //防止出现上下篇
                        }
                    }
                    unset($l);
                    $readycons=$cons; //后面偶尔会用到
                    $newalldata=[];
                    $allData=[];
                    $cons=$this->sliceArr($cons,50);
                    $i=1;
                    foreach ($cons as $consv){
                            $this->lock(); //multe_curl加锁
                            $newalldata[$i] = $this->multe_curl($consv);  //获取数据
                            $this->delock();
                            $i++;
                    }unset($consv);
                    $this->info('数据已经抓取完成准备入库');
                    foreach ($newalldata as $newallv){  //$i
                        foreach ($newallv as $newallv_key=>$newallv_v){ //[right=>,error=>]
                            if($newallv_key == 'right'){
                                foreach ($newallv_v as $newall_v_right_key=>$newall_v_right_v){
                                         $allData['right'][$newall_v_right_key]=$newall_v_right_v;
                                }
                            }
                            if($newallv_key == 'error'){
                                foreach ($newallv_v as $newall_v_error_key=>$newall_v_error_v){
                                    $allData['error'][$newall_v_error_key]=$newall_v_error_v;
                                }
                            }
                        }
                    }
                    if(isset($allData['right'])){
                        $creeParamData=$allData['right']; //返回成功的数据 mutelcurl
                        $fall_cree_data=$this->creeData($re, $commonset, $creeParamData);  //入库失败的数据 [章节=>章节]
                        if(count($fall_cree_data)){
                            foreach ($fall_cree_data as $fall_cree_data_v){
                               $nums= explode('--',$fall_cree_data_v);  //184--727799
                                $tsme = Carbon::now()->timestamp;
                                $csp=[
                                    'novel_id'=>$re,
                                    'url'=>$readycons[$fall_cree_data_v],
                                    'num'=>$nums[0],
                                    'created_at'=>$tsme,
                                    'updated_at'=>$tsme,
                                    'reg_id'=>$commonset->id
                                ];
                                $this->insertNullData($csp);
                            }
                        }
                    }
                    if(isset($allData['error'])){
                        $errorData=$allData['error']; //未获取到的数据 状态码不为200
                        if(count($errorData)) {
                            $nextData = [];
                            foreach ($errorData as $errokes => $errovs) {
                                $nextData[$errokes] = $errovs['url'];   //章节   url
                            }
                            $ersdata=$this->notDataCount($re, $commonset, $nextData, 2); //重新获取
                            foreach ($ersdata as $ersk=>$ersv){
                                $tsme = Carbon::now()->timestamp;
                                $nums=explode('--',$ersk);
                                $csp=[
                                    'novel_id'=>$re,
                                    'url'=>$ersv,
                                    'num'=>$nums[0],
                                    'created_at'=>$tsme,
                                    'updated_at'=>$tsme,
                                    'reg_id'=>$commonset->id
                                ];
                                $this->insertNullData($csp);
                            }
                        }
                    }

                   // $reg_statusID
                    DB::table('reg_endoff_novel')->where('id',$reg_statusID)->update(['status'=>1]);
                    $this->info($kjh->name.'的第'.$ipage.'页的的小说已经完成一部');
                }
                unset($v);
                $this->info($kjh->name.'的第'.$ipage.'页已完成');
            }
            $this->info($kjh->name ."的系列已完成");
        }
        echo "完成";
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

    /**插入失败的信息
     * @param array $data
     * @return bool
     */
    public function insertNullData(array $data){
        $res=DB::table('reg_null_data')->insert($data);
        return $res;
    }

    /**失败信息进行下次重新获取
     * @param array $data 数据
     * @param int $count 次数
     * @return array
     */
    public function notDataCount($re,$commonset,array $data,$count=1){  //未发现数据的处理
           // $Indata=$data;  //导致下次次数 ['章节'=>'url']
        $i=1;
        while (true) {
            if($i>$count){
                break;
            }
            $data = $this->sliceArr($data, 50);
            $kris = [];
            foreach ($data as $v) {
                $this->lock();
                $kris[] = $this->multe_curl($v);
                $this->delock();
            }
            unset($v);
            $allData = [];
            $errorData = [];
            foreach ($kris as $kris_v) { //$i
                foreach ($kris_v as $kris_v_key => $kris_v_v) { //right error
                    if ($kris_v_key == "right") {
                        foreach ($kris_v_v as $kris_v_v_key => $kris_v_v_v) {
                            $allData['right'][$kris_v_v_key] = $kris_v_v_v;
                        }
                        unset($kris_v_v_key);
                        unset($kris_v_v_v);
                    }
                    if ($kris_v_key == "error") {
                        foreach ($kris_v_v as $kris_v_v_key => $kris_v_v_v) {
                            $allData['error'][$kris_v_v_key] = $kris_v_v_v;
                        }
                        unset($kris_v_v_key);
                        unset($kris_v_v_v);
                    }

                }
                unset($kris_v_key);
                unset($kris_v_v);
            }
            unset($kris_v);
            if (isset($allData['right'])) {
                $param = $allData['right'];
                $this->creeData($re, $commonset, $param); //[章节=>章节] //插入
            }
            $data=[];
            if (isset($allData['error'])) {
                $notparam = $allData['error'];
                if (count($notparam)) {
                    foreach ($notparam as $kd => $vd) {
                        $data[$kd] = $vd['url'];
                    }unset($kd);unset($vd);
                }
            }
            $i++;
        }
        return $data;
    }

    /**正则匹配示函数
     * @param $reg 正则表达式
     * @param $str 字符
     * @return bool
     */
    public function reg($reg,$str){ //目前作者 海报 状态 字数 分类 标签  描述 章节数 阅读人数匹配
        if($reg==""){
            return false;
        }
        preg_match_all($reg,$str,$data,PREG_SET_ORDER);
        if(!is_array($data)){
            return false;
        }
        return $data;
    }

    /**筛选截取函数
     * @param $str
     * @param $section
     * @return array
     */
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

    /**
     * @param $str
     * @return mixed
     */
    public function novalcontent_call($str){  //处理内容字符
        $str=preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags($str));
        return $str;
    }

    /**小说状态解析
     * @param $status
     * @return int
     */
    public function serial_status($status){   //小说状态

        if($status == '连载中'){
            return 1;
        }else{
            if($status=="已完结"){
                return 2;
            }else{
                if($status=='限时免费'){
                    return 3;
                }else{
                    return 1;
                }
            }
        }
    }

    /**多进程curl函数
     * @param array $connomains
     * @return array
     */
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

    /**将中文数组转为阿拉伯数字
     * @param $str
     * @return int|mixed|string
     */
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

    /**与chrtonum连用
     * @param $var
     * @param string $check
     * @param string $default
     * @return string
     */
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

    /**去掉a标签和script标签 并转区未utf-8模式
     * @param $bs
     * @param $comonset
     * @return array|mixed
     */
    public function notHtml($bs,$comonset){
        $novalContentHtml = preg_replace("/<a[^>]*>(.*?)<\/a>/is", "$1", $bs);
        $novalContentHtml=preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", $novalContentHtml);
        $novalContentHtml = preg_replace("/<script[^>]*>.*?<\/script>/is", "", $novalContentHtml);
        $novalContentHtml=preg_replace("/<(\/?link.∗?)>/si","",$novalContentHtml); //过滤link标签
        $novalContentHtml=$this->deleteScript($novalContentHtml);
        $novalContentHtml = preg_replace("/<meta[^>]*>.*?\/>/is", "", $novalContentHtml);
        $novalContentHtml = str_replace('gbk', 'utf-8', $novalContentHtml); //防止乱码
        $sr=$comonset->list_novel_content;
        $novalContentHtml=$this->get_section_data($novalContentHtml,$sr); //获取内容
        return $novalContentHtml;
    }

    /**小说整理入库
     * @param $re 小说id
     * @param $commonset 配置设置对象
     * @param array $allData 多进程curl的数据
     * $count 表示的未获取到要几次
     */
    private function creeData($re,$commonset,array $allData,$count=0){
        $notFound=array();//没有找到的数据
        $time = Carbon::now()->timestamp;
        $exitNovel=DB::table('novel_sections')->where('novel_id',$re)->get(['num'])->toArray();
        $dxtable=[];
        foreach ($exitNovel as $exv){
            $dxtable[]=$exv->num;
        }
        foreach ($allData as $key=> $bs) { //key表示章节  bs表示内容
            try {
                if(in_array($key,$dxtable)){ //已经存在该小说章节
                   continue;
                }
                $encode = mb_detect_encoding($bs, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
                if($encode !='UTF-8') {
                    $bs = mb_convert_encoding($bs, 'utf-8', 'GB2312');
                }
                $novalContentHtml = $this->notHtml($bs,$commonset);
                $reg = $commonset->reg1_content_title;
                $titlearr = $this->reg($reg, $bs); //获取章节名称
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
                    $numarr[0][1]=$key; //默认值为**
                }
                $num = $this->chrtonum($numarr[0][1]); //章数
                //传至oss
                //$name=$re.'号章节为('.$num.')';
                $name = md5($re . '|' . $num.time());
                $name = 'novel/'.$re.'/'.$name . '.html';
                $url=$this->uploadOss($name,$novalContentHtml);
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
            }catch (Exception $e) {  //
                logger('问题为'.$e->getMessage());
                continue;
            }
        }
        unset($bs);
        unset($key);
        return $notFound; //返回未获取到的数据
    }

    /**
     * mutel curl加锁
     */
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
    //去掉script标签
    public function deleteScript($string){
            $pregfind = array("/<script.*>.*<\/script>/siU",'/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i');
            $pregreplace = array('','');
            $string = preg_replace($pregfind, $pregreplace, $string);
            return $string;
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
//测试用到
    public function test($re,$commonset){
        $r=['141--355392'=>'https://www.boluoxs.com/biquge/70/70294/xs27057135.html','14520--123'=>'https://www.boluoxs.com/biquge/70/70294/x'];
        $rs=$this->notDataCount($re,$commonset,$r,3);
        foreach ($rs as $ersk=>$ersv){
            $nums=explode('--',$ersk);
            $tsme = Carbon::now()->timestamp;
            $csp=[
                'novel_id'=>$re,
                'url'=>$ersv,
                'num'=>$nums[0],
                'created_at'=>$tsme,
                'updated_at'=>$tsme,
                'reg_id'=>$commonset->id
            ];
            $this->insertNullData($csp);
        }
    }


}
