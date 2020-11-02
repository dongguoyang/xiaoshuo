<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/11
 * Time: 11:42
 */

namespace App\Admin\Controllers;

use App\Logics\Repositories\src\NovelRepository;
use App\Logics\Repositories\src\NovelSectionRepository;
use App\Admin\Models\Type;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Logics\Traits\CollectTrait;
use App\Jobs\NovelsCollect;


class NovelCollectController extends AdminController
{
    use CollectTrait;
    private $cookie;
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
            $content->view('admin.home.novelcollect',['options'=>$drs]);
            return $content;
        }else {
            ignore_user_abort(true);    //关掉浏览器，PHP脚本也可以继续执行.
            set_time_limit(0);          // 通过set_time_limit(0)可以让程序无限制的执行下去
            $input = request()->input();
            $url = $input['url'];
            $name = $input['name'];
            $cookie = $input['cookie'];
            if($cookie){
                Cache::put('mp_cookie',  $cookie);
                $this->cookie = $cookie;
            }else{
                $this->cookie = Cache('mp_cookie');
                if(!$cookie){
                    $msg=['code'=>101,'msg'=>'cookie缺失'];
                }
            }
            $header = '';
            //获取小说链接
            $pattern = '/member_token=(.*?);/i';
            preg_match($pattern,$this->cookie,$match);
            if($match){
                $header = 'Authorization: Bearer '.$match[1];
            }
            $cookie_url = $url.'/v1/read/11340166';
            $cookie_rel = $this->cookieCurl($cookie_url,[],[],'json',0,$header);
            $cookie_result = json_decode(mb_convert_encoding($cookie_rel, 'UTF-8', 'GB2312, GBK'),true);
            //登陆错误提示
            if(isset($cookie_result['code']) && $cookie_result['code']==90010){
                $msg = ['code'=>101,'msg'=>$cookie_result['message']];
                return json_encode($msg);
            }
            //书籍查找
            $req_url = $url.'/v1/search?q='.urlencode($name).'&highlight=1';
            $rel = $this->cookieCurl($req_url,[],[],'json',0,$header);
            $novel_list = json_decode(mb_convert_encoding($rel, 'UTF-8', 'GB2312, GBK'),true);
            if(!isset($novel_list['data'])|| !$novel_list['data']){
                $msg = ['code'=>101,'msg'=>'未查找到该书籍'];
                return json_encode($msg);
            }
            //重复书籍提示
            $novel_book_name =(new NovelRepository())->findByCondition([['title', $name], ['origin_web', 'mp']], ['id', 'sections', 'spider_url', 'serial_status', 'status', 'suitable_sex', 'word_count', 'type_ids', 'serial_status']);
            if($novel_book_name){
                $section = (new NovelSectionRepository())->findByCondition([['novel_id', $novel_book_name['id']]], ['num', 'novel_id', 'title', 'spider_url'], ['num', 'desc']);
                if($section){
                    if($section['num'] == $novel_list['data'][0]['article_count']){
                        $msg = ['code'=>101,'msg'=>'已经有该书籍，并且是最新的'];
                        return json_encode($msg);
                    }
                }

            }
            $book_name = str_replace('<em>','',$novel_list['data'][0]['title']);
            $book_name = str_replace('</em>','',$book_name);
            if($book_name != $name){
                $msg = ['code'=>101,'msg'=>'未查找到该书籍'];
                return json_encode($msg);
            }
            $book_id = $novel_list['data'][0]['id'];
            $book_url = $url.'/v1/novels/'.$book_id;
            $arg= [
                'cookie'=>urlencode($this->cookie),
                '--host'=>$url,
                '--type_id'=>12,
                '--suitable_sex'=>1,
                '--link'=>$book_url
            ];
            NovelsCollect::dispatch('novel:collect-mp',$arg);
            $msg = ['code'=>200,'msg'=>'成功'];
            return json_encode($msg);
        }
    }
}