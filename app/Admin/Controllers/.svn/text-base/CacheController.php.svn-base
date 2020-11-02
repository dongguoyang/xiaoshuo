<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Logics\Traits\ApiResponseTrait;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Cache;
use Redis;

class CacheController extends Controller {
    use ApiResponseTrait;

    public function index(Content $content) {
        $content->header('缓存管理');
        $content->description('管理，');
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/administrator']
        );

        $content->body(view('admin.home.cache'));
        return $content;
    }
    /**
     * 获取 缓存信息
     */
    public function getCacheInfo(){
        $key = request()->input('key');
        $info = Cache::get($key);
        if (!$info) {
            $info = Redis::get($key);
            if (!$info) {
                return $this->result('', 2000, '缓存不存在');
            }
        }

        $html = '';
        if (is_array($info)) {
            foreach ($info as $k=>$v) {
                $hlv = is_array($v) ? implode(',', $v) : $v;
                $html .= '<b>' .$k . '</b>' . ' ::&nbsp;&nbsp;&nbsp;' . $hlv . '<br/>';
            }
        } else {
            $html = $info;
        }

        return $this->result($html, 0, 'ok');
    }

    /**
     * 删除指定缓存
     */
    public function delCacheInfo() {
        $key = request()->input('key');
        if ($key == '') {
            return $this->result('', 2000, '缓存键不能为空');
        }

        if (!Cache::forget($key)) {
            if (!Redis::del($key)) {
                return $this->result('', 2000, '删除失败');
            }
        }

        return $this->result('', 0, '操作完成');
    }
    /**
     * 获取redis 信息
     */
    public function getRedisInfo(){
        $key = request()->input('key');
        $info = Redis::get($key);
        if(!$info) {
            return $this->result('', 2000, '缓存不存在');
        }

        $html = '';
        if (is_array($info)) {
            foreach ($info as $k=>$v) {
                $hlv = is_array($v) ? implode(',', $v) : $v;
                $html .= '<b>' .$k . '</b>' . ' ::&nbsp;&nbsp;&nbsp;' . $hlv . '<br/>';
            }
        } else {
            $html = $info;
        }

        return $this->result($html, 0, 'ok');
    }

    /**
     * 删除指定redis信息
     */
    public function deleteRedisCache() {
        $key = request()->input('key');
        if ($key == '') {
            return $this->result('', 2000, '缓存键不能为空');
        }

        if (!Redis::del($key)) {
            return $this->result('', 2000, '删除失败');
        }

        return $this->result('', 0, '操作完成');
    }

    /**
     * 删除指定tag 缓存
     */
    public function DelTagsCache() {
        $key = request()->input('key');
        if ($key == '') {
            return $this->result('', 2000, 'tag 键不能为空');
        }

        if (strpos($key, '..')) {
            $tags = explode('..', $key);
            Cache::tags($tags)->flush();
        } else {
            Cache::tags($key)->flush();
        }

        return $this->result('', 0, '操作完成');
    }

    /**
     * 清空所有缓存
     */
    public function flushAll() {
        Cache::flush();
        if (function_exists('opcache_compile_file')) {
            opcache_reset(); #清空 opcache 缓存
        }

        return $this->result('', 0, '操作完成');
    }

    /**
     * 删除指定list 缓存
     */
    public function DelListCache() {
        $key = request()->input('key');
        if ($key == '') {
            return $this->result('', 2000, 'list 键不能为空');
        }

        $had = Redis::llen($key);
        if ($had) {
            // 删除老数据
            while (true) {
                if (!Redis::rpop($key)) {
                    break;
                }
            }
        }

        return $this->result('', 0, '操作完成');
    }
    public function GetListInfo(){
        $key = request()->input('key');
        $had = Redis::llen($key);

        $html = '列表长度：'.$had;

        return $this->result($html, 0, 'ok');
    }

}
