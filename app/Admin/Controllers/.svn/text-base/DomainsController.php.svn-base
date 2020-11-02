<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Domain;
use App\Admin\Models\DomainType;
use App\Logics\Traits\ApiResponseTrait;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\MessageBag;

class DomainsController extends AdminController
{
    use ApiResponseTrait;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '域名管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Domain);
        $grid->tools(function ($tools) {
            $map = [
                ['status', 1],
            ];
            if ($type_id = request()->input('type_id', 0)) {
                $map[] = ['type_id', $type_id];
            }
            $normal = Domain::where($map)->count();
            $tools->append(' <span style="margin: auto 20px;" class="btn btn-info">正常域名数： <span style="font-weight: bold;"> '.$normal.'</span></span> ');
        });

        $grid->model()->orderBy('status', 'desc');
        if (request()->input('type_id')) {
            $grid->model()->where('type_id', request()->input('type_id'));
        }
        $grid->column('id', __('Id'));
        $grid->column('host', __('域名'));
        $grid->column('customer.name', __('使用人'));
        $grid->column('status', __('状态'))->switch(Domain::switchStatus())->sortable();
        $grid->domainType()->name('域名类型')->sortable();
        $grid->column('updated_at', __('最近更新'))->sortable();

        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('host', '域名');
            $filter->equal('status', '状态')->select(Domain::switchStatus(1));
            $filter->equal('type_id', '域名类型')->select(DomainType::select(['id', 'name'])->pluck('name', 'id'));
        });
        $grid->tools(function ($actions) {
            // append一个操作
            $actions->append('<label class="btn btn-warning btn-sm"><a href="domains/create?type_id=' . request()->input('type_id', 0) . '" title="批量添加" style="color: #fff;"><i class="fa fa-plus"></i> 批量添加 </label></a>');
            $actions->append('<label class="confirm2doall btn btn-danger btn-sm" data-url="/'.config('admin.route.prefix').'/domains/dels" data-status="2" title="批量删除" style="color: #fff;"><i class="fa fa-minus"></i> 批量删除 </label>');
        });
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
            $actions->append('<label data-url="/'.config('admin.route.prefix').'/domains/dels" data-status="2" title="删除" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-trash"></i> 删除</label>');
        });
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
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
        $show = new Show(Domain::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('host', __('Host'));
        $show->field('app', __('App'));
        $show->field('status', __('Status'));
        $show->field('type_id', __('Type no'));
        $show->field('ip_num', __('Ip num'));
        $show->field('view_num', __('View num'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Domain);

        if (request()->route()->getActionMethod() === 'edit') {
            $form->text('host', '域名')->rules('required', [
                'required' => '域名必填写',
            ]);
        } else {
            $form->textarea('host', '域名列表')->rows(10)->rules('required', [
                'required' => '域名必填写',
            ]);
            $form->select('type_id', '域名类型')->options(DomainType::select(['name', 'id'])->get()->pluck('name', 'id'))->default(request()->input('type_id', 0));
        }

        //$form->text('host', __('域名'));
        //$form->text('app', __('使用组别'));
        // $form->select('app', __('使用组别'))->options($this->apiTypes());
        $form->switch('status', __('状态'))->states(Domain::switchStatus())->default(Domain::STATUS_1);
        //$form->number('type_id', __('组别序号'))->min(1)->max(250)->default(1);
        //$form->number('ip_num', __('Ip num'));
        //$form->number('view_num', __('View num'));

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
                $type_id = request()->post('type_id');
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
                $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
                foreach ($data as $v) {
                    if (Domain::where('host', $v)->select('id')->first()) {
                        $re++;
                        $re_list .= $v . ', ';
                        continue;
                    }
                    Domain::create(['host' => $v, 'status' => $status, 'type_id' => $type_id, 'app'=>$customer['id']]);
                }
                $success = new MessageBag([
                    'title' => '保存成功',
                    'message' => '保存成功！' . ($re>0 ? ($re.'个域名重复，' . substr($re_list, 0, -2) . '添加失败！') : ''),
                ]);
                $this->lmCacheForget($type_id);
                return redirect('/'.config('admin.route.prefix').'/domains?type_id=' . $type_id)->with(compact('success'));
            }
        });
        $form->saved(function (Form $form) {
            $type_id = $form->model()->type_id;
            $id = $form->model()->id;
            $this->lmCacheForget($type_id, $id);
        });
        return $form;
    }

    // 清除配置缓存
    public function lmCacheForget($type, $id=0) {
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $key = config('app.name') . 'domain_list_'.$type.'_'.$customer['id'];
        if (Cache::has($key)){
            Cache::forget($key);
        }
        $key = config('app.name') . 'domain_type_info_'.$type;
        if (Cache::has($key)){
            Cache::forget($key);
        }
        $key = config('app.name') . 'domain_list_'.$type.'_app_0';
        if (Cache::has($key)){
            Cache::forget($key);
        }
        $key = config('app.name') . 'domain_list_'.$type.'_app_'.request()->input('app');
        if (Cache::has($key)){
            Cache::forget($key);
        }
        if ($id)
        {
            $info = Domain::select(['id', 'app', 'type_id'])->find($id);
            $key = config('app.name') . 'domain_list_'.$type.'_app_'.$info['app'];
            if (Cache::has($key)) Cache::forget($key);
            $key = config('app.name') . 'domain_list_'.$type.'_'.$info['app'];
            if (Cache::has($key)) Cache::forget($key);
            $key = config('app.name') . 'domain_list_'.$info['type_id'].'_'.$info['app'];
            if (Cache::has($key)) Cache::forget($key);
        }
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

        $ids = explode(',', $ids);
        $num = count($ids);

        if ($num == 1) {
            $info = Domain::select(['type_id', 'id'])->find($ids[0]);
            $types = [ 0=>[ 'id'=>$info['type_id'] ] ];
        } else {
            $types = DomainType::select(['id'])->get()->toArray();
        }

        Domain::whereIn('id', $ids)->delete();
        foreach ($types as $v) {
            $this->lmCacheForget($v['id']);
        }

        return $this->result($num, 0, '删除成功！');
    }
}
