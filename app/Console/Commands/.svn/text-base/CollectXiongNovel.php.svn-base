<?php
/**
 * niuniutui 暴走小说 公众号《峦周书城》 小说采集的命令
 *
 * 运行命令
 * php artisan novel:collect-xiong-novel 玄幻 urlencode(cookie)
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
php artisan novel:collect-xiong-novel 玄幻 PHPSESSID%3dosiod8f6qgfpdj7bbppjofko4h%3b+Hm_lvt_fcd37298453821c3bc8b266221db88ac%3d1575882958%2c1575885839%3b+Hm_lvt_84bbc585ea2e33b0c5a894bab2c53ab4%3d1575882932%2c1575885839%2c1575886617%2c1575886634%3b+user%3d%257B%2522nickname%2522%253A%2522%255Cu662f%255Cu54e6%2522%252C%2522img%2522%253A%2522http%253A%255C%252F%255C%252Fthirdwx.qlogo.cn%255C%252Fmmopen%255C%252Fvi_32%255C%252FDYAIOgq83eqoicKy5qiboP1kZJdhOyibBDghj29c1DGNuGkRg7LS3cIDZu5VLwmt2M4Y2z1HOKHUnOrXmibuQyuw3g%255C%252F132%2522%252C%2522open_id%2522%253A%2522oH3xe1JpIstLCIYXTUg4YO1tyZQk%2522%252C%2522sex%2522%253A%25221%2522%252C%2522domain%2522%253A%2522lyksrz.chuanyistar.com%2522%252C%2522addtime%2522%253A%25221576058638%2522%252C%2522open_id_pre%2522%253A%2522oH3xe1%2522%257D%3b+suid%3d1108881%3b+Hm_lpvt_84bbc585ea2e33b0c5a894bab2c53ab4%3d1575945002%3b+Hm_lpvt_fcd37298453821c3bc8b266221db88ac%3d1575945002
 *
 */

namespace App\Console\Commands;

