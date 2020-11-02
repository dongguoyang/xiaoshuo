<?php

namespace App\Admin\Actions\Novel;

use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

use App\Admin\Models\NovelCheckLog;

class NovelCheckAction extends Action
{
    protected $selector = '.novel-check-action';

    public $types = [
        1   =>  '小说封面'
    ];

    public $latest_check_results = [];
    public $latest_check_description = '';

    public function __construct()
    {
        parent::__construct();
        foreach($this->types as $type => $description) {
            $latest_check_result = NovelCheckLog::where('target_type', '=', $type)->orderBy('id', 'desc')->first();
            $this->latest_check_results[$type] = $latest_check_result;
            if($latest_check_result) {
                $this->latest_check_description .= "[[检测类型-$description; 总量: {$latest_check_result['total_count']}; 有效总量: {$latest_check_result['valid_count']}; 无效总量: {$latest_check_result['invalid_count']}; 修复/处理总量: {$latest_check_result['fixed_count']}; 最近处理目标ID: {$latest_check_result['last_target_id']}; 起始时间：{$latest_check_result['started_time']} 至 {$latest_check_result['finished_time']} ;]]";
                $this->latest_check_description .= '<br/>';
            }
        }
    }

    public function handle(Request $request)
    {
        $params = request()->input();
        $type = isset($params['type']) && isset($this->types[$params['type']]) ? $params['type'] : 0;
        if(!$type) {
            $this->response()->error('大兄弟，不要搞事情哈');
        }
        $continued = isset($params['continue']) && $params['continue'] ? true : false;

        try {
            Artisan::queue('novel:check', [
                'last_id'       =>  0,
                '--type'        =>  $type,
                '--continue'    =>  $continued
            ]);
        } catch (\Exception $e) {
            try {
                Artisan::call('novel:check', [
                    'last_id'       =>  0,
                    '--type'        =>  $type,
                    '--continue'    =>  $continued
                ]);
            } catch (\Exception $e) {
                $this->response()->error('处理失败，原因：'.$e->getMessage());
            }
        }

        return $this->response()->success('已提交处理，请稍后查看')->refresh();
    }

    public function form()
    {
        $this->select('type', '检测类型')->options($this->types)->default(1)->required()->help($this->latest_check_description);
        $this->radio('continue', '是否从上次停下处继续')->options([1 => '是', 0 => '否'])->default(1);
    }

    public function html()
    {
        $description = str_replace('<br/>', '    ', $this->latest_check_description);
        return <<<HTML
        <a class="btn btn-sm btn-danger novel-check-action" title="$description">数据检测</a>
HTML;
    }
}