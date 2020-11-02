<?php

namespace App\Console\Commands;

use App\Logics\Repositories\src\NovelRepository;
use App\Logics\Repositories\src\NovelSectionRepository;
use Illuminate\Console\Command;

class StopSectionMin10 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:stop-section-min10 {section_mins}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '停用章节数小于10的';

    private $section_mins = 10;

    private $novels;
    private $novelSections;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->novels        = new NovelRepository();
        $this->novelSections = new NovelSectionRepository();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->section_mins = $this->argument('section_mins');
        if (!$this->section_mins || !is_numeric($this->section_mins)) {
            exit('请填写 section_mins 数量');
        }
        //
        $this->info($this->signature . ' start ' . date('Y-m-d H:i:s'));
        $num = 0;
        $rel = ['up'=>0, 'stop'=>0];
        while (true) {
            $list = $this->novels->model->where('status', 1)->where('id', '>', $num)->limit(100)->select(['id', 'sections', 'desc'])->get();

            if (!$list || !count($list)) break;
            foreach ($list as $nov) {
                $update = [];
                $section_count = $this->novelSections->model->where('novel_id', $nov['id'])->count();
                $section_count = $section_count ? $section_count : 0;
                if ($nov['sections'] != $section_count) {
                    // 更新总章节数
                    $update['sections'] = $section_count;
                    $rel['up']++;
                    $this->info('novel_id = ' . $nov['id'] . 'update sections '. $nov['sections'] . ' to ' . $section_count);
                }
                if ($section_count < $this->section_mins) {
                    // 停用小于5章的
                    $update['status'] = 0;
                    $rel['stop']++;
                    $this->info('novel_id = ' . $nov['id'] . 'stoped');
                }
                if (strpos($nov['desc'], '&nbsp;') || strpos($nov['desc'], '&hellip;') || strpos($nov['desc'], '</')) {
                    // 清除标签和特殊符号
                    $update['desc'] = str_replace('&nbsp;', '', $nov['desc']);
                    $update['desc'] = str_replace('&hellip;', '', $nov['desc']);
                    $update['desc'] = strip_tags($update['desc']);
                }
                if ($update) {
                    $this->novels->update($update, $nov['id']);
                }
            }
            $num = $nov['id'];
        }
        $this->info($this->signature . ' end ' . json_encode($rel) . '___' . date('Y-m-d H:i:s'));
    }
}
