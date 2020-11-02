<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Admin\Models\NovelSection;

class ChapterResetTitle extends Command
{
    public $total = 0;
    public $valid_count = 0;
    public $invalid_count = 0;
    public $success_count = 0;
    public $failure_count = 0;
    public $skip_count = 0;
    public $page_size = 1000;
    public $last_max_id = 0;
    public $regx = '/[\x{7b2c}]{1}[^A-Za-z\f\n\r\t\v]{1}[\x{7ae0}]{1}/u';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chapter:reset-title {last_id=0 : 上次最后处理的目标ID，作为本次起点}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重新构建章节标题，去掉开头的“第N章”';

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
        do {
            $chapters = NovelSection::where([
                    ['id', '>', $this->last_max_id]
                ])
                ->orderBy('id', 'asc')
                ->limit($this->page_size)
                ->get();
            $chapters = $chapters ? $chapters->toArray() : [];
            foreach ($chapters as $chapter) {
                ++$this->total;
                $this->last_max_id = $chapter['id'];
                $title = $chapter['title'];
                if(empty($title)) {
                    ++$this->skip_count;
                    $this->error('[ERROR] chapter ['.$chapter['id'].'] has no title');
                    continue;
                }
                if(preg_match($this->regx, $title)) {
                    ++$this->invalid_count;
                    $title = preg_replace($this->regx, '', $title);
                    $res = NovelSection::where('id', '=', $chapter['id'])->update(['title' => $title]);
                    if($res) {
                        ++$this->success_count;
                        $this->info('[INFO] chapter ['.$chapter['id'].'] reset title successfully');
                    } else {
                        ++$this->failure_count;
                        $this->error('[WARNING] chapter ['.$chapter['id'].'] reset title failed');
                    }
                } else {
                    ++$this->valid_count;
                    $this->info('[NOTICE] chapter ['.$chapter['id'].'] has a valid title');
                }
            }
        } while(count($chapters) >= $this->page_size);
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
        $this->error("Skip: ".$this->skip_count);
    }
}
