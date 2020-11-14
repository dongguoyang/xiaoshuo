<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\ReadNovelLogs;
use App\Logics\Repositories\Repository;

class ReadNovelLogsRepository  extends Repository {

    public function model() {
        return ReadNovelLogs::class;
    }

    public function AddNumLog($customer_id,$platform_wechat_id,$novel_id,$novel_section_id,$novel){
        $result = $this->model->findByMap([['customer_id'=>$customer_id],['novel_id'=>$novel_id],['novel_section_id'=>$novel_section_id]],$this->model->fields);
        if($result){ //数量加一
            $this->model->update(['user_read_num'=>$result['user_read_num']+1],$result['id']);
        }else{ //新增数据
            $data = [
               'customer_id'=> $customer_id,
               'platform_wechat_id'=>$platform_wechat_id,
               'novel_id'=>$novel_id,
               'name'=>$novel['title'],
               'novel_section_id'=>$novel_section_id,
               'user_read_num'=>1,
               'title'=>$novel['section_title']
            ];
            $this->model->create($data);
        }
    }

}