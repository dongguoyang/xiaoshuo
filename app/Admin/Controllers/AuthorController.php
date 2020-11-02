<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Author;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class AuthorController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '作者管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Author);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('作者ID'));
        $grid->column('name', __('笔名'));
        $grid->column('status', __('是否启用'))->switch(Author::switchStatus(0, ['否', '是']));
        $grid->column('updated_at', __('更新于'));
        $grid->column('created_at', __('创建于'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('name', '笔名');
            $filter->equal('status', '是否启用')->radio([
                0   =>  '否',
                1   =>  '是'
            ]);
        });
        $grid->disableExport();
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($uri) {
            $actions->disableDelete();// 去掉删除
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            $actions->append('<a href="'. $uri.'/'.$actions->getKey() .'/edit" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
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
        $show = new Show(Author::findOrFail($id));

        $show->field('id', __('作者ID'));
        $show->field('name', __('笔名'));
        $show->field('status', __('是否启用'))->as(function ($status) {
            return $status > 0 ? '是' : '否';
        });
        $show->field('updated_at', __('更新于'));
        $show->field('created_at', __('创建于'));

        $show->panel()
            ->tools(function (Show\Tools $tools) {
                $tools->disableDelete();
            });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Author);

        $form->text('name', __('笔名'))->rules([
            'required', 'regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u',
            'between:2,64',
            Rule::unique('authors')->where(function ($query) use ($form) {
                $query->where('id', '<>', $form->model()->id);
            })
        ], ['regex' => '笔名只能由汉字、字母、数字和下划线组成']);
        $form->switch('status', __('是否启用'))->states(Author::switchStatus())->default(1)->required();

        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
        });

        return $form;
    }
}
