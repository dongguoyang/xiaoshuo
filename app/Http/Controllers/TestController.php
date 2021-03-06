<?php

namespace App\Http\Controllers;

use App\Jobs\SendCustomerMsg;
//use App\Logics\Traits\CollectTrait;
use App\Logics\Traits\OfficialAccountTrait;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class TestController extends BaseController
{
    use  OfficialAccountTrait;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'niuniutui.com 小说采集程序；order is "novel:collect-niuniutui {type} {urlencode(cookie)}"';

    protected $cookie = 'PHPSESSID=7fq3cj42fe105422li9geakej4; Hm_lvt_a2a31e4150947f96027c1bdd8f63af7c=1583729657; Hm_lpvt_a2a31e4150947f96027c1bdd8f63af7c=1583729659';


    /**
     * @return mixed
     */
    public function test()
    {
        echo 11111;
        exit();
        $openid = 'oyGmss4HLeVvaFoaBvAJ_t9FrlF8';
        $host = 'http://jfds.axzgb.cn';
        $customer_id = 15;

        SendCustomerMsg::dispatch('recharge-fail', $customer_id, $openid, 12);
        SendCustomerMsg::dispatch('recharge-fail', $customer_id, $openid, 12)->delay(120);

        dd(date('Y-m-d', strtotime(date("Y-m-d",strtotime("+1 month")))));
        $state = 'c%3D15%2Cp%3D0v13v0home0v13v0out0v15v0section0v18v0html0v14v00v12v0novel0v16v0id0v11v01868070v20v0section0v11v020v20v0subscribe0v11v030v20v0customer0v16v0id0v11v0150v20v0cid0v11v015%2Cel%3D217%2Co%3DoceiNsw-xwHGRgWE6ZKqt_dk_GEA';
        dd($this->stateInfo($state));
        $arr = [1, 200=>2, 300=>3, 400=>4];
        foreach ($arr as $k=>$v) {
            if ($k % 200 === 0) {
                dump($k, $v);
            }
        }
        dd();
        $this->schemeHose = 'http://d301967.xjnvwpg.cn';
        $this->novelPageHtml(1884);
    }

    private function novelPageHtml($id) {
        $url = $this->schemeHose . '/nbook/id/' . $id;
        //$this->spidedHtml = $this->cookieCurl($url);
        //die($this->spidedHtml);
        //$rel = $this->sectionList($id, 0);
        //dd(end($rel));
        dd($this->novelList());
    }

    private $spNovel; //当前正在抓取的小说
    private function novelList() {
        $start = 0;
        $limit = 20;
        $url = $this->schemeHose . '/index.php/cms/api/getbook';
        $header = ["X-Requested-With: XMLHttpRequest", "Referer: {$this->schemeHose}/booklist"];
        while (true) {
            $params['start'] = $start;
            $params['limit'] = $limit;
            $params['xstype'] = '';
            $params['gender'] = 'male';
            $params['tstype'] = '';
            $params['fenlei'] = '';
            $rel = $this->cookieCurl($url, [CURLOPT_HTTPHEADER=>$header], $params);
            $rel = json_decode($rel, 1);
            foreach ($rel['data'] as $item) {
                $this->spNovel = $item;
                $this->novelSpider($item['id']);
            }

            $start += $limit;
        }

    }
    // 单本小说抓取
    // $url 抓取小说的url    $sp_nid 是劲爆那边的小说ID
    private function novelSpider($url) {
        if (is_numeric($url)) {
            $sp_nid = $url;
        } else {
            $arr = explode('nbook/id/', $url);
            $sp_nid = $arr[1];
        }

        $novel = $this->getMyNovel($sp_nid); // 添加/查询小说信息

        $section = $this->startSpiderSection($novel, $sp_nid); // 查询开始抓取章节
        if (!$section['sp_sid']) {
            dump('没有获取到章节！');
            return false;
        }

        while (true) {
            list($content, $title, $section['sp_sid'], $spider_url) = $this->sectionContent($section['sp_sid']);
            if (!$this->novelSections->findByMap([
                ['novel_id', $novel['id']],
                ['title', $title],
            ], ['id', 'spider_url'])) {
                $section['num']++;
                $data['novel_id']   = $novel['id'];
                $data['num']        = $section['num'];
                $data['title']      = $title;
                $path = 'html/' . $novel['id'] . date('/Ymd/') . RandCode(18, 12) . '.html';
                $data['content']    = $this->uploadCloud($path, $content);
                $data['spider_url'] = $spider_url;
                $this->novelSections->create($data);
            }
            if (!$section['sp_sid']) {
                dump('小说最新章节信息抓取完成！');
                break;
            }
        }
        // 更新novel数据
        $novel_up = ['sections'=>$section['num'], 'spider_url'=>$spider_url];
        if ($section['num'] > 20) $novel_up['status'] = 1;
        $this->novels->update($novel_up, $novel['id']);// 更新小说总章节数据
    }
    // 查询小说是否存在；没有就获取并添加小说
    private function getMyNovel($sp_nid) {
        $url = $this->schemeHose . '/nbook/id/' . $sp_nid;
        $this->spidedHtml = $this->cookieCurl($url);

        $data['spider_url'] = $url;
        $data['title']  = $this->novelTitle();
        list($data['author_id'], $data['author_name']) = $this->novelAuthor();
        if ($had = $this->novels->findByMap([['title', $data['title']], ['author_id', $data['author_id']]], ['id', 'sections', 'spider_url', 'serial_status'])) {
            $had['spider_url'] = $data['spider_url'];
            return $had;
        }

        $data['desc']   = $this->novelDesc();
        $data['tags']   = $this->novelTags();
        $data['word_count'] = $this->novelWordCount();
        list($data['type_ids'], $data['serial_status']) = $this->novelType2SerStatus();
        $data['img']    = $this->uploadImg($this->novelImg(), $data['type_ids']);
        $data['need_buy_section'] = 9;
        $data['subscribe_section'] = 8;
        $data['status'] = 0;
        $in = $this->novels->create($data);
        return ['id'=>$in['id'], 'sections'=>0, 'spider_url'=>$data['spider_url']];
    }
    // 获取抓取的开始章节；
    // $novel 我们数据库里面的小说信息；$sp_nid 抓取的小说ID
    private function startSpiderSection($novel, $sp_nid) {
        // 查询我们数据里面的最大章节
        $section = $this->novelSections->findByCondition([['novel_id', $novel['id']]], ['num', 'novel_id', 'title', 'spider_url'], ['num', 'desc']);
        if ($section) {
            $section = $this->novelSections->toArr($section);
            $section['sp_sid']  = 0;
            $start = 0;
            $endsec = [];
            while (true) {
                $seclist = $this->sectionList($sp_nid, $start);
                if (!$seclist) {
                    throw new \Exception('目录列表获取失败！', 2000);
                }
                foreach ($seclist as $sec) {
                    if (trim($sec['title']) == $section['title']) {
                        $endsec = $sec;
                        break;
                    }
                }
                if ($endsec) {
                    $section['sp_sid']  = $endsec['id'];
                    $section['sid_had'] = true;
                    break;
                }
                $sec = end($seclist);
                $start = $sec['idx'];
            }
        } else {
            $seclist = $this->sectionList($sp_nid, 0);
            $section['num']     = 0;
            $section['novel_id']= $novel['id'];
            $section['title']   = null;
            $section['sp_sid']  = $seclist[0]['id'];
        }

        return $section;
    }
    // 小说标题
    private function novelTitle() {
        if (isset($this->spNovel['title']) && $this->spNovel['title']) {
            return trim($this->spNovel['title']);
        }

        preg_match('/<h1 class="novel\-title.*?>(.*?)<\/h1>/', $this->spidedHtml, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('标题异常匹配！', 2000);
        }

        return trim($match[1]);
    }
    // 小说简介
    private function novelDesc() {
        if (isset($this->spNovel['desc']) && $this->spNovel['desc']) {
            return trim($this->spNovel['desc']);
        }

        preg_match('/<div class="novel\-summary[.\s\S]*?<p>(.*?)<\/p>/', $this->spidedHtml, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('简介异常匹配！', 2000);
        }

        return trim($match[1]);
    }
    // 小说总字数
    private function novelWordCount() {
        if (isset($this->spNovel['zhishu']) && $this->spNovel['zhishu']) {
            return round($this->spNovel['zhishu'] / 10000, 2);
        }

        preg_match('/<div class="novel\-meta">([.\s\S]*?)<\/div>/', $this->spidedHtml, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('字数异常匹配！', 2000);
        }

        $num = trim(str_replace(['字数', ':', '：', '字'], '', trim($match[1])));
        if (strpos($num, '万') === false) {
            if (!$num) $num = mt_rand(100000, 1000000); // 没有字数，就随机获取一个
            $num = round($num / 10000, 2);
        } else {
            $num = str_replace('万', '', $num);
        }
        return $num;
    }
    // 小说类型和连载状态
    private function novelType2SerStatus() {
        if (isset($this->spNovel['tstype']) && $this->spNovel['tstype'] && isset($this->spNovel['xstype']) && $this->spNovel['xstype']) {
            return [$this->spNovel['tstype'], $this->spNovel['xstype']];
        }

        preg_match('/<div class="wsj\_sxstype.*?>([.\s\S]*?)<\/div>/', $this->spidedHtml, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('分类和连载状态异常匹配！', 2000);
        }

        preg_match('/<span>(.*?)<\/span>/', $match[1], $match2);
        if (!isset($match2[1]) || !$match2[1]) {
            throw new \Exception('分类异常匹配！', 2000);
        }
        preg_match('/<span style=.*?>(.*?)<\/span>/', $match[1], $match3);
        if (!isset($match3[1]) || !$match3[1]) {
            throw new \Exception('连载状态异常匹配！', 2000);
        }

        return [trim($match2[1]), trim($match3[1])];
    }
    // 小说封面图
    private function novelImg() {
        if (isset($this->spNovel['image']) && $this->spNovel['image']) {
            $img = $this->spNovel['image'];
        } else {
            preg_match('/<div class="novel\-cover">[.\s\S]*?<img.*?src=[\'\"]([.\s\S]*?)[\'\"]/', $this->spidedHtml, $match);
            if (!isset($match[1]) || !$match[1]) {
                throw new \Exception('图片异常匹配！', 2000);
            }
            $img = $match[1];
        }

        if (strpos($img, '://') === false) {
            $img = $this->schemeHose . $img;
        }

        return $img;
    }
    // 获取作者信息
    private function novelAuthor() {
        if (isset($this->spNovel['zuozhe']) && $this->spNovel['zuozhe']) {

        }

        $author_name = '.';
        $author_id   = 1;

        return [$author_id, $author_name];
    }
    // 小说封面上传
    // $img 图片地址；$type_id 类型ID
    private function uploadImg($img, $type_id) {
        $ext = '.jpg';
        if (strstr($img, '.jpeg')) {
            $ext = '.jpeg';
        } else if (strstr($img, '.png')) {
            $ext = '.png';
        }

        $name = 'img/' . $type_id . date('/Ymd/His_') . RandCode(18, 12) . $ext;
        $img = $this->uploadCloud($name, @file_get_contents($img));

        return $img;
    }
    // 小说章节列表获取
    // $id 小说ID；$start 从第几章开始获取
    private function sectionList($id, $start = 0) {
        $url = $this->schemeHose . '/index.php/cms/api/getchater/?bid='.$id.'&start='.$start;
        $header = ["X-Requested-With: XMLHttpRequest", "Referer: {$this->schemeHose}/chapter/bid/{$id}"];
        $data = $this->cookieCurl($url, [CURLOPT_HTTPHEADER=>$header]);
        if (!$data && !$start) {
            throw new \Exception('目录列表获取失败！', 2000);
        }
        $data = json_decode($data, 1);
        if (isset($data['data']))
            return $data['data'];
        else
            return [];
    }
    // 章节正文内容获取和下一章地址获取
    // $section_id 章节ID
    private function sectionContent($section_id) {
        $url = $this->schemeHose . '/detail/id/' . $section_id;
        $rel = $this->cookieCurl($url);
        preg_match('/<article class="weui\_article">([.\s\S]*?)<\/article>/', $rel, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('章节内容异常匹配！', 2000);
        }
        $match[1] = str_replace(['<section>', '</section>'], '', trim($match[1]));
        $match[1] = trim($match[1]);
        if (strpos($match[1], '<br') === 0) {
            // 第一个有换行就去除掉
            $match[1] = substr($match[1], strpos($match[1], '&nbsp;'));
        }

        preg_match('/<h1 class="page_title">(.*?)<\/h1>/', $rel, $title);
        if (!isset($title[1]) || !$title[1]) {
            throw new \Exception('章节标题异常匹配！', 2000);
        }

        // 匹配下一章地址
        preg_match('/<a class="weui\_btn btn\-next\-article".*?data\-url="(.*?)">下一章/', $rel, $next);
        if (!isset($next[1])) $next[1] = '';
        // 获取章节ID
        $arr = explode('/id/', $next[1]);
        $next[1] = $arr[1];

        return [$match[1], trim($title[1]), $next[1], $url];
    }
    // 小说章节目录地址获取
    private function novelSectionUrl() {
        preg_match('/<div class="catalog\-footer">([.\s\S]*?)<\/div>/', $this->spidedHtml, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('目录地址异常匹配！', 2000);
        }
        preg_match('/href="(.*?)"/', $match[1], $match2);
        if (!isset($match2[1]) || !$match2[1]) {
            throw new \Exception('目录地址url异常匹配！', 2000);
        }

        if (strpos($match2[1], '://') === false) {
            $match2[1] = $this->schemeHose . $match2[1];
        }
        return $match2[1];
    }



}
