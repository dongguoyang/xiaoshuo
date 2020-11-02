<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/29
 * Time: 21:38
 */

namespace App\Admin\Controllers;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Models\RegNullData;

class RegNullDataController extends AdminController
{
    protected $title="未采集到的信息";
    protected function grid(){
        $grid=new Grid(new RegNullData);
        $grid->column('id',__('ID'));
        $grid->column('novels.title',__('小说标题'));
        $grid->column('url',__('小说url地址'));
        $grid->column('num',__('小说章节'));
        $grid->column('reg_id',__('配置信息ID'));
        $grid->column('created_at',__('创建时间'));
        $grid->column('updated_at',__('更改时间'));
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableColumnSelector();
        $grid->disableTools();
        $grid->disableExport();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
        });
        return $grid;
    }

}