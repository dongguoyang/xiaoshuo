<?php

namespace App\Admin\Controllers;

use App\Admin\Models\ReadLog;
use App\Admin\Models\User;
use App\Admin\Models\Customer;
use App\Admin\Models\PlatformWechat;
use App\Admin\Models\Novel;
use App\Admin\Models\NovelSection;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ReadLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '阅读日志';
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
        $grid = new Grid(new ReadLog);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('日志ID'));
        $grid->column('customer_id', __('商户'))->display(function ($customer_id) {
            $customer = Customer::find($customer_id);
            return $customer['company'] ? '【'.$customer['company'].'】'.$customer['name'] : ($customer ? $customer['name'] : '');
        });
        /*$grid->column('platform_wechat_id', __('微信'))->display(function ($platform_wechat_id) {
            $platform_wechat = PlatformWechat::find($platform_wechat_id);
            return $platform_wechat ? $platform_wechat['app_name'] : '';
        });*/
        $grid->column('user_id', __('用户'))->display(function ($user_id) {
            $user = User::find($user_id);
            return $user ? $user->name : '<span style="color:red">-未知-</span>';
        });
        $grid->column('name', __('小说名'));
        $grid->column('title', __('最后阅读章节名'));
        $grid->column('sectionlist', __('阅读章节列表'))->view('admin.grid.array');/*->display(function ($sectionlist){
            $sectionlist = explode(',', trim($sectionlist, ','));
            return $sectionlist;
        });*/
        $grid->column('status', __('状态'))->display(function ($status) {
            return $status ? '<span style="color:#36ef08;">有效</span>' : '<span style="color:#ef0837;">失效</span>';
        });
        $grid->column('updated_at', __('更新于'));
        $grid->column('created_at', __('创建于'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->equal('status', '状态')->select([
                0   =>  '失效',
                1   =>  '有效'
            ]);
            if($this->customer()->isAdministrator()) {
                $filter->equal('customer_id', '商家')->select(function () {
                    return Optional(Customer::orderBy('id', 'asc')->limit(10)->get())->pluck('name', 'id');
                })->ajax('/administrator/api/customers');
            }
            $filter->equal('platform_wechat_id', '微信')->select(function () {
                return Optional(PlatformWechat::orderBy('id', 'asc')->limit(10)->get())->pluck('app_name', 'id');
            })->ajax('/administrator/api/platform-wechats');
            $filter->equal('user_id', '用户')->select(function () {
                return optional(User::orderBy('id', 'asc')->limit(10)->get())->pluck('name', 'id');
            })->ajax('/administrator/api/users');
            $filter->equal('novel_id', '小说名')->select(function () {
                return Optional(Novel::orderBy('id', 'asc')->limit(10)->get())->pluck('title', 'id');
            })->ajax('/administrator/api/novels');
            $filter->equal('novel_section_id', '章节名')->select(function () {
                return Optional(NovelSection::orderBy('id', 'desc')->limit(10)->get())->pluck('title', 'id');
            })->ajax('/administrator/api/novel-sections');
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

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ReadLog::findOrFail($id));

        $show->field('id', __('日志ID'));
        $show->field('customer_id', __('商户ID'));
        $show->field('platform_wechat_id', __('微信ID'));
        $show->field('user_id', __('用户ID'));
        $show->field('novel_id', __('小说ID'));
        $show->field('name', __('小说名'));
        $show->field('novel_section_id', __('章节ID'));
        $show->field('title', __('章节名称'));
        $show->field('status', __('状态'));
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
        $form = new Form(new ReadLog);

        $form->number('customer_id', __('商户ID'))->disable();
        $form->number('platform_wechat_id', __('微信ID'))->disable();
        $form->number('user_id', __('用户ID'))->disable();
        $form->number('novel_id', __('小说ID'))->disable();
        $form->text('name', __('小说名'))->disable();
        $form->number('novel_section_id', __('章节ID'))->disable();
        $form->text('title', __('章节名称'))->disable();
        $form->switch('status', __('状态'))->default(1)->disable();

        return $form;
    }
}
