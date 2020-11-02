<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/11
 * Time: 11:42
 */

namespace App\Admin\Controllers;


use App\Admin\Models\Author;
use App\Admin\Models\CommonSet;
use App\Admin\Models\Novel;
use App\Admin\Models\NovelSection;
use App\Admin\Models\Type;
use App\Libraries\Facades\Curl;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GetNovelApiController extends AdminController
{
    public function index(Content $content){
        if(request()->method()=='GET'){

            // 选填
            $content->header('获取小说章节');
            // 选填
            $content->description('小说章节');
            // 添加面包屑导航 since v1.5.7
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/administrator']
            );
            $dx=Type::where('pid','<>',0)->get()->toarray();
            $drs=[];
            foreach ($dx as $v){
                $drs[$v['id']]=$v['name'];
            }
            $content->view('admin.home.getnovel',['options'=>$drs]);
            return $content;
        }else {
            ignore_user_abort(true);    //关掉浏览器，PHP脚本也可以继续执行.
            set_time_limit(0);          // 通过set_time_limit(0)可以让程序无限制的执行下去
            $input = request()->input();
            $type = $input['type'];
            $returnType = $input['returnType'];
            $start=strtotime($input['start']);
            $end=strtotime($input['end'])+3600*24;
            $page=$input['page'];
            $domianinfo=CommonSet::where('type','novel_out_api')->where('status',1)->first();
            if(!$domianinfo){
                return json_encode(['code'=>205,'msg'=>'外放接口未配置正确或未配置']);
            }
            $domian=$domianinfo->value;
            $url = $domian."api/getNovel?type=$type&returnType=$returnType&start=$start&end=$end&page=$page";
            $result = Curl::CallCurl($url);
            $result = json_decode($result, 1);
            if ($result['err_code'] == 200) { //请求成功
                $data = $result['data'];
                if(!$data){
                    return json_encode(['code'=>203,'msg'=>'已经抓取完成']);
                }
                foreach ($data as $key => $vs) {
                    $keys = base64_decode($key);
                    $keys = json_decode($keys, 1);
                    $novel = Novel::where('author_name', $keys['author_name'])->where('title', $keys['title'])->first(); //查询自己的库是否有该小说
                    if (!$novel) {
                        $mynovel_id = $this->insertNovelData($keys);  //插入小说和作者
                    } else {
                        $mynovel_id = $novel->id;
                    }
                    $haveNum=NovelSection::where('novel_id',$mynovel_id)->get(['num']);
                    $haveNum=$haveNum->toarray();
                    $dlop=[];
                    if(!empty($haveNum)){
                         foreach ($haveNum as $vh){
                            $dlop[]=$vh['num'];
                         }
                    }
                    foreach ($vs as $vss) {
                        $this->insertNovelSection($vss, $returnType, $mynovel_id,$dlop);  //插入小说章节
                    }
                }
               $page++;
                $msg=['code'=>200,'msg'=>'更新成功','page'=>$page];
            } else {
               $msg=['code'=>101,'msg'=>'请求失败'];
            }
            return json_encode($msg);
        }
    }
    public function insertNovelData(array $data){
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        $issetAuthor=Author::where('name',$data['author_name'])->first();
        if(!$issetAuthor) {
            $dxr = ['name' => $data['author_name'], 'status' => 1, 'created_at' => time(), 'updated_at' => time()];
            $author_id = Author::insertGetId($dxr); //添加作者
        }else{
            $author_id=$issetAuthor->id;
        }
        $data['author_id']=$author_id;
        $imgD=@file_get_contents($data['img']);
        $url='novel_I/'.date('Y-m-d').'/'.time().mt_rand(1000,999999999).'.jpg';
        Storage::put($url,$imgD);
        $data['img']=Storage::url($url);
        $data['created_at']=time();
        $data['updated_at']=time();
        $mynovel_id=Novel::insertGetId($data);
        return $mynovel_id;
    }
    public function insertNovelSection(array $data,$retrunType,$mynovel_id,$dlop){
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
       if(in_array($data['num'],$dlop)){
           return false;
       }
        if($retrunType ==1){  //oss
            $content=@file_get_contents($data['content']);
        }else {
            $content = $data['content'];
        }
        $url='novel_T/'.$data['novel_id'].'/'.time().mt_rand(1000,999999999).'.html';
        Storage::put($url,$content);
        $data['content']=Storage::url($url);
        $data['novel_id']=$mynovel_id;
        NovelSection::create($data);
        return false;
    }





}