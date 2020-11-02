<?php
namespace app\Admin\Extensions\excel;

use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Facades\Excel;
use App\Admin\Models\GetcashLog;
use Encore\Admin\Grid;
class GetcashLogsExcel extends AbstractExporter
{
   
    protected $body = ['id','user_id','user_money_log_id','money',
        'status','created_at','updated_at','payment_time','retry'];
    
    protected $head = ['编号','用户编号','用户资金流水记录id','金额(元)',
        '状态','创建时间','最后更新时间','付款时间','提现重试次数'
    ];
    

    public function export() {
         
        Excel::create('testgetcash', function($excel) {
            $excel->sheet('sheet', function($sheet) {
                $head = $this->head;
                $body = $this->body;
                $GetcashLog_status = GetcashLog::getStatus(0);
                $bodyRows = collect($this->getData())->map(function ($item)use($body,$GetcashLog_status) {
                    foreach ($body as $keyName){
                        switch ($keyName) {
                            case "money":
                                $item['money'] = $item['money'] /100;
                            break;
                            case "status":
                                $item['status'] = $GetcashLog_status[$item['status']];
                            break;
                            
                        }
                        $arr[] = array_get($item, $keyName);
                    }
                    return $arr;
                });
                $rows = collect([$head])->merge($bodyRows);
               
                $sheet->rows($rows);
            });
        })->export('csv');
    }
}

