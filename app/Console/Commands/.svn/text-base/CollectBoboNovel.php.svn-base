<?php
/**
 * 博博美文公众号 小说采集的命令
 *  novel.id > 187367       novel_section.id > 2348110
 * 运行命令
 * php artisan novel:collect-jingbao-shuzhan urlencode(cookie)  // 采集全部
 * php artisan novel:collect-jingbao-shuzhan urlencode(cookie) --type=玄幻  // 采集玄幻分类
 * php artisan novel:collect-bobo-meiwen urlencode(cookie) --link=http://zjj.muoqe.cn/r/ijkAWcHYw9jBm9aNlNlx,,http://zjj.muoqe.cn/r/ghiB2FTN10xxUzoIZFZvx  // 采集指定link
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
php artisan novel:collect-bobo-meiwen PHPSESSID%3dosiod8f6qgfpdj7bbppjofko4h%3b+Hm_lvt_fcd37298453821c3bc8b266221db88ac%3d1575882958%2c1575885839%3b+Hm_lvt_84bbc585ea2e33b0c5a894bab2c53ab4%3d1575882932%2c1575885839%2c1575886617%2c1575886634%3b+user%3d%257B%2522nickname%2522%253A%2522%255Cu662f%255Cu54e6%2522%252C%2522img%2522%253A%2522http%253A%255C%252F%255C%252Fthirdwx.qlogo.cn%255C%252Fmmopen%255C%252Fvi_32%255C%252FDYAIOgq83eqoicKy5qiboP1kZJdhOyibBDghj29c1DGNuGkRg7LS3cIDZu5VLwmt2M4Y2z1HOKHUnOrXmibuQyuw3g%255C%252F132%2522%252C%2522open_id%2522%253A%2522oH3xe1JpIstLCIYXTUg4YO1tyZQk%2522%252C%2522sex%2522%253A%25221%2522%252C%2522domain%2522%253A%2522lyksrz.chuanyistar.com%2522%252C%2522addtime%2522%253A%25221576058638%2522%252C%2522open_id_pre%2522%253A%2522oH3xe1%2522%257D%3b+suid%3d1108881%3b+Hm_lpvt_84bbc585ea2e33b0c5a894bab2c53ab4%3d1575945002%3b+Hm_lpvt_fcd37298453821c3bc8b266221db88ac%3d1575945002 --type=玄幻
 *
 */

namespace App\Console\Commands;

