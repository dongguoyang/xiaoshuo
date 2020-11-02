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

class NovelPayInfoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户充值阅读记录';
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
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $novel_id = request()->input('novel_id');
        $grid = new Grid(new NovelPayStatistics);
        //$grid->model()->where('type', 3)->where('type_id','>',0)->where('status',1)->orderBy('id', 'desc');
        if($customer['pid']){
            $grid->model()->where('customer_id',$customer['id'])->where('novel_id',$novel_id)->orderBy('date_belongto', 'desc');
        }else{
            $grid->model()->where('novel_id',$novel_id)->selectRaw('sum(pay_num) as pay_num, date_belongto,id,novel_id,customer_id,read_num,updated_at')->groupBy('date_belongto')->orderBy('date_belongto', 'desc');
        }
        $grid->column('id', __('日志ID'));
        $grid->column('date_belongto', __('时间'));
        $grid->column('pay_num', __('总支付人数'));
        $grid->column('总阅读人数')->display(function() use($customer){
            return $this->read_num($this->novel_id,$customer,$this->date_belongto);
        });
        $grid->column('充值阅读比例')->display(function()use($customer){
            $read_num_now = $this->read_num($this->novel_id,$customer,$this->date_belongto);
            return round(($this->pay_num/$read_num_now)*100,3).'%';
        });
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->column('novel.title', __('小说'));
                $grid->disableCreateButton();
        $grid->disableActions();
        $grid->actions(function (Grid\Displayers\Actions $actions) use($uri){
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
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
