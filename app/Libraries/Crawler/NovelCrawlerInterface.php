<?php
namespace App\Libraries\Crawler;

use GuzzleHttp\Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

interface NovelCrawlerInterface {
    /**
     * 初始化订制的爬虫
     * @param string $base_url 所爬取的目标网站根地址
     * @param string $charset 编码：utf-8 / gbk / gb2312
     * @param string $novel_list_path 小说列表页起始路径，相对于根地址而言
     * @param int $start 抓取的起点偏移量
     * @param int $limit 抓取总量，若小于1则默认抓取所有
     * @param string $cookie 若站点需要设置cookie，请带上此参数
     * @param bool $debug 调试模式是否开启，开启调试模式则不需要入库
     */
    public function getNovelList();
    public function getNovelInfo(string $novel_url);
    public function saveNovelInfo(array $novel_info);
    public function saveAuthorInfo(string $author_name);
    public function saveTypeInfo(string $type_name);
    public function getChapterList(string $chapter_list_url, int $novel_id);
    public function getChapter(string $chapter_url, int $novel_id);
    public function saveChapterInfo(array $chapter_info);
}