use App\Logics\Traits\CollectTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class CollectBoboNovel extends Command
{
    use CollectTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     */
    protected $signature = 'novel:collect-bobo-meiwen {cookie} {token} {--H|host= : 指定采集的域名} {--T|type= : 指定采集首页还是分类页} {--M|slmax= : 章节抓取的最大间隔时间} {--L|link= : 单独采集一部小说的链接地址，多个链接用 ,, 两个英文逗号进行分隔}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '公众号博博美文、月耀看书的小说采集程序；order is "novel:collect-bobo-meiwen {cookie} {token} {--host=} {--U|ua= : 浏览器UA}"';

    protected $cookie = 'PHPSESSID=tppucefh1ms7bj2nusqev1o82o; UM_distinctid=16f042c17bf142-0b7c85085eb44c-1a053053-448e0-16f042c17c0211; CNZZDATA1275384399=864687622-1576320699-%7C1576320699';
    protected $token = '登录验证的 token';
    protected $novelWeb = 'bobomeiwen';

    private $spidered = 0;// 抓取了几章了
    private $spideredNum = 0;//已抓取的小说数量
    private $enSpiderNum = 100;//已抓取的小说数量
    private $sleepMax = 15; // 章节抓取间隔最大时间
    private $suitableSex = 1; // 该本小说的适用性别  1男；2女
    protected $noSpiderTitles = [
        '登峰造极'
    ];
    protected  $sp_section_offset = 0; //抓取章节目录的第几页

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
        $this->cookieCurlUA = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36 QBCore/4.0.1301.400 QQBrowser/9.0.2524.400 Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2875.116 Safari/537.36 NetType/WIFI MicroMessenger/7.0.5 WindowsWechat';
        $cookie = $this->argument('cookie');
        if (!$cookie) {
            exit('请填写用户登录凭据 cookie');
        }
        $this->cookie = urldecode($cookie);
        $token = $this->argument('token');
        if (!$token) {
            exit('请填写用户登录凭据 token');
        }
        $this->token = $token;

        if ($host = $this->option('host')) {
            $this->schemeHose = $host;
        }
        if ($slmax = $this->option('slmax')) {
            $this->sleepMax = $slmax;
        }

        $link = $this->option('link');
        $link = $link ? urldecode($link) : $link;
        if ($link && strpos(trim($link), 'http') === 0) {// 抓取一本小说
            try {
                $links = explode(',,', $link);
                foreach ($links as $link) {
                    if (empty($link) || strpos(trim($link), 'http') === false) {
                        dump('-------------------- link is error !', $link);
                        continue;
                    }
                    try {
                        $sp_nid = explode('/fiction/', $link)[1];
                        $this->novelSpider($sp_nid);
                    } catch (\Exception $e) {
                        dump('spiderANovel Exception:', $e->getLine(), $e->getMessage(), $e->getCode());
                        continue;
                    }
                }
                dd('SpiderANovel is over !', $links);
            } catch (\Exception $e) {
                dd('link Spiders DD : line = '.$e->getLine(), $e->getLine(), $e->getMessage(), $e->getCode());
            }
        }
        if ($this->option('type') == 'index') {
            $this->indexSpiders();
        } else {
            $this->typeSpiders();
        }

        exit('ALL novel roll spider !!!');
    }
    /**
     * 首页抓取
     */
    private function indexSpiders() {
        $url = $this->schemeHose . '/api/fictionEntrance';
        $header = ["token: {$this->token}", "Referer: {$this->schemeHose}/index"];
        $rel = $this->cookieCurl($url, [CURLOPT_HTTPHEADER=>$header]);

        $rel = json_decode($rel, 1);
        if (!$rel['content']) {
            dump('未抓取到首页内容！');
            return false;
        }
        $rel = $rel['content'];
        $type_list_id = $this->typeListIds();
        dump($rel, $type_list_id);
        foreach ($rel as $arr) {
            if (!is_array($arr)) continue;
            foreach ($arr as $arr2) {
                if (!is_array($arr2)) continue;
                foreach ($arr2 as $novel) {
                    if (isset($type_list_id[$novel['id']])) continue; // 列表抓取中已经在抓取了；就跳过
                    $this->spNovel = $novel;
                    $this->spideredNum++; // 增加已抓取数量
                    try {
                        $this->novelSpider($novel['id']);
                    } catch (\Exception $e) {
                        dd('indexSpiders DD : line = '.$e->getLine(), $this->spNovel['title'], $e->getMessage(), $novel);
                    }
                }
            }
        }
    }
    private function typeListIds() {
        $type_list_id = [];
        //return $type_list_id;
        $page = 1;
        while (true) {
            $url = $this->schemeHose . '/api/fictionList?pageSize=10&pageNumber='. $page .'&type=-1&status=-1&sex=-1';
            $header = ["token: {$this->token}", "Referer: {$this->schemeHose}/book"];
            $rel = $this->cookieCurl($url, [CURLOPT_HTTPHEADER=>$header]);

            $rel = json_decode($rel, 1);
            if (!$rel['content']) break;
            foreach ($rel['content'] as $novel) {
                $type_list_id[$novel['id']] = 1;
            }
            $page++;
        }
        return $type_list_id;
    }
    /**
     * 分类列表抓取
     */
    private function typeSpiders() {
        $page = 1;
        while (true) {
            $url = $this->schemeHose . '/api/fictionList?pageSize=10&pageNumber='. $page .'&type=-1&status=-1&sex=-1';
            $header = ["token: {$this->token}", "Referer: {$this->schemeHose}/book"];
            $rel = $this->cookieCurl($url, [CURLOPT_HTTPHEADER=>$header]);

            $rel = json_decode($rel, 1);
            if (!$rel['content']) break;
            foreach ($rel['content'] as $novel) {
                $this->spNovel = $novel;
                $this->spideredNum++; // 增加已抓取数量
                try {
                    $this->novelSpider($novel['id']);
                } catch (\Exception $e) {
                    dd('typeSpiders DD : line = '.$e->getLine(), $this->spNovel['title'], $e->getMessage(), $e->getLine(), $novel);
                }
            }
            $page++;
        }
    }


    private $spNovel; //当前正在抓取的小说
    // 单本小说抓取
    // $url 抓取小说的url    $sp_nid 是劲爆那边的小说ID
    private function novelSpider($sp_nid) {
        try {
            $novel = $this->getMyNovel($sp_nid); // 添加/查询小说信息
        } catch (\Exception $e) {
            dump('novelSpider 小说信息获取失败！继续抓取下一本', $e->getMessage());
            return false;
        }
        dump($novel, $this->spNovel);
        if (!$novel) return false;

        $this->sp_section_offset = 0;
        $start_sp = false;
        $section = $this->startSpiderSection($novel, $sp_nid); // 查询开始抓取章节
        if ($section['title'] == '') $start_sp = true;
        while (true) {
            $list = $this->sectionList($sp_nid);
            if (!$list) break;
            foreach ($list as $item) {
                if ($start_sp) {
                    list($content, $spider_url) = $this->sectionContent($sp_nid, $item['id']);
                    if ($content === null) {
                        throw new \Exception('小说章节内容抓取失败！');
                    }

                    $title = trim($item['title']);
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
                    sleep(mt_rand(1, $this->sleepMax)); // 一章读 5-15 秒钟
                } else {
                    if (trim($item['title']) == $section['title']) {
                        $start_sp = true;
                    }
                }
            }
        }
        // 更新novel数据
        $novel_up = ['sections'=>$section['num']];
        if (isset($spider_url) && $spider_url) {
            $novel_up['spider_url'] = $spider_url;
        }
        if ($section['num'] > 20 && $novel['status'] == 0) $novel_up['status'] = 1;
        $this->novels->update($novel_up, $novel['id']);// 更新小说总章节数据
    }
    // 查询小说是否存在；没有就获取并添加小说
    private function getMyNovel($sp_nid, $re = 0) {
        $url = $this->schemeHose . '/fiction/' . $sp_nid;
        $header = ["Referer: {$this->schemeHose}/book"];
        $this->cookieCurl($url, [CURLOPT_HTTPHEADER=>$header]); //模拟请求详情页

        $url = $this->schemeHose . '/api/FictionDetails?fictionid=' . $sp_nid;
        $header = ["token: {$this->token}", "Referer: {$this->schemeHose}/fiction/" . $sp_nid];
        $rel = $this->cookieCurl($url, [CURLOPT_HTTPHEADER=>$header]);

        $rel = json_decode($rel, 1);
        if (!isset($rel['content']) || !$rel['content']) {
            dump($rel);
            if ($re < 5) {
                sleep(mt_rand(1, $this->sleepMax));
                return $this->getMyNovel($sp_nid, ++$re);
            }
            throw new \Exception('小说信息抓取失败！', 2000);
        }
        $rel = $rel['content'];

        $data['spider_url'] = $url;
        $data['title']  = $rel['title'];
        if (in_array($data['title'], $this->noSpiderTitles)) return null;
        list($data['author_id'], $data['author_name']) = $this->novelAuthor($rel['author']);

        if ($had = $this->novels->findByMap([
            ['title', $data['title']],
            ['author_id', $data['author_id']],
            ['origin_web', $this->novelWeb],
        ], ['id', 'sections', 'spider_url', 'serial_status', 'status'])) {
            $had['spider_url'] = $data['spider_url'];
            return $had;
        }

        $data['desc']   = $rel['introduction'];
        $data['tags']   = $this->novelTags();
        $data['word_count'] = $this->novelWordCount();
        list($data['type_ids'], $data['serial_status']) = $this->novelType2SerStatus($rel);
        $data['img']    = $this->uploadImg($rel['img'], $data['type_ids']);
        $data['need_buy_section'] = 9;
        $data['subscribe_section'] = 8;
        $data['status'] = 0;
        $data['suitable_sex'] = mt_rand(1, 2);
        $data['origin_web'] = $this->novelWeb;

        $in = $this->novels->create($data);
        return ['id'=>$in['id'], 'sections'=>0, 'spider_url'=>$data['spider_url'], 'status'=>0, 'serial_status'=>$data['serial_status']];
    }
    // 获取抓取的开始章节；
    // $novel 我们数据库里面的小说信息；$sp_nid 抓取的小说ID
    private function startSpiderSection($novel, $sp_nid) {
        // 查询我们数据里面的最大章节
        $section = $this->novelSections->findByCondition([['novel_id', $novel['id']]], ['num', 'novel_id', 'title', 'spider_url'], ['num', 'desc']);
        if ($section) {
            $section = $this->novelSections->toArr($section);
            $section['sp_sid']  = 0;
        } else {
            $section['num']     = 0;
            $section['novel_id']= $novel['id'];
            $section['title']   = '';
            $section['sp_sid']  = '';
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
        $num = mt_rand(10, 100);
        return $num;
    }
    // 小说类型和连载状态
    private function novelType2SerStatus($rel) {
        $ser_type = $rel['wanjie'] == 1 ? 2 : 1;
        $type_ids = $this->typeIdsVal();

        return [$type_ids, $ser_type];
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
    private function novelAuthor($author_name) {
        $author = $this->authors->findBy('name', $author_name, ['id', 'name']);
        if (!$author) {
            $author = $this->authors->create(['name'=>$author_name, 'status'=>1]);
        }
        $author_id = $author['id'];

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
    private function sectionList($sp_nid) {
        $url = $this->schemeHose . '/api/FictionMenu?fictionid='. $sp_nid .'&offset='. $this->sp_section_offset;
        $header = ["token: {$this->token}", "Referer: {$this->schemeHose}/fullCatalog/{$sp_nid}"];
        $data = $this->cookieCurl($url, [CURLOPT_HTTPHEADER=>$header]);
        $data = json_decode($data, 1);
        $this->sp_section_offset++; // 章节目录第几页
        if (isset($data['content']['tabLists']))
            return $data['content']['tabLists'];
        else
            return [];
    }
    // 章节正文内容获取和下一章地址获取
    // $section_id 章节ID
    private function sectionContent($sp_nid, $section_id, $re = 0) {
        //http://t12734755.uocgsi.cn/api/fictionDetailsText?fictionid=9c2754b8e12760af10bee015986a0f7a&fictionPage=0352573127ee5a93a17808161ef4c56f&t=&lkk=33

        $header = ["Referer: {$this->schemeHose}/fiction/{$sp_nid}/{$section_id}?lkk=33&t="];
        $url = "{$this->schemeHose}/fiction/{$sp_nid}/{$section_id}?t=&lkk=33";
        $this->cookieCurl($url, [CURLOPT_HTTPHEADER=>$header]);// 详情页壳子
        $header = ["token: {$this->token}", "Referer: {$this->schemeHose}/fiction/{$sp_nid}/{$section_id}?lkk=33&t="];
        $url = $this->schemeHose . '/api/watermark';
        $this->cookieCurl($url, [CURLOPT_HTTPHEADER=>$header]); // 水印接口
        $url = $this->schemeHose . '/api/fictionDetailsText?fictionid=' . $sp_nid . '&fictionPage=' . $section_id . '&t=&lkk=33';
        $rel = $this->cookieCurl($url, [CURLOPT_HTTPHEADER=>$header]); // 正文内容

        $rel = json_decode($rel, 1);
        if (!isset($rel['content']) || !$rel['content'] || !isset($rel['content']['detail']) || !$rel['content']['detail']) {
            if ($re < 5) {
                dump('spider content null!', $rel);
                sleep(mt_rand(1, $this->sleepMax));
                return $this->sectionContent($sp_nid, $section_id, ++$re);
            }
            return [null, $url];
        }
        $content = trim($rel['content']['detail']);
        if (substr($content, 0, 3) != '<p>') {
            $content = '<p>' . $content;
        }
        if (substr($content, -4, 4) != '</p>') {
            $content = $content . '</p>';
        }

        return [$content, $url];;
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
        '都市'    => 3,
        '励志'    => 15,
        '同人'    => 8,
    ];
    private function typeIdsVal() {
        // 实例化小说分类
        return $this->novelTypes[array_rand($this->novelTypes)];
    }
}
