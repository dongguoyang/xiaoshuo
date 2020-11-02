<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Comment;
use App\Admin\Models\Novel;
use App\Admin\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class CommentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '评论管理';// 目前只能通过平台管理员进行管理

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Comment);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('评论ID'));
        $grid->column('novel.title', __('小说'))->display(function ($novel) {
            return mb_substr($novel, 0, 30);
        });
        $grid->column('user_detail', __('用户'))->display(function () {
            return '<img src="'.$this->user_img.'" onerror="this.src=\'https://novelsys.oss-cn-shenzhen.aliyuncs.com/user/avatar/default-avatar.png\';" style="max-width:50px;max-height:50px" class="img img-thumbnail">&nbsp;&nbsp;<span style="color:#f15c5c;">'.$this->username.'</span>';
        });
        $grid->column('content', __('评论'))->limit(48);
        $grid->column('status', __('是否隐藏'))->switch(Comment::switchStatus(0, ['是', '否']));
        $grid->column('updated_at', __('更新于'));
        $grid->column('created_at', __('创建于'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->equal('novel_id', '小说')->select(function () {
                return Optional(Novel::orderBy('id', 'asc')->limit(10)->get())->pluck('title', 'id');
            })->ajax('/administrator/api/novels');
            $filter->equal('user_id', '用户')->select(function () {
                return optional(User::orderBy('id', 'asc')->limit(10)->get())->pluck('name', 'id');
            })->ajax('/administrator/api/users');
            $filter->equal('status', '是否隐藏')->radio([
                0   =>  '是',
                1   =>  '否'
            ]);
        });
        $grid->disableCreateButton();
        $grid->disableExport();
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($uri) {
            $actions->disableDelete();// 去掉删除
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            $actions->append('<a href="'. $uri.'/'.$actions->getKey() .'/edit" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
            $actions->append('<a href="javascript:void(0);" data-id="' .$actions->getKey(). '" class="grid-row-delete grid-row-delete2 btn btn-xs btn-danger" title="删除"><i class="fa fa-trash"></i> 删除</a>');
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
        $show = new Show(Comment::findOrFail($id));

        $show->field('id', __('评论ID'));
        $show->field('novel', __('小说'))->as(function ($novel) {
            return $novel['title'] ?: $novel['desc'];
        });
        $show->field('user_detail', __('用户'))->as(function () {
            return '<img src="'.$this->user_img.'" onerror="this.src=\'https://novelsys.oss-cn-shenzhen.aliyuncs.com/user/avatar/default-avatar.png\';" style="max-width:50px;max-height:50px" class="img img-thumbnail">&nbsp;&nbsp;<span style="color:#f15c5c;">'.$this->username.'</span>';
        });
        $show->field('content', __('评论'));
        $show->field('status', __('是否隐藏'))->as(function ($status) {
            return $status > 0 ? '否' : '是';
        });
        $show->field('updated_at', __('更新于'));
        $show->field('created_at', __('创建于'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Comment);

        $form->number('novel_id', __('小说ID'))->readonly();
        $form->number('user_id', __('用户ID'))->readonly();
        $form->text('content', __('评论'));
        $form->switch('status', __('是否隐藏'))->states(Comment::switchStatus(0, ['是', '否']))->default(1)->required();

        $form->disableEditingCheck();

        $form->disableCreatingCheck();

        $form->disableViewCheck();

        $form->disableReset();

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            // $tools->disableList();
        });
        $form->footer(function (Form\Footer $footer) {

            // 去掉`重置`按钮
            $footer->disableReset();

            // 去掉`提交`按钮
            // $footer->disableSubmit();

            // 去掉`查看`checkbox
            $footer->disableViewCheck();

            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();

            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();

        });

        return $form;
    }
}
