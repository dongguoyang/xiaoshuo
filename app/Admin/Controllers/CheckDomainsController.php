<?php

namespace App\Admin\Controllers;

use App\Admin\Models\CheckDomain;
use App\Logics\Traits\ApiResponseTrait;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;

class CheckDomainsController extends AdminController
{
    use ApiResponseTrait;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '检测域名管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CheckDomain);
        $grid->column('id', __('Id'))->sortable();
        $grid->column('host', __('域名'))->sortable();
        $grid->column('status', __('状态'))->switch(CheckDomain::switchStatus())->sortable();

        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('host', '域名');
            $filter->equal('status', '状态')->select(CheckDomain::switchStatus(1));
        });
        $grid->tools(function ($actions) {
            // append一个操作
            $actions->append('<label class="btn btn-danger btn-sm"><a href="checkdomains/create" title="批量添加" style="color: #fff;"><i class="fa fa-plus"></i> 批量添加 </label></a>');
            //$actions->append('<label class="btn btn-danger btn-sm"><a href="checkdomains/dels?ids=all" title="清空所有" style="color: #fff;"><i class="fa fa-adjust"></i> 清空所有 </label></a>');
            $actions->append('<label data-url="/'.config('admin.route.prefix').'/checkdomains/dels" data-allids="1" data-ids="all" title="清空所有" class="confirm2doall btn btn-danger btn-sm"><i class="fa fa-trash"></i> 清空所有</label>');
        });
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
            $actions->append('<label data-url="/'.config('admin.route.prefix').'/checkdomains/dels" data-status="2" title="删除" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-trash"></i> 删除</label>');
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
        $show = new Show(CheckDomain::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('host', __('Host'));
        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CheckDomain);

        if (request()->route()->getActionMethod() === 'edit') {
            $form->text('host', '域名')->rules('required', [
                'required' => '域名必填写',
            ]);
        } else {
            $form->textarea('host', '域名列表')->rows(10)->rules('required', [
                'required' => '域名必填写',
            ]);
        }

        $form->switch('status', __('状态'))->states(CheckDomain::switchStatus())->default(CheckDomain::STATUS_1);
        $form->footer(function ($footer) {
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


        //保存前回调
        $form->saving(function (Form $form) {
            if ($form->model()->id > 0) {} else {
                $host = request()->post('host');
                $status = request()->post('status') == 'off' ? 0 : 1;
                if (!is_string($status)) {
                    $status = $status == 'off' ? 0 : 1;
                }
                $urls = explode("\n", strtolower($host));
                $data = [];
                foreach ($urls as $k => $v) {
                    $v = TrimAll($v);
                    if ($v) {
                        $data[] = $v;
                    }
                }
                $re = 0;$re_list = '';
                foreach ($data as $v) {
                    if (CheckDomain::where('host', $v)->select('id')->first()) {
                        $re++;
                        $re_list .= $v . ', ';
                        continue;
                    }
                    CheckDomain::create(['host' => $v, 'status' => $status]);
                }
                $success = new MessageBag([
                    'title' => '保存成功',
                    'message' => '保存成功！' . ($re>0 ? ($re.'个域名重复，' . substr($re_list, 0, -2) . '添加失败！') : ''),
                ]);
                return redirect('/'. config('admin.route.prefix') .'/checkdomains')->with(compact('success'));
            }
        });
        return $form;
    }

    /**
     * 删除域名
     */
    public function Dels()
    {
        $ids = request()->input('ids');

        if (!$ids) {
            return $this->result([], 2000, '参数错误！');
        }

        if ($ids == 'all') {
            CheckDomain::where('id', '>', 0)->delete();
            return $this->result('ok', 0, '删除成功！');
        }

        $ids = explode(',', $ids);
        $num = count($ids);

        CheckDomain::whereIn('id', $ids)->delete();

        return $this->result($num, 0, '删除成功！');
    }
}
