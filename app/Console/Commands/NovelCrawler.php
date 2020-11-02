<?php
/**
 * 小说爬虫实现类
 */
namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;// 请注意此组件可能抛出异常，故使用时应检查是否有对应内容，做好异常捕捉
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Libraries\Crawler\NovelCrawlerInterface;
use App\Logics\Models\Author;
use App\Logics\Models\Novel;
use App\Logics\Models\NovelSection;
use App\Logics\Models\Type;

class NovelCrawler extends Command implements NovelCrawlerInterface {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:crawl:youmi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '采集指定小说网站所有小说';

    const CONCURRENCY = false;
    const CONCURRENCY_NUM = 10;
    const WILDCARD = '[*]';
    const INTERVAL = 20000000;// 采集时间间隔（微秒）
    const PERTURBATION = 5000000;// 扰动范围（微秒）

    private $client;

    private $base_url;// 所爬取的目标网站根地址，例如：http://www.novelsite.com
    private $charset = 'utf-8';// 编码：utf-8 / gbk / gb2312
    private $novel_list_path;// 小说列表页路径，相对于根地址而言，例如：novel/[*]/all/1.html，其中[*]为通配符，实际使用时由 $start 和 $limit 参数指定的区间替换
    private $start = 1;// 抓取的小说起点偏移量
    private $limit = -1;// 抓取小说总量，若小于1则默认抓取所有
    private $cookie;// 若站点需要设置cookie，请带上此参数
    private $debug = false;// 调试模式是否开启，开启调试模式则不需要入库

    private $novel_list_xpath;

    private $request_send = false;
    private $regx = '/[\x{7b2c}]{1}[^A-Za-z\f\n\r\t\v]{1}[\x{7ae0}]{1}/u';

    private $novel_break_point;// 采集小说断点，若相应小说采集被设置断点，则停止采集。此变量结构：array ( novel_id1 => bool1, novel_id2 => bool2, ...)

