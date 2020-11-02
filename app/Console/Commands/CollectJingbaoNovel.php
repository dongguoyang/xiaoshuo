<?php
/**
 * 劲爆书站公众号 小说采集的命令
 *
 * 运行命令
 * php artisan novel:collect-jingbao-shuzhan urlencode(cookie)  // 采集全部
 * php artisan novel:collect-jingbao-shuzhan urlencode(cookie) --type=玄幻  // 采集玄幻分类
 * php artisan novel:collect-jingbao-shuzhan urlencode(cookie) --link=http://zjj.muoqe.cn/r/ijkAWcHYw9jBm9aNlNlx,,http://zjj.muoqe.cn/r/ghiB2FTN10xxUzoIZFZvx  // 采集指定link
 *
 * last view novel_section_id = 2226666    // 上次下班观察的章节最大 ID 值
 *
'武侠'    => 6,
'仙侠'    => 6,
'都市'    => 3,
'历史'    => 5,
'游戏'    => 7,
'玄幻'    => 4,
'科幻'    => 8,
'励志'    => 15,
'同人'    => 8,
php artisan novel:collect-jingbao-shuzhan PHPSESSID%3dosiod8f6qgfpdj7bbppjofko4h%3b+Hm_lvt_fcd37298453821c3bc8b266221db88ac%3d1575882958%2c1575885839%3b+Hm_lvt_84bbc585ea2e33b0c5a894bab2c53ab4%3d1575882932%2c1575885839%2c1575886617%2c1575886634%3b+user%3d%257B%2522nickname%2522%253A%2522%255Cu662f%255Cu54e6%2522%252C%2522img%2522%253A%2522http%253A%255C%252F%255C%252Fthirdwx.qlogo.cn%255C%252Fmmopen%255C%252Fvi_32%255C%252FDYAIOgq83eqoicKy5qiboP1kZJdhOyibBDghj29c1DGNuGkRg7LS3cIDZu5VLwmt2M4Y2z1HOKHUnOrXmibuQyuw3g%255C%252F132%2522%252C%2522open_id%2522%253A%2522oH3xe1JpIstLCIYXTUg4YO1tyZQk%2522%252C%2522sex%2522%253A%25221%2522%252C%2522domain%2522%253A%2522lyksrz.chuanyistar.com%2522%252C%2522addtime%2522%253A%25221576058638%2522%252C%2522open_id_pre%2522%253A%2522oH3xe1%2522%257D%3b+suid%3d1108881%3b+Hm_lpvt_84bbc585ea2e33b0c5a894bab2c53ab4%3d1575945002%3b+Hm_lpvt_fcd37298453821c3bc8b266221db88ac%3d1575945002 --type=玄幻
 *
 */

namespace App\Console\Commands;