use App\Logics\Repositories\src\AuthorRepository;
use App\Logics\Repositories\src\NovelRepository;
use App\Logics\Repositories\src\NovelSectionRepository;
use App\Logics\Traits\RedisCacheTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class CollectXiongNovel extends Command
{
    use RedisCacheTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     */
    protected $signature = 'novel:collect-xiong-novel {type} {cookie}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'niuniutui.com 小说采集程序；order is "novel:collect-niuniutui {type} {urlencode(cookie)}"';

    protected $cookie = 'PHPSESSID=tppucefh1ms7bj2nusqev1o82o; UM_distinctid=16f042c17bf142-0b7c85085eb44c-1a053053-448e0-16f042c17c0211; CNZZDATA1275384399=864687622-1576320699-%7C1576320699';
    protected $novelTypes;  // 小说分类信息
    protected $sexes;  // 小说性别分类
    protected $serialStatus; // 连载状态
    protected $schemeHose;  // 协议和域名


    private $authors;
    private $novels;
    private $novelSections;
    private $spidered = 0;// 抓取了几章了

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->authors = new  AuthorRepository();
        $this->novels = new NovelRepository();
        $this->novelSections = new NovelSectionRepository();
        $this->initConfig(); // 实例化配置
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->argument('type');
        // $max  = $this->argument('max');
        $cookie = $this->argument('cookie');
        if (!$cookie) {
            exit('请填写用户登录凭据 cookie');
        }
        $this->cookie = urldecode($cookie);

        $types = $this->classifyData(); // 获取分类列表
        foreach ($types as $k=>$v) {
            if($v == $type) {
                $this->typeSpider($k); // 抓取指定分类数据
                $relStr = '-----------------------------------------------\r\n
                ------------------------'.$type . ' spider over !-----------------------\r\n
                -----------------------------------------------\r\n';
                exit($relStr);
            }
        }

        if ($type == 'all') {
            // 一直循环抓取小说
            $type_ids = [];
            foreach ($types as $k=>$v) {
                $type_ids[] = $k;
            }
            $i = 0;
            while (true) {
                $this->typeSpider($type_ids[$i]);
                $i++;
                if (!isset($type_ids[$i]) || count($type_ids) == $i) {
                    $i = 0;
                    $this->heartbeat(); // 抓取一轮之后，执行心跳操作，然后过2小时再抓取
                }
            }
            exit('ALL novel roll spider !!!');
        }


        // 循环抓取分类数据；所有分类抓完就结束
        foreach ($types as $k=>$v) {
            $this->typeSpider($k);
        }
        $relStr = '-----------------------------------------------\r\n
                ------------------------ All types spider over ! -----------------------\r\n
                -----------------------------------------------\r\n';
        exit($relStr);
    }
    /**
     * 抓取指定类型的小说
     * @param int $type_id
     */
    private function typeSpider($type_id) {
        $p = 0;
        while (true) {
            try {
                $typeListLinks = $this->typesListLink($type_id, ++$p); // 按分类分页获取详情页链接
                if (!$typeListLinks) break;
            } catch (\Exception $e) {
                dump(
                    date('Y-m-d H:i:s'),
                    $e->getMessage(),
                    'error line : ' . $e->getLine(),
                    '--------------------------------------------------'
                );
                break;
            }

            foreach ($typeListLinks as $v) {
                try {
                    list($detail, $mulu) = $this->novelPageHtml($v);
                    $novel = $this->addNovelInfo($detail); // 查询添加小说数据
                    list($section, $spider_url, $msg) = $this->spiderSectionInfo($novel, $mulu);// 抓取章节数据；抛出异常就不能更新总章节数
                    dump(
                        date('Y-m-d H:i:s'),
                        '抓取章节数据完成//异常 section-num = '.$section,
                        $msg,
                        ' === url === ' . $v,
                        '--------------------------------------------------'
                    );

                    // 更新novel数据
                    $novel_up = ['sections'=>$section, 'spider_url'=>$spider_url];
                    if ($section > 20) $novel_up['status'] = 1;
                    $this->novels->update($novel_up, $novel['id']);// 更新小说总章节数据

                    Log::info('niuniutui-spider-info: ' . $detail['title'] . '_' . $type_id . '___' . $msg);
                } catch (\Exception $e) {
                    dump(
                        date('Y-m-d H:i:s'),
                        $e->getMessage(),
                        'error line : ' . $e->getLine(),
                        ' === url === ' . $v,
                        '--------------------------------------------------'
                    );
                    continue;
                }
            }
        }
    }

    /**
     * 配置信息
     */
    private function initConfig() {
        // 实例化小说分类
        $this->novelTypes = [
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

        $this->sexes = [
            '未知'    => 0,
            '男生'    => 1,
            '女生'    => 2,
        ];

        $this->serialStatus = [
            '连载中'    => 1,
            '已完结'    => 2,
            '限时免费'    => 3,
            '连载'    => 1,
            '完结'    => 2,
            '免费'    => 3,
            '完本'    => 2,
        ];

        $this->schemeHose = 'http://wx0e5baad403af8147.shanhukanshu.com';
    }
    /**
     * 获取分类信息数据
     */
    private function classifyData() {
        $url = $this->schemeHose . '/shuku?wx_redirect_count=1';
        $html = $this->cookieCurl($url);

        preg_match('/<span class=\"s_name\">分类<\/span>([.\s\S]*?)<\/li>/', $html, $match);

        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('分类信息字符串异常匹配！', 2000);
        }
        preg_match_all('/javascript\:set\(2\,(\d*?)\).*?>(.*?)<\/a>/', $match[1], $match);

        if (!isset($match[1]) || !$match[1] || !isset($match[2]) || !$match[2]) {
            throw new \Exception('具体分类信息异常匹配！', 2000);
        }
        foreach ($match[1] as $k=>$v) {
            //if ($v) {
            // [27 => '都市']
            $types[$v] = $match[2][$k];
            //}
        }
        return $types;
    }

    /**
     * 获取分类列表链接
     */
    private function typesListLink($tid, $p) {
        $url = $this->schemeHose . "/shuku/{$p}//{$tid}/0/2/2.html";

        $html = $this->cookieCurl($url);

        preg_match('/<div id=\"bookList\">([.\s\S]*?)<div class=\"page-turn\">/', $html, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('分类box信息异常匹配！', 2000);
        }
        preg_match_all('/<a class=\"book\-a\" href=\"(.*?)\"/', $match[1], $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('分类列表获取失败！', 2000);
        }
        return $match[1];
    }
    /**
     * 获取小说主页信息
     */
    private function novelPageHtml($url) {
        $url = $this->schemeHose . $url;
        $html = $this->cookieCurl($url);
        $rel = [];

        preg_match('/<div class="more"><a href="(.*?)"/', $html, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('目录地址异常匹配！', 2000);
        }
        $mulu = $match[1];

        $rel['spider_url'] = $this->firstSectionUrl($html); // 获取第一章阅读链接

        preg_match('/<div class="content1">([.\s\S]*?)<div class="news_content">/', $html, $match);
        $html = $match[1];

        preg_match('/<span class="content_header">.*?img src=.*?data-original="(.*?)"/', $html, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('banner异常匹配！', 2000);
        }
        $rel['img'] = $match[1];

        $rel['desc'] = strip_tags($this->matchDesc($html));

        preg_match('/<h2 class="line\_2">(.*?)<\/h2>/', $html, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('标题异常匹配！', 2000);
        }
        $rel['title'] = strip_tags(trim($match[1]));

        preg_match('/<span class="author">(.*?)<\/span>/', $html, $match);
        if (!isset($match[1]) || !$match[1]) {
            // throw new \Exception('作者异常匹配！', 2000);
            $rel['author_name'] = '宝宝疯';
            $rel['author_id'] = 1;
        } else {
            $rel['author_name'] = $match[1];
            $rel['author_id'] = $this->authors->getOrAddAuthor($rel['author_name']);
        }

        preg_match('/<p class="classify"[.\s\S]*?<span>([.\s\S]*?)<\/span>/', $html, $match);
        if (!isset($match[1]) || !$match[1]) {
            // throw new \Exception('类型异常匹配！', 2000);
            $rel['type_ids'] = current($this->novelTypes);
        } else {
            $rel['type_ids'] = $this->setType(trim($match[1]));
        }

        preg_match('/<p class="classify"><span cla.*?>([.\s\S]*?)<\/span>/', $html, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('连载状态异常匹配！', 2000);
        }
        $rel['serial_status'] = $this->serialStatus[trim($match[1])];

        preg_match('/<span class="author"style.*?\>([.\s\S]*?)万字/', $html, $match);
        if (!isset($match[1]) || !$match[1]) {
            // throw new \Exception('总字数 xxx 万字匹配异常！', 2000);
            $rel['word_count'] = mt_rand(10, 100);
        } else {
            $rel['word_count'] = trim($match[1]);
        }
        $rel['hot_num'] = mt_rand(1, 10);

        /*preg_match('/<a href="javascript:void\(0\);" class="green">([.\s\S]*?)<\/a>/', $head, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('性别异常匹配！', 2000);
        }*/
        //$rel['suitable_sex'] = $this->sexes[trim($match[1])];
        $rel['suitable_sex'] = mt_rand(1, 2);

        return [$rel, $mulu];
    }
    /**
     * 获取类型ID
     * @param string $name
     */
    private function setType($name) {
        $name = mb_substr($name, 0, 2);
        return isset($this->novelTypes[$name]) ? $this->novelTypes[$name] : current($this->novelTypes);
    }
    /**
     * 添加小说新
     */
    private function addNovelInfo($detail) {
        // $this->info(json_encode($detail, JSON_UNESCAPED_UNICODE));
        if ($had = $this->novels->findByMap([['title', $detail['title']], ['author_id', $detail['author_id']]], ['id', 'sections', 'spider_url', 'serial_status'])) {
            //if ($had = $this->novels->findBy('title', $detail['title'], ['id', 'sections', 'spider_url'])) {
            $had['spider_url'] = $detail['spider_url'];
            return $had;
        } else {
            $ext = substr($detail['img'], -4);
            if (substr($ext, 0, 1) != '.') {
                $ext = '.jpg';
            }
            $name = 'img/' . $detail['type_ids'] . date('/Ymd/His_') . RandCode(18, 12) . $ext;
            $detail['img'] = $this->uploadCloud($name, @file_get_contents($detail['img']));

            try{
                // $detail['word_count'] = mt_rand(10, 100);
                $in = $this->novels->create($detail);

                return ['id'=>$in['id'], 'sections'=>0, 'spider_url'=>$detail['spider_url']];
            } catch (\Exception $e) {
                Log::error('colect niuniutui novel: ' . $e->getMessage());
                $this->deleteCloud($detail['img']);
                throw new \Exception($e->getLine(), $e->getMessage());
            }
        }
    }
    /**
     * 上传图片或者文件到云对象存储
     * @param string $path
     * @param string $str 文件流
     */
    private function uploadCloud($path, $str, $re = 0) {
        $disk = config('filesystems.default');

        $dir = 'lm-novelspider/baozou/' . $path;

        try {
            if (!Storage::disk($disk)->put($dir, $str))
            {
                throw new \Exception('文件上传失败！', 2001);
            }

            $url = Storage::disk($disk)->url($dir);
            return $url;
        } catch (\Exception $e) {
            dump(
                date('Y-m-d H:i:s'),
                $e->getMessage(),
                'error line : ' . $e->getLine(),
                '--------------------------------------------------'
            );
            if ($re < 3) {
                $re++;
                sleep($re * 3);
                return $this->uploadCloud($path, $str, $re);
            }
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
    /**
     * 上传图片或者文件到云对象存储
     * @param string $path
     * @param string $str 文件流
     */
    private function deleteCloud($path) {
        $disk = config('filesystems.default');

        $path = substr($path, strpos($path, '.com/') + 5);

        if (!Storage::disk($disk)->delete($path))
        {
            throw new \Exception('文件删除失败！', 2001);
        }
    }
    /**
     * 跳转到这部小说之前采集的最后一章
     */
    private function jumpToSpiderEnd($mulu, $title) {
        $html = $this->cookieCurl($this->schemeHose . $mulu);
        if (strpos($html, $title) === false) { // 没有找到 $title 就继续查询下一页数据寻找 $title
            preg_match('/<div class="but wandu_wap_bgcolor">[.\s\S]*?<div class="but wandu_wap_bgcolor"><a href="(.*?)">下一页/', $html, $match);
            if (!isset($match[1]) || !$match[1]) {
                throw new \Exception('目录下一页获取失败！------- '.$title);
            }
            return $this->jumpToSpiderEnd($match[1], $title);
        }
        $arr = explode($title, $html);
        $arr = explode('</li><li>', $arr[0]);
        preg_match('/data-url="(.*?)"/', end($arr), $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('章节链接获取失败', 2000);
        }
        return $match[1];
    }
    private function spiderSectionInfo($novel, $mulu) {
        $section = $this->novelSections->findByCondition([['novel_id', $novel['id']]], ['num', 'novel_id', 'title', 'spider_url'], ['num', 'desc']);
        if ($section) {
            $section = $this->novelSections->toArr($section);
            $novel['spider_url'] = $this->jumpToSpiderEnd($mulu, $section['title']);
        } else {
            $section['num'] = 0;
            $section['novel_id'] = $novel['id'];
            $section['title'] = null;
        }
        $section_had = true;
        while (true) {
            $old_title = $section['title'];
            $url = $this->schemeHose . $novel['spider_url'];
            $html = $this->cookieCurl($url);

            preg_match('/<p class="title" style="text\-indent.*?\>([.\s\S]*?)<\/p>/', $html, $match);
            if (!isset($match[1]) || !$match[1]) {
                return [$section['num'], $novel['spider_url'], '章节title获取失败' . $url];
            }
            if ($section_had) {
                // 查询是否已经存在了当前章节
                if ($had = $this->novelSections->findByMap([
                    ['novel_id', $novel['id']],
                    ['title', $match[1]],
                ], ['id', 'spider_url'])) {
                    if ($had) {
                        // 该章节已存在；则获取文章下一章地址
                        $match[1] = $this->matchNextSectionUrl($html);
                        if (!isset($match[1]) || !$match[1]) {
                            return [$section['num'], $novel['spider_url'], 'section_had_true_下一章链接获取失败！' . $url];
                        }
                        $novel['spider_url'] = $match[1];
                    }
                    continue;
                } else {
                    $section_had = false;
                }
            }
            $section['title'] = trim(strip_tags($match[1]));

            preg_match('/(<div class="content" id="idcontent"[.\s\S]*?<\/div>)<div class="reading\-footer">/', $html, $match);
            if (!isset($match[1]) || !$match[1]) {
                return [$section['num'], $novel['spider_url'], '章节内容获取失败' . $url];
            }
            $content = $match[1];
            $path = 'html/' . $novel['id'] . date('/Ymd/') . RandCode(18, 12) . '.html';
            $section['content'] = $this->uploadCloud($path, $content);
            $section['num'] = $section['num'] + 1;

            if ($section['title'] != $old_title) {
                $section['spider_url'] = $url;
                // $this->info(json_encode($section, JSON_UNESCAPED_UNICODE));
                $this->novelSections->create($section);

                // 增加抓取章节，随机睡眠时间，防止标记机器人
                $this->spidered++;
                sleep(mt_rand(5, 40)); // 抓取一章后休息一会，模拟阅读；防止被标记为机器人
                if ($this->spidered > 200) { // 看了 200 章就去上个厕所回来再看
                    $this->spidered = 0;
                    sleep(mt_rand(3, 15) * 60);
                }
            }

            $match[1] = $this->matchNextSectionUrl($html);
            if (!isset($match[1]) || !$match[1]) {
                return [$section['num'], $novel['spider_url'], '下一章 link 获取失败' . $url];
            }
            $novel['spider_url'] = $match[1];
        }
    }
    /**
     * 获取阅读链接
     * 第一章阅读链接
     */
    private function firstSectionUrl($html) {
        /*preg_match('/作品目录<\/a>[.\s\S]*?<a href="([.\s\S]*?)"/', $html, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('阅读链接异常匹配！', 2000);
        }*/
        preg_match('/<h2>目录<\/h2>[.\s\S]*?<a href="([.\s\S]*?)"/', $html, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('第一章阅读链接异常匹配！', 2000);
        }

        return trim($match[1]);
    }
    /**
     * 带cookie 的 curl 抓取
     */
    private function cookieCurl($url, $option = [], $params = [], $type = 'url', $re = 0){
        try {
            return $this->doCookieCurl($url, $option, $params, $type);
        } catch (\Exception $e) {
            dump(
                date('Y-m-d H:i:s'),
                $e->getMessage(),
                'error line : ' . $e->getLine(),
                '--------------------------------------------------'
            );
            if ($re < 3) {
                $re++;
                sleep($re * 4);
                return $this->cookieCurl($url, $option, $params, $type, $re);
            }
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
    /**
     * 执行带cookie 的 curl 抓取
     */
    private function doCookieCurl($url, $option = [], $params = [], $type = 'url'){
        $strCookie = $this->cookie . '; path=/';
        if (!isset($option[CURLOPT_POST]) && $params) {
            $option[CURLOPT_POST] = 1;
        }
        $option[CURLOPT_COOKIE] = $strCookie;
        if ($params && $type) {
            if ($type == 'url') {
                $params = http_build_query($params);
            } else {
                $params = json_encode($params, JSON_UNESCAPED_UNICODE);
            }
            $option[CURLOPT_POSTFIELDS] = $params;
        }
        if (strpos($url, 'https:') !== false) {
            $option[CURLOPT_SSL_VERIFYPEER] = false;
            $option[CURLOPT_SSL_VERIFYHOST] = false;
        }
        $option[CURLOPT_URL] = $url; //请求url地址
        $option[CURLOPT_FOLLOWLOCATION] = TRUE; //是否重定向
        $option[CURLOPT_MAXREDIRS] = 4; //最大重定向次数
        $option[CURLOPT_RETURNTRANSFER] = 1; //是否将结果作为字符串返回，而不是直接输出
        $option[CURLOPT_TIMEOUT] = 15;
        $option[CURLOPT_USERAGENT] = UserAgent('magic2Wechat');
        // $option[CURLOPT_REFERER] = $refer;
        $ch = curl_init();
        curl_setopt_array($ch, $option);
        $rel = curl_exec($ch);
        $curl_no = curl_errno($ch);
        $curl_err = curl_error($ch);
        curl_close($ch);

        return $rel;
    }
    /**
     * 匹配小说简介 novel.desc
     */
    private function matchDesc($html) {
        $str = '';

        if (!$str) {
            preg_match('/<a href="javascript:;" data-act="text_flod">([.\s\S]*?)<font color="#3B80E3">/', $html, $match);
            if (isset($match[1]) && $match[1]) {
                $str = mb_substr(trim(strip_tags($match[1])), 0, 252);
            }
        }

        if (!$str) {
            preg_match('/<p class="bh_book_title_show".*?>([.\s\S]*?)<\/p>/', $html, $match);
            if (isset($match[1]) && $match[1]) {
                $str =  mb_substr(trim(strip_tags($match[1])), 0, 252);
            }
        }

        if (!$str) {
            preg_match('/<p class="bh_book_title_hide".*?>([.\s\S]*?)<\/p>/', $html, $match);
            if (isset($match[1]) && $match[1]) {
                $str = mb_substr(trim(strip_tags($match[1])), 0, 252);
            }
        }

        if ($str) {
            if (strlen($str) > 600) {
                $str = mb_substr($str, 0, 199);
            }
            return $str;
        }

        throw new \Exception('简介异常匹配！', 2000);
    }
    /**
     * 匹配小说下一章链接 section.spider_url
     */
    private function matchNextSectionUrl($html) {
        preg_match('/id="next" name="next" value="(.*?)[\'\"]/', $html, $match);
        if (!isset($match[1]) || !$match[1]) {
            return null;
            // throw new \Exception('下一章 link 获取失败', 2000);
        }
        return $match[1];
    }
    /**
     * 执行心跳操作
     */
    private function heartbeat() {
        $start = time();
        while (true) {
            if ((time() - $start) > 7200) break; // 超过2小时了；就跳出循环继续执行抓取
            $second = 60 * mt_rand(1, 8);
            dump(
                date('Y-m-d H:i:s'),
                'heartbeat start = '.$start. ' ; now = '. time(),
                'heartbeat sleep : ' . $second . ' 秒',
                '-------------------------------------------------------'
            );
            sleep($second);
            $this->classifyData();
        }
        return $start;
    }
    /**
     * 更新总的章节数
     */
    private function updateNovelSectionsNum() {
        $novels = $this->novels->model->select(['id', 'sections'])->get();
        foreach ($novels as $novel) {
            $section = $this->novelSections->findByCondition([
                ['novel_id', $novel['id']],
            ], ['num'], ['num', 'desc']);

            if ($section && $section['num'] != $novel['sections']) {
                $novel->sections = $section['num'];
                $novel->save();
            }
        }
    }
}
