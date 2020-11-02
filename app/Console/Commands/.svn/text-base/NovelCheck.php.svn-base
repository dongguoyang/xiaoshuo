<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Admin\Models\Novel;
use App\Admin\Models\NovelCheckLog;
use App\Logics\Repositories\src\NovelRepository;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\RequestException;

class NovelCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:check {last_id=0 : 上次最后检测的目标ID，作为本次起点} {--T|type=1 : 检测类型：1 小说封面} {--C|continue : 是否从上次停下处继续}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '数据检测，目前支持小说封面的检测';

    public $types = [
        1   =>  '检测小说封面，封面异常则关闭小说'
    ];
    public $total = 0;
    public $valid_count = 0;
    public $invalid_count = 0;
    public $success_count = 0;
    public $failure_count = 0;
    public $page_size = 1000;
    public $last_max_id = 0;
    const LOG_FILE = 'novel_check.log';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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
        $this->last_max_id = max(0, (int)$this->argument('last_id'));
        $type = (int)getNumber($this->option('type'));
        $type = in_array($type, array_keys($this->types)) ? $type : false;
        if(false === $type) {
            $this->error('No such a mission');
            return $type;
        }
        $continued = $this->option('continue');
        $continued = ($continued === true || $continued == 'on') ? true : false;
        if($continued) {
            $latest_check = NovelCheckLog::where('target_type', '=', $type)->orderBy('id', 'desc')->first();
            $this->last_max_id = $latest_check ? $latest_check->last_target_id : $this->last_max_id;
        }
        // 处理进程
        $this->info('Mission: '.$this->types[$type]);
        switch ($type) {
            case 1:
                // 检测小说封面
                $client = new Client(['timeout' => 5, 'http_errors' => true]);
                do {
                    $data = Novel::where([
                            ['id', '>', $this->last_max_id],
                            ['status', '=', 1]
                        ])
                        ->orderBy('id', 'asc')
                        ->limit($this->page_size)
                        ->get();
                    $data = $data ? $data->toArray() : [];
                    foreach ($data as $novel) {
                        ++$this->total;
                        $this->last_max_id = $novel['id'];
                        $cover_url = $novel['img'];
                        if(!empty($cover_url)) {
                            if(!CheckUrl($cover_url)) {
                                $cover_url = trim(config('app.url'), '/').'/'.ltrim($cover_url, '/');
                                if(!CheckUrl($cover_url)) {
                                    ++$this->invalid_count;
                                    $this->lockNovel($novel['id']);
                                    continue;
                                }
                            }
                            // 实测链接
                            try {
                                $response = $client->get($cover_url);
                                $code = $response->getStatusCode();
                                // 仅允许使用状态码为 200 的
                                if($code != 200) {
                                    ++$this->invalid_count;
                                    $this->lockNovel($novel['id']);
                                    continue;
                                }
                                // 检查返回头
                                $content_type = $response->getHeader('Content-Type')[0];
                                $content_length = $response->getHeader('Content-Length')[0];
                                if(false === stripos($content_type, 'image/') || $content_length < 10) {
                                    ++$this->invalid_count;
                                    $this->lockNovel($novel['id']);
                                    continue;
                                }
                                // 通过检测，且认为可用
                                ++$this->valid_count;
                                $this->info('[INFO] novel - ['.$novel['id'].'] has a valid cover image');
                            } catch (TransferException $e) {
                                ++$this->invalid_count;
                                $this->lockNovel($novel['id']);
                                continue;
                            } catch (\Exception $e) {
                                ++$this->invalid_count;
                                $this->lockNovel($novel['id']);
                                continue;
                            }
                        } else {
                            ++$this->invalid_count;
                            $this->lockNovel($novel['id']);
                            continue;
                        }
                    }
                } while(count($data) >= $this->page_size);
                break;
        }
        // 结果总结
        $timestamp = time();
        $date_time = date('Y-m-d H:i:s', $timestamp);
        if($this->total < 1) {
            $check_summary = [
                'target_type'   =>  $type,
                'total_count'   =>  $this->total,
                'valid_count'   =>  $this->valid_count,
                'invalid_count' =>  $this->invalid_count,
                'fixed_count'   =>  $this->success_count,
                'started_time'  =>  date('Y-m-d H:i:s', (int)$start_time),
                'finished_time' =>  $date_time,
                'last_target_id'=>  $this->last_max_id,
                'updated_at'    =>  $timestamp,
                'created_at'    =>  $timestamp
            ];
            $check_summary_res = NovelCheckLog::create($check_summary);
            if (!$check_summary_res) {
                $temp = '[ERROR] insert result failed';
                $this->error($temp);
                $this->writeLog($temp . ', Data: ' . json_encode($check_summary));
            }
            if ($check_summary['fixed_count']) {
                // 更新缓存
                $novel_repository = new NovelRepository;
                $novel_repository->ClearCache();
            }
        }
        $end_time = microtime(true);
        $time_spent = $end_time - $start_time;
        $msg = 'Mission Finished at '.date('Y-m-d H:i:s', (int)$start_time).', with in '.gmdate("H:i:s", (int)$time_spent).' [seconds: '.$time_spent.']';
        $this->info($msg);
        $this->info("Summary: ");
        $this->info("Total: ".$this->total);
        $this->info("Valid: ".$this->valid_count);
        $this->error("Invalid: ".$this->invalid_count);
        $this->info("Success: ".$this->success_count);
        $this->error("Failure: ".$this->failure_count);
        return true;
    }

    /**
     * 实时根据ID关闭小说
     * @param int $novel_id 小说ID
     */
    public function lockNovel(int $novel_id) {
        try {
            Novel::where('id', '=', $novel_id)->update([
                'status'    =>  0
            ]);
            ++$this->success_count;
            $this->info('[NOTICE] novel - ['.$novel_id.'] set lock successfully');
        } catch (\Exception $e) {
            ++$this->failure_count;
            $this->error('[ERROR] novel - ['.$novel_id.'] failed to set lock');
        }
    }

    protected function writeId($id) {
        $this->writeLog("$id, ");
    }

    protected function writeLog($msg) {
        file_put_contents(self::LOG_FILE, $msg."\r\n", FILE_APPEND);
    }
}
