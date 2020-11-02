<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;

class NovelRebuilt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:rebuilt {sid?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重新规整文章，仅保留最终文章数据，参数sid : [可选]需要重新规整的文章节点ID';

    const LOG_FILE = 'novel_rebuilt_failure_ids.log';
    protected $oss_bucket;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->oss_bucket = config('filesystems.disks.oss.bucket');
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
        $this->writeLog("+++++++++++++++++++++\r\n".$msg);
        $sid = $this->argument('sid');

        if(isset($sid) && !empty($sid) && $sid != '*') {
            $only_target = true;
        } else {
            $only_target = false;
        }

        $total = 0;
        $valid_count = 0;
        $invalid_count = 0;
        $success_count = 0;
        $failure_count = 0;
        $page_size = 1000;
        $last_max_id = 100000;// reset it to 0 after mission complete

        $client = new Client(['timeout' => 3]);
        Storage::disk('oss');

        do {
            if($only_target) {
                $data = DB::table('novel_sections')
                    ->where([
                        ['id', '=', $sid]
                    ])
                    ->orderBy('id', 'asc')
                    ->limit($page_size)
                    ->get();
            } else {
                $data = DB::table('novel_sections')
                    ->where([
                        ['id', '>', $last_max_id]
                    ])
                    ->orderBy('id', 'asc')
                    ->limit($page_size)
                    ->get();
            }
            if($data) {
                $data->transform(function($i) {
                    return (array)$i;
                });
                $data = $data->toArray();
            } else {
                $data = [];
            }
            foreach($data as $section) {
                ++$total;
                $last_max_id = $section['id'];
                $url = $section['content'];
                if(CheckUrl($url)) {
                    try {
                        // 发送请求
                        $response = $client->request('GET', $url);
                        $body = $response->getBody();
                        $content = $body->getContents();
                        if($content) {
                            // 解析 ...
                            // 算了，文章内容不对，直接截断非正常部分
                            if(false !== mb_stripos($content, '<div class="banner"><center></center></div>')) {
                                // 错误文章
                                ++$invalid_count;
                                $content = mb_substr($content, 0, mb_stripos($content, '<div class="banner"><center></center></div>'));
                                $content = mb_substr($content, 0, mb_stripos($content, '</div>'));// 最新内容
                                $format = '.html';
                                $file_name = 'novels/' . ceil($section['novel_id'] / 100) . '/'. ceil($section['id'] / 1000) . '/' . uniqid() . $format;
                                $res = Storage::put($file_name, $content);
                                if ($res) {
                                    $res = DB::table('novel_sections')
                                        ->where('id', '=', $section['id'])
                                        ->update([
                                            'content'       =>  Storage::url($file_name),
                                            'updated_num'   =>  DB::raw('updated_num + 1'),
                                            'updated_at'    =>  time()
                                        ]);
                                    if($res) {
                                        ++$success_count;
                                        $this->info('[NOTICE] section - ['.$section['id'].'] has rebuilt the content successfully');
                                        // remove the old file
                                        $old_file = str_replace('https://'.$this->oss_bucket.'.oss-cn-hangzhou.aliyuncs.com/', '', $url);
                                        Storage::delete($old_file);
                                    } else {
                                        ++$failure_count;
                                        $this->writeId($section['id']);
                                        $this->error('[NOTICE] section - ['.$section['id'].'] failed to rebuild the content');
                                        // remove the file
                                        Storage::delete($file_name);
                                    }
                                } else {
                                    ++$failure_count;
                                    $this->writeId($section['id']);
                                    $this->error('[WARNING] section - ['.$section['id'].'] rebuilt content failed, cause: upload failed');
                                }
                            } else {
                                // 文章内容正常
                                ++$valid_count;
                                $this->info('[NOTICE] section - ['.$section['id'].'] has the correct content');
                            }
                        } else {
                            ++$invalid_count;
                            ++$failure_count;
                            $this->writeId($section['id']);
                            $this->error('[ERROR] section - ['.$section['id'].'] has no content, url: '.$url);
                        }
                    } catch (RequestException $e) {
                        // 请求失败
                        ++$invalid_count;
                        ++$failure_count;
                        $this->writeId($section['id']);
                        $this->error('[ERROR] section - ['.$section['id'].'] request for content failed, url: '.$url);
                        continue;
                    } catch (\Exception $e) {
                        // 重构失败
                        $this->writeId($section['id']);
                        $this->error('[ERROR] section - ['.$section['id'].'] rebuilt failed, cause: '.$e->getMessage());
                    }
                } else {
                    ++$invalid_count;
                    ++$failure_count;
                    $this->error('[ERROR] section - ['.$section['id'].'] has a invalid node url: '.$url);
                    $this->writeId($section['id']);
                }
            }
        } while(count($data) >= $page_size);
        $end_time = microtime(true);
        $time_spent = $end_time - $start_time;
        $msg = 'Mission Finished at '.date('Y-m-d H:i:s', (int)$start_time).', with in '.gmdate("H:i:s", (int)$time_spent).' [seconds: '.$time_spent.']';
        $this->writeLog($msg."\r\n--------------------\r\n".$msg);
        $this->info($msg);
        $this->info("Summary: ");
        $this->info("Total: ".$total);
        $this->info("Valid: ".$valid_count);
        $this->error("Invalid: ".$invalid_count);
        $this->info("Success: ".$success_count);
        $this->error("Failure: ".$failure_count);
        return true;
    }

    protected function writeId($id) {
        $this->writeLog("$id, ");
    }

    protected function writeLog($msg) {
        file_put_contents(self::LOG_FILE, $msg."\r\n", FILE_APPEND);
    }
}