    /**
     * 初始化订制的爬虫
     */
    public function __construct() {
        parent::__construct();

        $this->base_url = 'http://wx0e5baad403af8147.shanhukanshu.com/';// 请带上最后的"/"
        $this->charset = 'utf-8';
        $this->novel_list_path = 'shuku/[*]//0/0/2/2.html';
        $this->start = 1;
        $this->limit = 210;
        $this->debug = false;
        $this->cookie = CookieJar::fromArray([
            'PHPSESSID'             =>  't9d4tpaq7u9ch4gcdf777nobh2',
            'UM_distinctid'         =>  '16f60af278c48f-0b0de9fd45d97f-41495a08-1fa400-16f60af278d5a4',
            'CNZZDATA1275384399'    =>  '61938674-1577875155-%7C1577951249',
            'CNZZDATA1275384400'    =>  '1497860906-1577873067-null%7C1577873067'
        ], rtrim(substr($this->base_url, stripos($this->base_url, '://') + 3), '/'));
        $this->client = new Client([
            'base_uri'  =>  $this->base_url,
            'timeout'   =>  20,
            'headers'   =>  [
                'User-Agent'        =>  'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36 QBCore/4.0.1278.400 QQBrowser/9.0.2524.400 Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2875.116 Safari/537.36 NetType/WIFI MicroMessenger/7.0.5 WindowsWechat',
                'Accept-Language'   =>  'zh-CN,zh;q=0.8,en-US;q=0.6,en;q=0.5;q=0.4',
                'Accept-Encoding'   =>  'gzip, deflate',
                'Accept'            =>  'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'
            ],
            'cookies'   =>  $this->cookie
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start_time = microtime(true);
        $msg = 'Mission started at '.date('Y-m-d H:i:s', (int)$start_time);
        $this->info($msg);
        $this->getNovelList();
        $end_time = microtime(true);
        $time_spent = $end_time - $start_time;
        $msg = 'Mission Finished at '.date('Y-m-d H:i:s', (int)$start_time).', with in '.gmdate("H:i:s", (int)$time_spent).' [seconds: '.$time_spent.']';
        $this->info($msg);
        return true;
    }

    /**
     * 获取小说列表
     */
    public function getNovelList() {
        for($page = $this->start; $page <= $this->limit; ++$page) {
            // 获取小说列表
            $novel_list_url = str_replace(self::WILDCARD, $page, $this->novel_list_path);
            $this->info('[TIPS] crawl on '.$novel_list_url.' for novel list');
            $response = $this->sendRequest('GET', $novel_list_url);
            if(!$response) {
                $novel_list_url = $this->base_url.$novel_list_url;
                $this->error("[ERROR] Get novel list page failed, page: $page, target-site url: ".$novel_list_url);
                file_put_contents('crawler.novel.list.fail.log', $novel_list_url."\r\n", FILE_APPEND);
                continue;
            }
            $stringBody = $response->getBody()->getContents();
            $document = new Crawler();
            $document->addHtmlContent($stringBody, strtoupper($this->charset));
            try {
                $document->filterXPath('//div[@id="bookList"]/div[@class="book"]')->each(function (Crawler $node, $i) {
                    try {
                        $novel_url = $node->filterXPath('//a[@class="book-a"]')->attr('href');
                        $this->getNovelInfo($novel_url);
                        return true;
                    } catch (\Exception $e) {
                        return false;
                    }
                });
            } catch (\Exception $e) {
                $this->error("[ERROR] Get novel list page failed, page: $page, detail: ".$e->getMessage());
            }
            if($this->debug) {
                break;// once debug
            }
        }
    }

    /**
     * 获取小说详情
     * @param string $novel_url
     * @return bool
     */
    public function getNovelInfo(string $novel_url) {
        $this->info('[TIPS] crawl on '.$novel_url.' for novel data');
        $response = $this->sendRequest('GET', $novel_url);
        if(!$response) {
            $novel_url = $this->base_url.ltrim($novel_url, '/');
            $this->error("[ERROR] Get novel page failed, novel url: ".$novel_url);
            file_put_contents('crawler.novel.fail.log', $novel_url."\r\n", FILE_APPEND);
            return false;
        }
        $stringBody = $response->getBody()->getContents();
        $document = new Crawler();
        $document->addHtmlContent($stringBody, strtoupper($this->charset));
        try {
            // 解析小说详情页
            $title = $document->filterXPath('//div[@class="content_body"]/h2[@class="line_2"]')->text('未命名小说' . md5(time()));
            $cover = $document->filterXPath('//span[@class="content_header"]/img[@class="lazyload"]')->attr('data-original');
            $cover_offset = stripos($cover, '?');
            $cover = substr($cover, 0, $cover_offset === false ? strlen($cover) : $cover_offset);
            $author_name = $document->filterXPath('//div[@class="content_body"]/p[@class="classify"]/span[@class="author"]')->first()->text('佚名');
            $serial_status_string = $document->filterXPath('//div[@class="content_body"]/p[@class="classify"]/span[@class="nh_wap_fontcolor"]')->text('完本');
            $word_count = $document->filterXPath('//div[@class="content_body"]/p[@class="classify"]/span[@class="author"]')->eq(1)->text();
            $word_count = (float)str_replace('万字', '', $word_count);
            $type = $document->filterXPath('//div[@class="content_body"]/p[1]/span[1]')->text();
            $description = $document->filterXPath('//div[@class="jianjie"]/p/a[@data-act="text_flod"]')->text();
            $description = str_replace(['收起', '展开'], '', strip_tags($description));
            $latest_chapter = $document->filterXPath('//div[@class="news_content"]/div[@class="news"]/a[@class="btn1"]/div[@class="new"]')->text();
            $current_chapter_count = (int)getNumber($latest_chapter);
            $spider_url = $this->base_url . ltrim($novel_url, '/');
            // 获取章节目录
            $chapter_list_url = $document->filterXPath('//div[@id="tab_conbox1"]/div[contains(@class, "tab1")]/div[@class="more"]/a')->attr('href');
        } catch (\Exception $e) {
            $novel_url = $this->base_url.ltrim($novel_url, '/');
            $this->error("[ERROR] Get novel info failed, novel url: ".$novel_url);
            file_put_contents('crawler.novel.fail.log', $novel_url."\r\n", FILE_APPEND);
            return false;
        }
        // 入库
        // 所有数据在入库时进行格式化处理
        $novel_info = [
            'title'         =>  $title,
            'img'           =>  $cover,
            'author_name'   =>  $author_name,
            'serial_status' =>  $serial_status_string,
            'word_count'    =>  $word_count,
            'type'          =>  $type,
            'desc'          =>  $description,
            'sections'      =>  $current_chapter_count,
            'spider_url'    =>  $spider_url
        ];
        // debug
        if($this->debug) {
            $this->line('[DEBUG] novel info as below:');
            dump($novel_info);
        }
        $retry = 3;
        do {
            $novel = $this->saveNovelInfo($novel_info);
            if($novel) {
                break;
            }
            --$retry;
        } while ($retry);
        if(!$novel) {
            // 保存失败则不再尝试
            return false;
        }
        return $this->getChapterList($chapter_list_url, $novel['id']);
    }

    /**
     * 保存小说详情
     * @param array $novel_info
     * @return array|bool
     */
    public function saveNovelInfo(array $novel_info) {
        if($this->debug) {
            $novel_info['id'] = 0;
            return $novel_info;
        }
        // 保存作者
        $author = $this->saveAuthorInfo($novel_info['author_name']);
        // 保存分类
        $type = $this->saveTypeInfo($novel_info['type']);
        try {
            $read_num = random_int(10, 1000);
            $week_read_num = random_int($read_num, $read_num + 1000);
        } catch (\Exception $e) {
            $read_num = 1;
            $week_read_num = 8;
        }
        $_novel = [
            'title'         =>  $novel_info['title'],
            'img'           =>  $novel_info['img'],
            'author_id'     =>  $author ? $author['id'] : 1,
            'author_name'   =>  $author ? $author['name'] : strip_tags($novel_info['author_name']),
            'serial_status' =>  $this->getSerialStatus($novel_info['serial_status']),
            'word_count'    =>  $novel_info['word_count'],
            'suitable_sex'  =>  1,
            'type_ids'      =>  $type ? $type['id'] : 1,
            'tags'          =>  $type ? $type['name'] : strip_tags($novel_info['type']),// 获取不到标签，以类型作为标签
            'desc'          =>  $novel_info['desc'],
            'sections'      =>  $novel_info['sections'],
            'status'        =>  1,
            'read_num'      =>  $read_num,
            'week_read_num' =>  $week_read_num
        ];
        // 去重
        $condition = [
            ['title' , '=', $_novel['title']],
            ['author_name', '=', $_novel['author_name']]
        ];
        $novel_existed = Novel::where($condition)->first();
        if($novel_existed) {
            $this->info('[NOTICE] novel['.$_novel['title'].'] existed');
            // dump($novel_existed->toArray());
            return $novel_existed->toArray();
        }
        // 入库
        try {
            $novel = Novel::create($_novel)->toArray();
            $this->novel_break_point[$novel['id']] = false;
            // 获取图片
            $response = $this->sendRequest('GET', $novel['img'], true);
            if(!$response) {
                $this->error( "[ERROR] Get cover for novel[ {$novel['id']} - {$novel['title']} ] failed, cover url: {$novel['img']}");
                file_put_contents('crawler.novel.cover.fail.log', "{$novel['id']}\r\n", FILE_APPEND);
            }
            $image_stream = $response->getBody()->getContents();
            $file_extension = get_file_type(substr($image_stream, 0, 2));
            if(!$file_extension || !in_array($file_extension, ['jpg', 'gif', 'png'])) {
                $this->warn("[WARNING] invalid image type detected, please replace the novel image with your own. novel-id: {$novel['id']}, invalid type: $file_extension");
                file_put_contents('crawler.novel.image.warn.log', "{$novel['id']}\r\n", FILE_APPEND);
                return $novel;
            }
            $image_name = 'novel/cover/' . uniqid() . '.' . $file_extension;
            // 转存
            $res = Storage::put($image_name, $image_stream);
            if (!$res) {
                $this->error( "[ERROR] Store cover for novel[ {$novel['id']} - {$novel['title']} ] failed, cover url: {$novel['img']}");
                file_put_contents('crawler.novel.cover.fail.log', "{$novel['id']}\r\n", FILE_APPEND);
            } else {
                $restored_cover = Storage::url($image_name);
                Novel::where('id', '=', $novel['id'])->update(['img' => $restored_cover]);
                $novel['img'] = $restored_cover;
            }
            $this->info('[INFO] novel['.$novel['id'].'] created successfully');
            return $novel;
        } catch (\Exception $e) {
            $this->error('[ERROR] novel['.$_novel['title'].'] create failed, reason: '.$e->getMessage());
            file_put_contents('crawler.novel.create.fail.log', json_encode($_novel)."\r\n", FILE_APPEND);
            return false;
        }
    }

    /**
     * 解析小说状态
     * @param string $serial_status
     * @return int
     */
    private function getSerialStatus(string $serial_status) {
        // 限免由运营决定，此处不考虑
        return stripos($serial_status, '完') !== false ? 2 : 1;
    }

    /**
     * 保存作者数据
     * @param string $author_name
     * @return bool|array
     */
    public function saveAuthorInfo(string $author_name) {
        $author_name = strip_tags($author_name);
        $existed = Author::where([
            ['name', '=', $author_name]
        ])->first();
        if($existed) {
            return $existed->toArray();
        }
        try {
            return Author::create([
                'name'      =>  $author_name,
                'status'    =>  1
            ])->toArray();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 保存分类
     * @param string $type_name
     * @return bool|array
     */
    public function saveTypeInfo(string $type_name) {
        $type_name = strip_tags($type_name);
        $existed = Type::where([
            ['name', 'like', "%$type_name%"]
        ])->first();
        if($existed) {
            return $existed->toArray();
        }
        try {
            return Type::create([
                'pid'       =>  0,
                'level'     =>  1,
                'name'      =>  $type_name,
                'status'    =>  1,
                'sort'      =>  100
            ])->toArray();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取章节列表
     * @param string $chapter_list_url 章节列表链接/路径
     * @param int $novel_id 小说ID
     */
    public function getChapterList(string $chapter_list_url, int $novel_id) {
        do {
            if($this->novel_break_point[$novel_id]) {
                return false;
            }
            $this->info('[TIPS] get chapter list on '.$chapter_list_url);
            $response = $this->sendRequest('GET', $chapter_list_url);
            if(!$response) {
                $chapter_list_url = $this->base_url.ltrim($chapter_list_url, '/');
                $this->error("[ERROR] Get chapter list page failed, list url: ".$chapter_list_url);
                file_put_contents('crawler.chapter.list.fail.log', $chapter_list_url."\r\n", FILE_APPEND);
                break;
            }
            $stringBody = $response->getBody()->getContents();
            $document = new Crawler();
            $document->addHtmlContent($stringBody, strtoupper($this->charset));
            // 解析章节列表
            try {
                // 下一页
                $chapter_list_url = $document->filterXPath('//div[@class="fanye"]/div[3]/a')->attr('href');
                // debug
                if($this->debug) {
                    $this->line('[DEBUG] next chapter list url ' . $chapter_list_url);
                }
                $document->filterXPath('//div[@id="tab_conbox1"]/div[@class="tab1"]/ul/li')->each(function (Crawler $node, $i) use ($novel_id) {
                    try {
                        $chapter_url = $node->filterXPath('//a')->attr('data-url');
                        return $this->getChapter($chapter_url, $novel_id);
                    } catch (\Exception $e) {
                        return false;
                    }
                });
            } catch (\Exception $e ) {
                $chapter_list_url = $this->base_url.ltrim($chapter_list_url, '/');
                $this->error("[ERROR] Get chapter list content failed, list url: ".$chapter_list_url);
                file_put_contents('crawler.chapter.list.fail.log', $chapter_list_url."\r\n", FILE_APPEND);
                $this->novel_break_point[$novel_id] = true;
                break;
            }
        } while (!empty($chapter_list_url) && stripos($chapter_list_url, 'javascript:') === false);
        return true;
    }

    /**
     * 获取章节内容并入库
     * @param string $chapter_url 章节链接/路径
     * @param int $novel_id 小说ID
     * @return bool|array
     */
    public function getChapter(string $chapter_url, int $novel_id) {
        if($this->novel_break_point[$novel_id]) {
            return false;
        }
        $this->info('[TIPS] get chapter data on '.$chapter_url);
        // 获取内容
        $response = $this->sendRequest('GET', $chapter_url);
        if(!$response) {
            $chapter_url = $this->base_url.ltrim($chapter_url, '/');
            $this->error("[ERROR] Get chapter failed, chapter url: ".$chapter_url);
            file_put_contents('crawler.chapter.fail.log', $chapter_url."\r\n", FILE_APPEND);
            return false;
        }
        $stringBody = $response->getBody()->getContents();
        $document = new Crawler();
        $document->addHtmlContent($stringBody, strtoupper($this->charset));
        // 解析
        try {
            $chapter_title = $document->filterXPath('//div[@class="reading-body"]/p[@class="title"]')->text();
            $chapter_content = $document->filterXPath('//div[@class="reading-body"]/div[@id="idcontent"]')->html();
        } catch (\Exception $e ) {
            $this->novel_break_point[$novel_id] = true;
            return false;
        }
        $start_tag = 'var chapnum =';
        $offset = stripos($stringBody, $start_tag) + strlen($start_tag);
        $end_tag = 'var erweima';
        $offset2 = stripos($stringBody, $end_tag);
        $chapter_order = (int)getNumber(substr($stringBody, $offset, max(1, $offset2 - $offset)));
        $chapter_title = trim(preg_replace($this->regx, '', $chapter_title));
        // 保存
        $chapter_info = [
            'novel_id'      =>  $novel_id,
            'num'           =>  $chapter_order,
            'title'         =>  $chapter_title,
            'content'       =>  $chapter_content,
            'spider_url'    =>  $this->base_url.ltrim($chapter_url, '/')
        ];
        // debug
        if($this->debug) {
            $this->line('[DEBUG] chapter data as below:');
            dump($chapter_info);
            die();
        }
        $retry = 3;
        do {
            $res = $this->saveChapterInfo($chapter_info);
            if($res) {
                break;
            }
            --$retry;
        } while($retry);
        if(!$res) {
            $this->novel_break_point[$novel_id] = true;
        }
        return $res;
    }

    /**
     * 保存章节数据
     * @param array $chapter_info
     * @return array|bool
     */
    public function saveChapterInfo(array $chapter_info) {
        if($this->debug) {
            $chapter_info['id'] = 0;
            return $chapter_info;
        }
        // 查重
        $condition = [
            ['novel_id', '=', $chapter_info['novel_id']],
            ['num', '=', $chapter_info['num']],
        ];
        $chapter = NovelSection::where($condition)->first();
        DB::beginTransaction();
        try {
            // 入库/刷新
            if (!$chapter) {
                $chapter = [
                    'novel_id'      =>  $chapter_info['novel_id'],
                    'num'           =>  $chapter_info['num'],
                    'title'         =>  $chapter_info['title'],
                    'content'       =>  '',
                    'updated_num'   =>  0,
                    'spider_url'    =>  $chapter_info['spider_url']
                ];
                $chapter = NovelSection::create($chapter)->toArray();
            } else {
                $chapter->toArray();
                NovelSection::where([
                    ['id', '=', $chapter['id']]
                ])->update([
                    'title'         =>  $chapter_info['title'],
                    'updated_num'   =>  DB::raw('updated_num + 1'),
                    'spider_url'    =>  $chapter_info['spider_url']
                ]);
            }
            Novel::where([
                ['id', '=', $chapter_info['novel_id']],
                ['sections', '<', $chapter_info['num']]
            ])->update(['sections' => $chapter_info['num']]);
            // 转存
            $file_name = 'novels/' . ceil($chapter_info['novel_id'] / 100) . '/'. ceil($chapter['id'] / 1000) . '/' . uniqid() . '.html';
            $res = Storage::put($file_name, $chapter_info['content']);
            if (!$res) {
                $this->error( "[ERROR] Store chapter content failed, chapter detail: [novel_id - {$chapter_info['novel_id']}, order - {$chapter_info['num']}]");
                file_put_contents('crawler.chapter.content.fail.log', json_encode($chapter_info, JSON_UNESCAPED_UNICODE)."\r\n", FILE_APPEND);
            }
            $content_url = Storage::url($file_name);
            NovelSection::where([
                ['id', '=', $chapter['id']]
            ])->update([
                'content'   =>  $content_url
            ]);
            $chapter['content'] = $content_url;
            $this->info('[INFO] chapter['.$chapter['id'].'] created successfully, novel_id: '.$chapter_info['novel_id'].'; chapter_order: '.$chapter_info['num']);
            DB::commit();
            return $chapter;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('[ERROR] chapter['.$chapter_info['novel_id'].' - '.$chapter_info['num'].'] create failed, reason: '.$e->getMessage());
            file_put_contents('crawler.chapter.content.fail.log', json_encode($chapter_info, JSON_UNESCAPED_UNICODE)."\r\n", FILE_APPEND);
            return false;
        }
    }

    /**
     * 发送请求，获取结果
     * @param string $method 请求方式：GET / POST ...
     * @param string $url 请求路径
     * @param bool $is_resource 是否为请求静态资源
     * @param int $retry 失败重试次数
     * @return bool|\Psr\Http\Message\ResponseInterface 成功则返回响应对象，否则返回false
     */
    public function sendRequest(string $method, string $url, $is_resource = false, int $retry = 3) {
        do {
            if(!$is_resource) {
                if ($this->request_send) {
                    $micro_seconds = self::getRandNum();
                    if(/*$this->debug*/true) {
                        $this->line('[TIPS] sleeping for ' . $micro_seconds . ' micro seconds');
                    }
                    usleep($micro_seconds);
                } else {
                    $this->request_send = true;
                }
            }
            try {
                $response = $this->client->request($method, $url);
                $code = $response->getStatusCode();
                if($code != 200) {
                    return false;
                }
                return $response;
            } catch (\Exception $e) {
                --$retry;
            }
        } while ($retry);
        return false;
    }

    /**
     * 通过三角函数生成随机数，用于控制访问频率
     * @return float|int
     */
    public static function getRandNum() {
        try {
            $rand = random_int(-90, 90);
        } catch (\Exception $e) {
            $rand = 0;
        }
        $angle = M_PI_2 * ($rand / 90);
        return round(sin($angle) * self::PERTURBATION + self::INTERVAL);
    }
}