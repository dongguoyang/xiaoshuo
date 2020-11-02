<?php
/**
 * 笔趣阁 wap 章小说采集的命令
 *
 * 运行命令
 * php artisan novel:collect-biquge-wap urlencode(cookie)     --host= // 采集玄幻分类
 * php artisan novel:collect-biquge-wap asdidkd%3d1%3b+Hm_lvt_447fadd6bad32bfe438b95a891015d5b%3d1593164267%3b+Hm_lpvt_447fadd6bad32bfe438b95a891015d5b%3d1593164381 --host=https://wap.biqiuge.com --link=https://wap.biqiuge.com/book_44988 --type_id=19  --suitable_sex=1
php artisan novel:collect-changdu-zhongwen  --host=https://sitel5mowkgk15re0qx9.iycdm.com
 *
 */

namespace App\Console\Commands;

use App\Logics\Traits\CollectTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Logics\Repositories\src\TypeRepository;


class CollectBiqugePageNovel extends Command
{
    use CollectTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     */
    protected $signature = 'novel:collect-biquge-wap-page {cookie} {--S|suitable_sex= : 抓取性别分类 male、female} {--H|host= : 指定采集的域名} ';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '笔趣阁wap站的小说采集程序';
    private $host2cookie = []; // host cookie 列表对应
    protected $cookie; // 0 不需要收费的cookie     1 需要收费的cookie
    protected $novelWeb = 'biquge';
    private $sleepMax = 5; // 章节抓取间隔最大时间
    private $linkSp = 0;
    private $exptSpIds = []; // 排除抓取的 id 列表
    private $type_id; // 设置的分类id
    private $suitable_sex; // 设置的适用类型
    protected $noSpiderTitles = [
        '登峰造极'
    ];
    protected $type_model;
    // 转换类型
    protected $novelTypes = [
    ];
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->type_model = new TypeRepository();
        parent::__construct();
        $this->initCollect();
    }
    // 设置 host 和 cookie
    private function setHost2Cookie($link = '') {
        if ($link) {
            foreach ($this->host2cookie as $host => $cookie) {
                if (strpos($link, $host) !== false) {
                    $this->cookie = $cookie;
                    $this->schemeHose = $host;
                    break;
                }
            }
        } else {
            $cookie = $this->argument('cookie');
            if (!$cookie) {
                exit('请填写用户登录凭据 cookie');
            }
            $cookie = explode('|||||', urldecode($cookie));
            $host = $this->option('host');
            if (!$host) {
                exit('请填写 host 列表');
            }
            $host = explode('|||||', urldecode($host));
            foreach ($host as $k => $v) {
                $this->host2cookie[$v] = $cookie[$k];
            }
            $this->schemeHose = key($this->host2cookie);
            $this->cookie = current($this->host2cookie);
        }
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setHost2Cookie(); // 重置cookie host
        $this->nonelList();
    }

    private function nonelList($page = 18){
        while ($page < 50) {
            //小说列表
            $url = '/wapfull/'.$page.'.html';
            $rel = $this->cookieCurl($this->schemeHose . $url);
            $this->spidedHtml = mb_convert_encoding($rel, 'UTF-8', 'GB2312, GBK');
            if (!$this->spidedHtml) break;
            //<div class="page-book-turn">第[.\d]*?/([.\d]*?)页[([.\d]*?)章\/页].*?<\/div>
            preg_match_all('/<td[^>]*><a.*?href=\"(.*?)\">.*?<\/a><\/td>/', $this->spidedHtml, $match);
            if($match){
                $book_id = 0;
                foreach ($match[1] as $value){
                    $value = trim($value, "/");
                    if(strstr($value,'book_')){
                        $book_id = $value;
                    }
                    try {
                        if($book_id){
                            $this->novelSpider($book_id);
                            exit();
                        }
                    } catch (\Exception $e) {
                        dump('spiderANovel Exception:', $e->getLine(), $e->getMessage(), $e->getCode());
                        continue;
                    }
                }
            }
            $page++;
        }
    }

    private $spNovel; //当前正在抓取的小说
    // 单本小说抓取
    // $url 抓取小说的url
    private function novelSpider($book_id) {
        $novel = $this->getMyNovel($book_id); // 添加/查询小说信息
        if (!$novel) return false;
        $section = $this->startSpiderSection($novel); // 查询开始抓取章节
        if (!isset($section['num'])) {
            dump('没有获取到章节！');
            return false;
        }
        $book_id = explode('book_',$book_id);
        $book_id = $book_id[1];
        $page = 1;
        $star_content = $section['num'] ? false : true;
        $break_while = false;
        $spider_url = '';
        while (true) {
            //https://m.52bqg.net/chapters_84747/2 第二页
            if ($page > 1) {
                $url = 'chapters_'. $book_id .'/'. $page;
            } else {
                $url = 'chapters_'. $book_id;
            }
            $url = $this->schemeHose . $url;
            $rel = $this->cookieCurl($url);
            $rel = mb_convert_encoding($rel, 'UTF-8', 'GB2312, GBK');
            preg_match_all('/<a href=\"\/book_'.$book_id.'\/([.\d]*?).html\">([.\s\S]*?)<\/a>/s', $rel, $match);
            if (!isset($match[1]) || !$match[1]) {
                throw new \Exception('章节列表异常匹配！', 2000);
            }
            //判断是否大于200章
            preg_match('/<div class="page-book-turn">第([.\d]*?)\/([.\d]*?)页\[([.\d]*?)章\/页\][.\s\S]*?<\/div>/', $rel, $number);
            if (!isset($number[1]) || !$number[1]) {
                throw new \Exception('章节列表异常匹配111！', 2000);
            }
            if($number[2]*30 < 200){
                break;
            }
            $list = $match[1];
            $list_title = $match[2];
            if (count($list) <= 1) {
                break;
            }
            foreach ($list as $itk => $item) {
                $title = strip_tags(TrimAll($list_title[$itk]));
                if (!$star_content) {
                    if ($section['title'] == $title) {
                        $star_content = true;
                    }
                    continue;
                }
                //preg_match('/<a href =[\'\"](.*?)[\'\"]/', $item, $match);
                //if (!isset($match[1]) && !isset($list[$itk + 1])) break;
                if (!$title) {
                    dump('$title null 小说最新章节信息抓取完成！');
                    $break_while = true;
                    break;
                }

                $section['sp_num']++;
                $content_url = 'book_'.$book_id.'/'.$item.'.html';
                list($content, $spider_url) = $this->sectionContent($content_url);
                if (!$this->novelSections->findByMap([
                    ['novel_id', $novel['id']],
                    ['title', $title],
                    ['spider_url', $spider_url],
                ], ['id', 'spider_url'])) {
                    $section['num']++;
                    $data['novel_id']   = $novel['id'];
                    $data['num']        = $section['num'];
                    $data['title']      = $title;
                    $path = 'html/' . $novel['id'] . date('/Ymd/His') . RandCode(5, 12) . '.html';
                    $data['content']    = $this->uploadCloud($path, $content);
                    $data['spider_url'] = $spider_url;
                    $this->novelSections->create($data);
                }
                sleep(mt_rand(1, $this->sleepMax)); // 一章读 5-15 秒钟
            }
            if ($break_while) break;
            $page++;
        }
        // 更新novel数据
        $novel_up = ['sections'=>$section['num'], 'spider_url'=>$spider_url];
        if ($section['num'] > 20 && $novel['status'] == 0) $novel_up['status'] = 1;
        $this->novels->update($novel_up, $novel['id']);// 更新小说总章节数据
    }
    // 查询小说是否存在；没有就获取并添加小说
    private function getMyNovel($url, $re = 0) {
        $header = [
            "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36",
            "Referer: https://m.52bqg.net/",
            'X-FORWARDED-FOR: '.$this->Rand_IP(),
            'CLIENT-IP: '.$this->Rand_IP(),
        ];
        $rel = $this->cookieCurl($this->schemeHose.$url,[CURLOPT_HTTPHEADER=>$header]);
        $this->spidedHtml = mb_convert_encoding($rel, 'UTF-8', 'GB2312, GBK');
        $data['spider_url'] = $url;
        list($data['title'], $data['author_id'], $data['author_name'], $data['type_ids'], $data['serial_status'])  = $this->novelTitle2Author();
        if (strlen($data['author_name']) <= 2 && $data['author_name'] != '.') return null; // 作者都没得；后面又抓出来重复了
        $data['suitable_sex'] = 1;
        $data['word_count'] = $this->novelWordCount();
        if ($had = $this->novels->findByMap([
            ['title', $data['title']],
            ['author_id', $data['author_id']],
            ['origin_web', $this->novelWeb],
        ], ['id', 'sections', 'spider_url', 'serial_status', 'status', 'suitable_sex', 'word_count', 'type_ids', 'serial_status'])) {
            $had['spider_url'] = $data['spider_url'];
            if ($had['word_count']!=$data['word_count'] ||
                $had['type_ids']!=$data['type_ids'] ||
                $had['serial_status']!=$data['serial_status']
            ) {
                $this->novels->update($data, $had['id']);
            }
            return $had;
        }
        $data['desc']   = $this->novelDesc();
        $data['tags']   = $this->novelTags();
        $data['img'] = $this->novelImg();
        $data['img']    = $data['img'] ? $this->uploadImg($data['img'], $data['type_ids']) : 'https://ccccccc1111111.oss-cn-hangzhou.aliyuncs.com/lm-novelspider/baozou/img/6/20200114/165442_ou3o2nknbr91m792ep.jpg';//$this->uploadImg($this->novelImg(), $data['type_ids']);
        $data['need_buy_section'] = 10;
        $data['subscribe_section'] = 8;
        $data['status'] = 0;
        $data['origin_web'] = $this->novelWeb;
        $in = $this->novels->create($data);
        return ['id'=>$in['id'], 'sections'=>0, 'spider_url'=>$data['spider_url'], 'status'=>$data['status'], 'serial_status'=>$data['serial_status']];
    }
    // 小说封面图
    private function novelImg() {
        //<td><img src=\"([.\s\S]*?)\" border="0" width='100' height='130'\/><\/td>
        preg_match('/<td><img src=\"([.\s\S]*?)\" border="0" width=\'100\' height=\'130\'\/><\/td>/', $this->spidedHtml, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('图片异常匹配！', 2000);
        }
        $img = $match[1];
        return $img;
    }
    // 小说标题
    private function novelTitle2Author() {
        preg_match('/<table cellpadding="0" cellspacing="0">([.\s\S]*?)<\/table>/s', $this->spidedHtml, $match);
        if (!isset($match[0]) || !$match[0]) {
            throw new \Exception('章节内容异常匹配！', 2000);
        }
        $temp_html = $match[0];
        preg_match('/<p><strong>(.*?)<\/strong><\/p>/', $temp_html, $match1);
        if (!isset($match1[1]) || !$match1[1]) {
            throw new \Exception('标题异常匹配-2！', 2000);
        }
        $title = $match1[1];
        preg_match('/<p>作者：<a href=\".*\">(.*?)<\/a><\/p>/', $temp_html, $match2);
        if (!isset($match2[1]) || !$match2[1]) {
            $author_name = '.';
            $author_id   = 1;
        } else {
            $author_name = $match2[1];
            $author = $this->authors->findBy('name', $author_name, ['id', 'name']);
            if (!$author) {
                $author = $this->authors->create(['name'=>$author_name, 'status'=>1]);
            }
            $author_id = $author['id'];
        }
        preg_match('/<p>类别：<a href=\"\/wapsort\/.*\">(.*?)<\/a><\/p>/', $temp_html, $match3);
        if (!isset($match3[1]) || !$match3[1]) {
            $type = 3;
        }else{
            $name = $match3[1];
            $type = $this->type_model->findBy('name', $name, ['id', 'name']);
            if (!$type) {
                $type = $this->type_model->create(['name'=>$name, 'pid'=>1,'level'=>2,]);
            }
            $type = $type['id'];
        }
        $serial_status = 2;
        //dd($title, $author_id, $author_name, $type, $serial_status);
        return [$title, $author_id, $author_name, $type, $serial_status];
    }
    // 小说简介
    private function novelDesc() {
        preg_match('/<div class=\"intro\">(.*?)<\/div>/', $this->spidedHtml, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('小说简介信息匹配异常！', 2000);
        }
        return trim(strip_tags($match[1]));
    }
    // 获取作者信息
    private function novelAuthor() {
        if (isset($this->spNovel['book_author']) && $this->spNovel['book_author']) {
            $author_name = $this->spNovel['book_author'];
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
    // 获取抓取的开始章节；
    // $novel 我们数据库里面的小说信息；
    private function startSpiderSection($novel) {
        // 查询我们数据里面的最大章节
        $section = $this->novelSections->findByCondition([['novel_id', $novel['id']]], ['num', 'novel_id', 'title', 'spider_url'], ['num', 'desc']);
        if ($section) {
            $section = $this->novelSections->toArr($section);
            $section['sp_num'] = $section['num'];
        } else {
            $section['num']     = 0;
            $section['sp_num'] = $section['num'];
            $section['novel_id']= $novel['id'];
            $section['title']   = null;
            $section['spider_url']   = '';
        }

        return $section;
    }
    // 小说总字数
    private function novelWordCount() {
        preg_match('/<span>字数：(.*?)<\/span>/', $this->spidedHtml, $match);
        if (!isset($match[1]) || !is_numeric($match[1])) {
            $num = mt_rand(1, 20);
        } else {
            $num = $match[1];
        }
        if ($num < 1) {
            if ($num <= 0) {
                $num = mt_rand(1, 9);
            }
            while (true) {
                $num *= 10;
                if ($num > 10) break;
            }
        }

        return $num;
    }

    // 章节正文内容获取和下一章地址获取
    // $section_id 章节ID
    private function sectionContent($url) {
        $rel = $this->cookieCurl('https://www.52bqg.net/'. $url);
        $rel = mb_convert_encoding($rel, 'UTF-8', 'GB2312, GBK');
        preg_match('/<div id="content" name="content">([.\s\S]*?)<\/div>/s', $rel, $match);
        if (!isset($match[1]) || !$match[1]) {
            throw new \Exception('章节内容异常匹配！', 2000);
        }
        $title = '';
        $content = trim($match[1]);
        $content = str_replace('一秒记住【笔趣阁 www.52bqg.net】，精彩小说无弹窗免费阅读！','',$content);
        //去除笔趣阁广告
        $arr = explode("<br />", $content);
        $content2 = '';
        foreach ($arr as $p) {
            $p = trim($p);
            $p = str_replace('&nbsp;', '', $p);
            if (empty($p)) continue;
            if (!$title) {
                $title = $p;
                continue;
            }
            $content2 .= '<p>'. $p . '</p>';
        }
        return [$content2, $url];
    }
    private function typeIdsVal($tid, $sex) {
        // 实例化小说分类
        if (is_numeric($tid) && isset($this->novelTypes[$tid])) return $this->novelTypes[$tid];
        return $this->novelTypes[$sex];
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

        $name = 'img/' . $type_id . date('/Ymd/His_') . RandCode(5, 12) . $ext;
        $img = $this->uploadCloud($name, @file_get_contents($img));

        return $img;
    }
    // 排除其他分类的id不抓取
    private function exceptBookIds($type = 'all') {
        // https://sitel5mowkgk15re0qx9.zhuishuyun.com/api/books/female/index
        if ($type == 'all' || $type == 'female') {
            $url = $this->schemeHose . '/api/books/female/index';
            $rel = $this->cookieCurl($url);
            $rel = json_decode($rel, 1);
            if (!isset($rel['data']) || !$rel['data']) {
                dd('首页数据获取失败！');
            }
            foreach ($rel['data'] as $type_data) {
                foreach ($type_data['books'] as $novel) {
                    if (isset($novel['book_id']) && $novel['book_id'] && isset($novel['cover_url']) && isset($novel['book_chapter_total'])) {
                        $this->exptSpIds[] = $novel['book_id'];
                    } else {
                        $params = GetUrlParams($novel['redirect_url']);
                        $book_id = isset($params['bid']) ? $params['bid'] : $params['id'];
                        $this->exptSpIds[] = $book_id;
                    }
                }
            }
        }
        if ($type == 'all' || $type == 'male') {
            $url = $this->schemeHose . '/api/books/male/index';
            $rel = $this->cookieCurl($url);
            $rel = json_decode($rel, 1);
            if (!isset($rel['data']) || !$rel['data']) {
                dd('首页数据获取失败！');
            }
            foreach ($rel['data'] as $type_data) {
                foreach ($type_data['books'] as $novel) {
                    if (isset($novel['book_id']) && $novel['book_id'] && isset($novel['cover_url']) && isset($novel['book_chapter_total'])) {
                        $this->exptSpIds[] = $novel['book_id'];
                    } else {
                        $params = GetUrlParams($novel['redirect_url']);
                        $book_id = isset($params['bid']) ? $params['bid'] : $params['id'];
                        $this->exptSpIds[] = $book_id;
                    }
                }
            }
        }
    }
    /**
     * 格式化显示异常章节
     */
    private function explodSection() {
        $page = 1;
        $page_num = 200;
        while (true) {
            $list = $this->novelSections->model
                //->where('novel_id', 26)->where('num', '<', 10)
                ->where('id', '<', 84974)
                ->offset(($page - 1) * $page_num)
                ->limit($page_num)
                ->orderBy('id')
                ->get(['id', 'content']);
            if (!$list || !count($list)) {
                dd('清理章节格式完成！');
                break;
            }
            $page++;
            foreach ($list as $item) {
                try {
                    $url = $this->get_content($item['content']);
                    if ($url && strpos($url, $this->novelWeb)) {
                        dump($item['id'], $url);
                        $item->content = $url;
                        $item->save();
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            dump($item['id']);
        }
    }
    private function get_content($url, $re = 0) {
        $content = @file_get_contents($url);
        if (!$content && $re < 3) {
            sleep(3);
            return $this->get_content($url, ++$re);
        }
        if (strpos($content, '</p><p>') !== false) return false;
        $content = strip_tags($content);
        $arr = explode("\n", $content);
        $content2 = '';
        foreach ($arr as $line) {
            $arr2 = explode("\r", $line);
            foreach ($arr2 as $p) {
                $p = trim($p);
                if (empty($p)) continue;
                $content2 .= '<p>'. $p . '</p>';
            }
        }
        $path = explode($this->novelWeb.'/', $url)[1];
        return $this->uploadCloud($path, $content2);
    }

    //随机IP
    private function Rand_IP(){

        $ip2id= round(rand(600000, 2550000) / 10000); //第一种方法，直接生成
        $ip3id= round(rand(600000, 2550000) / 10000);
        $ip4id= round(rand(600000, 2550000) / 10000);
        //下面是第二种方法，在以下数据中随机抽取
        $arr_1 = array("218","218","66","66","218","218","60","60","202","204","66","66","66","59","61","60","222","221","66","59","60","60","66","218","218","62","63","64","66","66","122","211");
        $randarr= mt_rand(0,count($arr_1)-1);
        $ip1id = $arr_1[$randarr];
        return $ip1id.".".$ip2id.".".$ip3id.".".$ip4id;
    }
}