use App\Logics\Traits\CollectTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class CollectJingbaoNovel extends Command
{
    use CollectTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     */
    protected $signature = 'novel:collect-jingbao-shuzhan {cookie} {--H|host= : 指定采集的域名} {--T|type= : 中文类型名称/all} {--M|slmax= : 章节抓取的最大间隔时间} {--L|link= : 单独采集一部小说的链接地址，多个链接用 ,, 两个英文逗号进行分隔}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '公众号劲爆书站的小说采集程序；order is "novel:collect-jingbao-shuzhan {cookie} {--T|type= : 中文类型名称/all} {--L|link= : 单独采集一部小说的链接地址，多个链接用 ,, 两个英文逗号进行分隔}"';

    protected $cookie = 'PHPSESSID=tppucefh1ms7bj2nusqev1o82o; UM_distinctid=16f042c17bf142-0b7c85085eb44c-1a053053-448e0-16f042c17c0211; CNZZDATA1275384399=864687622-1576320699-%7C1576320699';
    protected $novelWeb = 'jingbao';

    private $spidered = 0;// 抓取了几章了
    private $spideredNum = 0;//已抓取的小说数量
    private $enSpiderNum = 100;//已抓取的小说数量
    private $sleepMax = 15; // 章节抓取间隔最大时间
    private $suitableSex = 1; // 该本小说的适用性别  1男；2女
    protected $noSpiderTitles = [
        '登峰造极'
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->initCollect();
        //$this->disk     = 'local';
        //$this->schemeHose = 'http://d301967.xjnvwpg.cn'; 'http://d301967.vdtuxts.cn';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cookie = $this->argument('cookie');
        if (!$cookie) {
            exit('请填写用户登录凭据 cookie');
        }
        $this->cookie = urldecode($cookie);

        if ($host = $this->option('host')) {
            $this->schemeHose = $host;
        }
        if ($slmax = $this->option('slmax')) {
            $this->sleepMax = $slmax;
        }

        $link = $this->option('link');
        if ($link && strpos(trim($link), 'http') === 0) {// 抓取一本小说
            try {
                $links = explode(',,', $link);
                foreach ($links as $link) {
                    if (empty($link) || strpos(trim($link), 'http') === false) {
                        dump('-------------------- link is error !', $link);
                        continue;
                    }
                    try {
                        $this->novelSpider($link);
                    } catch (\Exception $e) {
                        dump('spiderANovel Exception:', $e->getLine(), $e->getMessage(), $e->getCode());
                        continue;
                    }
                }
                dd('SpiderANovel is over !', $links);
            } catch (\Exception $e) {
                dd($e->getLine(), $e->getMessage(), $e->getCode());
            }
        }
        $this->spideredNum = 0;
        $this->suitableSex = 1;
        $this->novelList('male');
        dump('spideredNum = '. $this->spideredNum);
        $this->spideredNum = 0;
        $this->suitableSex = 2;
        $this->novelList('female');
        dump('spideredNum = '. $this->spideredNum);
        exit('ALL novel roll spider !!!');
    }


    private $spNovel; //当前正在抓取的小说
    private function novelList($gender = 'male') {
        $start = 0;
        $limit = 20;
        $url = $this->schemeHose . '/index.php/cms/api/getbook';
        $header = ["X-Requested-With: XMLHttpRequest", "Referer: {$this->schemeHose}/booklist"];
        while (true) {
            $params['start'] = $start;
            $params['limit'] = $limit;
            $params['xstype'] = '';
            $params['gender'] = $gender;
            $params['tstype'] = '';
            $params['fenlei'] = '';
            $rel = $this->cookieCurl($url, [CURLOPT_HTTPHEADER=>$header], $params);
            $rel = json_decode($rel, 1);
            foreach ($rel['data'] as $item) {
                $this->spNovel = $item;
                $this->spideredNum++; // 增加已抓取数量
                if ($this->spideredNum > $this->enSpiderNum) break;
                try {
                    $this->novelSpider($item['id']);
                } catch (\Exception $e) {
                    dd($this->spNovel['title'], $e->getMessage(), $item);
                }
            }
            if ($this->spideredNum > $this->enSpiderNum) break;

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
        if (!$novel) return false;

        $section = $this->startSpiderSection($novel, $sp_nid); // 查询开始抓取章节
        if (!$section['sp_sid']) {
            dump('没有获取到章节！');
            return false;
        }

        while (true) {
            list($content, $title, $section['sp_sid'], $spider_url) = $this->sectionContent($section['sp_sid']);
            if ($title === null &&  $section['sp_sid'] === null) break;
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

            sleep(mt_rand(1, $this->sleepMax)); // 一章读 5-15 秒钟
        }
        // 更新novel数据
        $novel_up = ['sections'=>$section['num'], 'spider_url'=>$spider_url];
        if ($section['num'] > 20 && $novel['status'] == 0) $novel_up['status'] = 1;
        $this->novels->update($novel_up, $novel['id']);// 更新小说总章节数据
    }
    // 查询小说是否存在；没有就获取并添加小说
    private function getMyNovel($sp_nid) {
        $url = $this->schemeHose . '/nbook/id/' . $sp_nid;
        $this->spidedHtml = $this->cookieCurl($url);

        $data['spider_url'] = $url;
        $data['title']  = $this->novelTitle();
        if (in_array($data['title'], $this->noSpiderTitles)) return null;
        list($data['author_id'], $data['author_name']) = $this->novelAuthor();

        if ($had = $this->novels->findByMap([
            ['title', $data['title']],
            ['author_id', $data['author_id']],
            //['origin_web', $this->novelWeb],
        ], ['id', 'sections', 'spider_url', 'serial_status', 'status'])) {
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
        $data['suitable_sex'] = $this->suitableSex;
        $data['origin_web'] = $this->novelWeb;
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
            $num = round($this->spNovel['zhishu'] / 10000, 2);
        } else {
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
        }
        if ($num < 1) {
            while (true) {
                $num *= 10;
                if ($num > 10) break;
            }
        }

        return $num;
    }
    // 小说类型和连载状态
    private function novelType2SerStatus() {
        if (isset($this->spNovel['tstype']) && $this->spNovel['tstype'] && isset($this->spNovel['xstype']) && $this->spNovel['xstype']) {
            $type_ids = $this->spNovel['tstype'];
            $ser_type = $this->spNovel['xstype'];
        } else {
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

            $type_ids = trim($match2[1]);
            $ser_type = trim($match3[1]);
        }

        return [$this->typeIdsVal($type_ids), $this->serialStatus($ser_type)];
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
            $author_name = $this->spNovel['zuozhe'];
            $author = $this->authors->findBy('name', $author_name, ['id', 'name']);
            if (!$author) {
                $author = $this->authors->create(['name'=>$author_name, 'status'=>1]);
            }
            $author_id = $author['id'];
        } else {
            $author_name = '.';
            $author_id   = 1;
        }

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

        // 匹配下一章地址
        preg_match('/<a class="weui\_btn btn\-next\-article".*?data\-url="(.*?)">下一章/', $rel, $next);
        if (!isset($next[1])) $next[1] = '';
        // 获取章节ID
        $arr = explode('/id/', $next[1]);
        $next[1] = $arr[1];// 下一章ID获取

        preg_match('/<article class="weui\_article">([.\s\S]*?)<\/article>/', $rel, $match);
        if (!isset($match[1]) || !$match[1]) {
            return ['章节内容异常匹配', null, null, $url];
            //throw new \Exception('章节内容异常匹配！', 2000);
        }
        $match[1] = str_replace(['<section>', '</section>'], '', trim($match[1]));
        $match[1] = trim($match[1]);
        if (strpos($match[1], '<br') === 0) {
            // 第一个有换行就去除掉
            $match[1] = substr($match[1], strpos($match[1], '&nbsp;'));
        }

        preg_match('/<h1 class="page_title">(.*?)<\/h1>/', $rel, $title);
        if (!isset($title[1]) || !$title[1]) {
            if (empty(trim(strip_tags($match[1])))) {
                if ($section_id == $next[1]) { // 下一章和当前章节一样；表示已经抓取完成了
                    return ['已有章节抓取完成', null, null, $url];
                }
                return $this->sectionContent($next[1]);
            }
            throw new \Exception('章节标题异常匹配！' . $url, 2000);
        }

        if ($section_id == $next[1]) { // 下一章和当前章节一样；表示已经抓取完成了
            return ['已有章节抓取完成', null, null, $url];
        }

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

    // 获取连载状态
    protected $serialStatus = [
        '连载中'  => 1,
        '连载'    => 1,

        '完结'    => 2,
        '已完结'  => 2,
        '完本'    => 2,
        '已完成'  => 2,

        '限时免费'=> 3,
        '免费'    => 3,
    ];
    private function serialStatus($name) {
        if (is_numeric($name) && in_array($name, $this->serialStatus)) return $name;
        return isset($this->serialStatus[$name]) ? $this->serialStatus[$name] : $this->serialStatus['完结'];
    }

    // 转换性别
    protected $suitSexs = [
        '未知'    => 0,
        '男生'    => 1,
        '女生'    => 2,
    ];
    private function suitSex($name) {
        if (is_numeric($name) && in_array($name, $this->suitSexs)) return $name;
        return isset($this->suitSexs[$name]) ? $this->suitSexs[$name] : $this->suitSexs['未知'];
    }

    // 转换类型
    protected $novelTypes = [
        '武侠'    => 6,
        '仙侠'    => 6,
        '都市'    => 3,
        '历史'    => 5,
        '游戏'    => 7,
        '玄幻'    => 4,
        '科幻'    => 8,
        '励志'    => 15,
        '同人'    => 8,
    ];
    private function typeIdsVal($name) {
        // 实例化小说分类
        if (is_numeric($name) && in_array($name, $this->novelTypes)) return $name;
        return isset($this->novelTypes[$name]) ? $this->novelTypes[$name] : $this->novelTypes['都市'];
    }
}
