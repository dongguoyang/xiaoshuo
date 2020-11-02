<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Goods;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;

class GoodsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '礼物管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Goods);

        $grid->column('id', __('礼物ID'));
        $grid->column('img', __('图标'))->image('', 80, 80);
        $grid->column('name', __('名称'));
        $grid->column('coin', __('价值（书币）'));
        $grid->column('count', __('总量'));
        $grid->column('stock', __('库存'));
        $grid->column('sale_num', __('售出'));
        $grid->column('sort', __('排序'));
        $grid->column('status', __('启用状态'))->switch(Goods::switchStatus());
        $grid->column('updated_at', __('更新时间'));
        $grid->column('created_at', __('创建时间'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('name', '名称');
            $filter->equal('status', '状态')->radio([
                0   =>  '停用',
                1   =>  '启用'
            ]);
        });
        $grid->disableExport();
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($uri) {
            $actions->disableDelete();// 去掉删除
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            // $actions->append('<label data-url="/'.config('admin.route.prefix').'/domains/dels" data-status="2" title="删除" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-trash"></i> 删除</label>');
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
        $show = new Show(Goods::findOrFail($id));

        $show->field('id', __('礼物ID'));
        $show->field('name', __('名称'));
        $show->field('coin', __('价值（书币）'))->image('', 80, 80);
        $show->field('img', __('图标'));
        $show->field('count', __('总量'));
        $show->field('stock', __('库存'));
        $show->field('sale_num', __('售出'));
        $show->field('sort', __('排序'));
        $show->field('status', __('启用状态'))->as(function ($status) {
            return $status > 0 ? '正常' : '停用';
        });
        $show->field('updated_at', __('更新时间'));
        $show->field('created_at', __('创建时间'));

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
        $form = new Form(new Goods);

        $form->text('name', __('名称'))->rules([
            'required', 'regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u',
            'between:2,64',
            Rule::unique('goodses')->where(function ($query) use ($form) {
                $query->where('id', '<>', $form->model()->id);
            })
        ], ['regex' => '名称只能由汉字、字母、数字和下划线组成']);
        $form->number('coin', __('价值（书币）'))->default(10)->required();
        $form->image('img', __('图标'))->uniqueName()->move('goods/images')->rules(['required', 'image']);
        $form->number('count', __('总量'))->default(100000)->required();
        if($form->isCreating()) {
            $form->number('stock', __('库存'))->default(100000)->help('只需设置总量即可，此项自动设置');
        } else {
            $form->number('stock', __('库存'))->default(100000)->readonly()->help('只需设置总量即可，此项自动设置');
        }
        $form->number('sale_num', __('售出'))->default(0);
        $form->number('sort', __('排序'))->default(0);
        $form->switch('status', __('启用状态'))->states(Goods::switchStatus())->default(1)->required();

        $form->saving(function (Form $form) {
            if($form->isCreating()) {
                if($form->count != ($form->stock + $form->sale_num)) {
                    $error = new MessageBag([
                        'title'   => '礼物数量配置错误',
                        'message' => '礼物总量、库存量、售出量不匹配',
                    ]);
                    return back()->with(compact('error'));
                }
            } else {
                if($form->count <= $form->sale_num) {
                    $error = new MessageBag([
                        'title'   => '礼物数量配置错误',
                        'message' => '礼物总量不足',
                    ]);
                    return back()->with(compact('error'));
                }
                $form->stock = $form->count - $form->sale_num;
            }
        });

        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
        });

        return $form;
    }
}
