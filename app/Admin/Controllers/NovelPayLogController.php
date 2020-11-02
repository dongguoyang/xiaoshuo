<?php

namespace App\Admin\Controllers;

use App\Admin\Models\CoinLog;
use App\Logics\Models\NovelPayStatistics;
use App\Logics\Services\src\NovelPayStatisticsService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class NovelPayLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户充值阅读记录';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $grid = new Grid(new NovelPayStatistics);
        if($customer['pid']){
          $grid->model()->where('customer_id',$customer['id'])->selectRaw('sum(pay_num) as sum, id,novel_id,customer_id,read_num,updated_at')->groupBy('novel_id')->orderby('sum','desc');  
        }else{
          $grid->model()->selectRaw('sum(pay_num) as sum, id,novel_id,customer_id,read_num,updated_at')->groupBy('novel_id')->orderby('sum','desc');  
        }
        $grid->column('id', __('日志ID'));
        $grid->column('sum', __('总支付人数'));
        $grid->column('总阅读人数')->display(function() use($customer){
            return $this->read_num($this->novel_id,$customer);
        });
        $grid->column('充值阅读比例')->display(function()use($customer){
            $read_num_now = $this->read_num($this->novel_id,$customer);
            return round(($this->sum/$read_num_now)*100,3).'%';
        });
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->column('novel.title', __('小说'));
        $grid->actions(function (Grid\Displayers\Actions $actions) use($uri){
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->append('<a target="_blank" href="/'. config('admin.route.prefix').'/novel_pay_info?novel_id='.$actions->row->novel_id .'" class="grid-row-edit btn btn-xs btn-primary" title="查看详情"><i class="fa fa-list-ol"></i> 查看详情</a>');

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
