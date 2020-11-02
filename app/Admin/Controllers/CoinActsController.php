<?php

namespace App\Admin\Controllers;

use App\Admin\Models\CoinAct;
use App\Logics\Repositories\src\DomainRepository;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CoinActsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '书币活动管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CoinAct);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('ID'));
        $grid->column('name', __('活动名称'));
        $grid->column('coin', __('书币'));
        $grid->column('start_at', __('开始时间'));
        $grid->column('end_at', __('结束时间'));
        $grid->column('send_num', __('领取数量'));

        $grid->column('status', __('启用状态'))->switch(CoinAct::switchStatus());
        $grid->column('updated_at', __('更新时间'));

        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $domainRep = new DomainRepository();
        $host = $domainRep->randOne(2, $customer['id']);

        $grid->column('link', __('链接'))->display(function () use ($host, $customer) {
            $params = encrypt(json_encode(['route'=>'coin_act', 'id'=>$this->id, 'cid'=>$customer['id'], 'customer_id'=>$customer['id'], 'dtype'=>2]));
            $url = $host . route('jumpto', ['passwd'=>$params], false);
            return '<span class="copybtn" data-content="'. $url .'" title="点击复制"><i class="fa fa-clipboard"></i> '. substr($url, 0, 32) .'</span>';
        });

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
        $show = new Show(CoinAct::findOrFail($id));

        $show->field('id', __('奖品ID'));
        $show->field('name', __('名称'));

        $show->field('count', __('总量'));
        $show->field('send_num', __('送出'));

        $show->field('updated_at', __('更新时间'));
        $show->field('created_at', __('创建时间'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CoinAct);


        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $form->hidden('customer_id', __('客户ID'))->default($customer['id']);
        $form->text('name', __('名称'))->required();
        $form->datetime('start_at', __('开始时间'))->required();
        $form->datetime('end_at', __('结束时间'))->required();
        $form->number('coin', __('书币'))->default(100)->required()->min(1);
        $form->number('count', __('总量'))->default(-1)->required()->min(-1)->help('-1 表示不限');

        $form->switch('status', __('启用状态'))->states(CoinAct::switchStatus())->default(1)->required();

        $form->tools(function (Form\Tools $tools) {
            // $tools->disableList();// 去掉`列表`按钮
            $tools->disableDelete();// 去掉`删除`按钮
            $tools->disableView();// 去掉`查看`按钮
        });
        $form->footer(function ($footer) {
            $footer->disableViewCheck();// 去掉`查看`checkbox
            $footer->disableEditingCheck();// 去掉`继续编辑`checkbox
            $footer->disableCreatingCheck();// 去掉`继续创建`checkbox
        });
        return $form;
    }
}
