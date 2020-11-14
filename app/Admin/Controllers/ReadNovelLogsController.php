<?php

namespace App\Admin\Controllers;

use App\Admin\Models\ReadNovelLogs;
use App\Admin\Models\User;
use App\Admin\Models\Customer;
use App\Admin\Models\PlatformWechat;
use App\Admin\Models\Novel;
use App\Admin\Models\NovelSection;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ReadNovelLogsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '小说章节阅读人数';
    public $customer;

    public function customer() {
        if(!empty($this->customer)) {
            return $this->customer;
        } else {
            return $this->customer = Admin::user();
        }
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new ReadNovelLogs);
        if($this->customer()->id == 1){
            $grid->model()->selectRaw('sum(user_read_num) as sum, id,novel_id,customer_id,name,user_read_num,updated_at')->groupBy('novel_id')->orderby('sum','desc');
        }else{
            $grid->model()->where('customer_id',$this->customer()->id)->selectRaw('sum(user_read_num) as sum, id,novel_id,customer_id,name,user_read_num,updated_at')->groupBy('novel_id')->orderby('sum','desc');

        }
        $grid->column('novel_id', __('小说ID'));
        $grid->column('name', __('小说名'));
        $grid->column('sum', __('阅读数量'));
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->append('<a target="_blank" href="/'. config('admin.route.prefix').'/read_novel_info?novel_id='.$actions->row->novel_id .'" class="grid-row-edit btn btn-xs btn-primary" title="查看详情"><i class="fa fa-list-ol"></i> 查看详情</a>');
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {

    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

    }
}
