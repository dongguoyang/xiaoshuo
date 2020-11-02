<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/29
 * Time: 21:26
 */

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Models\RegError;
class RegErrorController extends AdminController
{
    protected $title = '采集错误返回';
    public function grid(){
        $grid=new Grid(new RegError());
        $grid->column('id',__('ID'));
        $grid->column('code',__('返回码'));
        $grid->column('msg',__('返回提示'));
        $grid->column('created_at',__('创建时间'));
        $grid->column('updated_at',__('修改时间'));
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