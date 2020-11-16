<?php

namespace App\Admin\Controllers;

use App\Logics\Models\ReadNovelLogs;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ReadNovelInfoController extends AdminController
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
        $novel_id = request()->input('novel_id');
        $sum = request()->input('sum');
        $grid = new Grid(new ReadNovelLogs);
        if($this->customer()->id != 1){
            $grid->model()->where('customer_id',$this->customer()->id)->where('novel_id',$novel_id)->selectRaw('user_read_num as sum, id,novel_id,customer_id,name,user_read_num,novel_section_id,updated_at')->orderBy('sum', 'desc');
        }else{
            $grid->model()->where('novel_id',$novel_id)->selectRaw('sum(user_read_num) as sum, id,novel_id,customer_id,name,user_read_num,novel_section_id,updated_at')->groupBy('novel_section_id')->orderBy('sum', 'desc');
        }
        $grid->column('novel_id', __('小说id'));
        $grid->column('章节名')->display(function(){
            $novel_sections =  Db::table('novel_sections')->where('novel_id',$this->novel_id)->where('num',$this->novel_section_id)->first();
            return $novel_sections->title;
        });
        $grid->column('sum', __('阅读人数'));
        $grid->column('比例')->display(function()use($sum){
            return round(($this->sum/$sum)*100,3).'%';
        });
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->actions(function (Grid\Displayers\Actions $actions){
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
