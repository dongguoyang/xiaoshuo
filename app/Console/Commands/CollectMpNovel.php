<?php
/**
 * 公众号 wap 章小说采集的命令
 * 运行命令
 * php artisan novel:collect-biquge-wap urlencode(cookie)     --host=     --type_id=12    --suitable_sex=1男2女    --link=    // 采集玄幻分类
 * php artisan novel:collect-biquge-wap asdidkd%3d1%3b+Hm_lvt_447fadd6bad32bfe438b95a891015d5b%3d1593164267%3b+Hm_lpvt_447fadd6bad32bfe438b95a891015d5b%3d1593164381 --host=https://wap.biqiuge.com --link=https://wap.biqiuge.com/book_44988 --type_id=19  --suitable_sex=1
 * last view novel_section_id = 2226666    // 上次下班观察的章节最大 ID 值
 * php artisan novel:collect-changdu-zhongwen  --host=https://c110246.818tu.com
 *
 */

namespace App\Console\Commands;

use App\Logics\Traits\CollectTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\NovelsCollect;


class CollectMpNovel extends Command
{
    use CollectTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     */
    protected $signature = 'novel:collect-mp {cookie} {--S|suitable_sex= : 抓取性别分类 male、female} {--T|type_id= : 抓取类型index、rank} {--H|host= : 指定采集的域名} {--M|slmax= : 章节抓取的最大间隔时间} {--L|link= : 单独采集一部小说的链接地址，多个链接用 ,, 两个英文逗号进行分隔}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '公众号wap站的小说采集程序';
    private   $host2cookie = []; // host cookie 列表对应
    protected $cookie; // 0 不需要收费的cookie     1 需要收费的cookie
    protected $novelWeb = 'mp';
    private   $sleepMax = 5; // 章节抓取间隔最大时间
    private   $linkSp = 0;
    private   $exptSpIds = []; // 排除抓取的 id 列表
    private   $type_id; // 设置的分类id
    private   $suitable_sex; // 设置的适用类型
    protected $noSpiderTitles = [
        '公众号采集'
    ];
    private $header = '';
    // 转换类型
    protected $novelTypes = [
    ];

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
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
            $pattern = '/member_token=(.*?);/i';
            preg_match($pattern,$this->cookie,$match);
            if($match){
                $this->header = 'Authorization: Bearer '.$match[1];
            }
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
        if ($slmax = $this->option('slmax')) {
            $this->sleepMax = $slmax;
        }
        $this->type_id = $this->option('type_id');
        $this->suitable_sex = $this->option('suitable_sex');
        if (!$this->type_id || !$this->suitable_sex) {
            throw new \Exception('请设置 type_id suitable_sex', 2000);
        }
        $link = urldecode($this->option('link'));
        if ($link && strpos(trim($link), 'http') === 0) {// 抓取一本小说
            $this->linkSp = true;
            try {
                $links = explode(',,', $link);
                foreach ($links as $link) {
                    if (empty($link) || strpos(trim($link), 'http') === false) {
                        dump('-------------------- link is error !', $link);
                        continue;
                    }
                    try {
                        $book_id = intval(explode('/novels/', $link)[1]);
                        $this->novelSpider($book_id);
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
        exit('ALL novel roll spider !!!');
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
        $page = 1;
        $star_content = $section['num'] ? false : true;
        $break_while = false;
        $novel_section_buy_update = 1;
        while (true) {
            if ($page > 1) {
                $start = ($page-1)*100;
                $url = '/v1/novels/'.$book_id.'/catalog?start='.$start.'&limit=100';
            } else {
                $url = '/v1/novels/'.$book_id.'/catalog?start=0&limit=100';
            }
            $url = $this->schemeHose . $url;
            $rel = $this->cookieCurl($url);
            $rel = json_decode(mb_convert_encoding($rel, 'UTF-8', 'GB2312, GBK'),true);
            if(!isset($rel['data']) || !$rel['data']){
                //throw new \Exception('章节列表异常！', 2000);
                $this->again_get_queue();
            }
            $list = $rel['data'];
            if (count($list) <= 1) {
                break;
            }
            foreach ($list as $itk => $item) {
                $title = $item['title'];
                if (!$star_content) {
                    if ($section['title'] == $title) {
                        $star_content = true;
                    }
                    continue;
                }
                if($item['welth'] > 0 && $novel_section_buy_update == 1 && $novel['need_buy_section'] == 1){
                    $novel_section_buy_update++;
                    $novel_up = ['need_buy_section'=>$item['idx']];
                    $this->novels->update($novel_up, $novel['id']);// 更新付费章节
                }
                $section['sp_num']++;
                $url = '/v1/read/'. $item['id'];
                list($content, $spider_url) = $this->sectionContent($url);
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
                sleep(mt_rand(2, $this->sleepMax)); // 一章读 5-15 秒钟
            }
            if ($break_while) break;
            $page++;
        }
        // 更新novel数据
        //$novel_up = ['need_buy_section'=>$need_buy_section, 'spider_url'=>$spider_url];
        if ($section['num'] > 20 && $novel['status'] == 0) $novel_up['status'] = 1;
        //$this->novels->update($novel_up, $novel['id']);// 更新小说总章节数据
    }
    // 查询小说是否存在；没有就获取并添加小说
    private function getMyNovel($sp_nid, $re = 0) {
        $url = '/v1/novels/'. $sp_nid;
        $rel = $this->cookieCurl($this->schemeHose . $url,[],[],'json',0,$this->header);
        $this->spidedHtml = json_decode(mb_convert_encoding($rel, 'UTF-8', 'GB2312, GBK'),true);
        if(!isset($this->spidedHtml['data']) || !$this->spidedHtml['data']){
            if(empty($this->spidedHtml['data'])){
                Log::warning('获取小说信息失败！------',['info'=>$this->spidedHtml]);
                return false;
            }
            $this->again_get_queue();
        }
        $data['spider_url'] = $url;
        list($data['title'], $data['author_id'], $data['author_name'], $data['type_ids'], $data['serial_status'])  = $this->novelTitle2Author();
        if (in_array($data['title'], $this->noSpiderTitles)) return null;
        if (strlen($data['author_name']) <= 2 && $data['author_name'] != '.') return null; // 作者都没得；后面又抓出来重复了
        $data['suitable_sex'] = $this->suitable_sex;
        $data['word_count'] = $this->spidedHtml['data']['words'];
        $data['sections'] = $this->spidedHtml['data']['article_count'];
        if ($had = $this->novels->findByMap([
            ['title', $data['title']],
            ['author_id', $data['author_id']],
            ['origin_web', $this->novelWeb],
        ], ['id', 'sections', 'spider_url', 'serial_status', 'status', 'suitable_sex', 'word_count', 'type_ids', 'serial_status','need_buy_section'])) {
            $had['spider_url'] = $data['spider_url'];
            if ($had['word_count']!=$data['word_count'] ||
                $had['type_ids']!=$data['type_ids'] ||
                $had['serial_status']!=$data['serial_status']
            ) {
                $this->novels->update($data, $had['id']);
            }
            return $had;
        }
        $data['desc']   = $this->spidedHtml['data']['summary'];
        $data['tags']   = implode('|', $this->spidedHtml['data']['tags']);
        $data['img'] = $this->spidedHtml['data']['avatar'];
        $data['img']    = $data['img'] ? $this->uploadImg($data['img'], $data['type_ids']) : 'https://ccccccc1111111.oss-cn-hangzhou.aliyuncs.com/lm-novelspider/baozou/img/6/20200114/165442_ou3o2nknbr91m792ep.jpg';//$this->uploadImg($this->novelImg(), $data['type_ids']);
        $data['need_buy_section'] = 10;
        $data['subscribe_section'] = 8;
        $data['status'] = 0;
        $data['origin_web'] = $this->novelWeb;
        $in = $this->novels->create($data);
        return ['id'=>$in['id'], 'sections'=>0, 'spider_url'=>$data['spider_url'], 'status'=>$data['status'], 'serial_status'=>$data['serial_status']];
    }


    // 小说标题
    private function novelTitle2Author() {
        $title = $this->spidedHtml['data']['title'];
        $author_name = $this->spidedHtml['data']['author'];
        if (!$author_name) {
            $author_name = '.';
            $author_id   = 1;
        } else {
            $author = $this->authors->findBy('name', $author_name, ['id', 'name']);
            if (!$author) {
                $author = $this->authors->create(['name'=>$author_name, 'status'=>1]);
            }
            $author_id = $author['id'];
        }
        $serial_status = $this->spidedHtml['data']['status'];
        if (!$serial_status) {
            $serial_status = 1;
        } else {
            $serial_status = $serial_status == 'ongoing' ? 1 : 2;
        }
        $type_ids = $this->type_id;
        return [$title, $author_id, $author_name, $type_ids, $serial_status];
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

    // 章节正文内容获取和下一章地址获取
    // $section_id 章节ID
    private function sectionContent($url,$re=0) {
        $rel = $this->cookieCurl($this->schemeHose . $url,[],[],'json',0,$this->header);
        $rel = json_decode(mb_convert_encoding($rel, 'UTF-8', 'GB2312, GBK'),true);
        if(!isset($rel['data']) || !$rel['data']){
            if(isset($rel['message'])){
                dd('账号异常'); //这里要多个账号切换
            }
            Log::warning('小说采集后失败！------',['info'=>$rel]);
            if ($re < 5) {
                $re++;
                sleep($re * 25);
                $this->sectionContent($url,$re);
            }
            //再次放入队列
            $this->again_get_queue();
        }
        $content = trim($rel['data']['content']);
        $arr = explode("。", $content);
        $content2 = '';
        if($arr){
            foreach ($arr as $p) {
                $p = trim($p).'。';
                if (empty($p)) continue;
                $content2 .= '<p>'. $p . '</p>';
            }
        }else{
            $content2 = $content;
        }
        return [$content2, $url];
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

    //当任务失败时，再次放入队列中
    private function again_get_queue(){
        //再次放入队列
        $arg= [
            'cookie'=>urlencode($this->cookie),
            '--host'=>$this->schemeHose,
            '--type_id'=>12,
            '--suitable_sex'=>1,
            '--link'=>urldecode($this->option('link'))
        ];
        //NovelsCollect::dispatch('novel:collect-mp',$arg);
    }
}
