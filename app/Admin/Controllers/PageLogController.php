<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grids\Filters\TimestampBetween;
use App\Admin\Models\PageLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PageLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '页面日志';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PageLog());
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('日志ID'));
        $grid->column('bl_date', __('归属日期'))->display(function ($val){
            return substr($val, 0, 4) . '-' . substr($val, 4, 2) . '-' . substr($val, 6);
        });
        $grid->column('section_null', __('章节内容为空次数'));
        $grid->column('unsub_user', __('未关注注册用户数'));
        $grid->column('created_at', __('创建时间'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->use(new TimestampBetween('created_at', '创建时间'))->date();
        });
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
        });

        return $grid;
    }

}
