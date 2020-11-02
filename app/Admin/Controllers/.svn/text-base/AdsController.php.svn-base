<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Ad;
use App\Admin\Models\AdPosition;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class AdsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '广告管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Ad);

        $grid->model()->orderBy('status', 'desc');
        $stat = request()->input('status', -2);
        if ($stat >= 0) {
            $grid->model()->where('status', $stat);
        }

        $grid->id('ID')->sortable();
        $grid->title('广告名称');
        $grid->type('显示类型')->display(function ($type) {return Ad::getType(1)[$type];});

        $grid->img('图片')->image(120, 30);
        $grid->status("状态")->switch(Ad::switchStatus())->sortable();
        $grid->url('链接地址');
        $grid->sort('返回排序')->editable()->sortable();
        $grid->customer('特殊标识');
        $grid->updated_at('最近更新');
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->column(1 / 3, function ($filter) {
                // 在这里添加字段过滤器
                $filter->like('title', '广告名称');

            });
            $filter->column(1 / 3, function ($filter) {
                $filter->like('url', '链接地址');
            });
            $filter->column(1 / 3, function ($filter) {
                $filter->like('customer', '特殊标识');
            });
        });
        $grid->actions(function ($actions) {
            $actions->disableView();
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
        $show = new Show(Ad::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('广告名称'));
        $show->field('desc', __('简述'));
        $show->field('img', __('图片'))->image();
        $show->field('url', __('Url'));
        $show->field('status', __('状态'))->as(function ($status) {
            return Ad::switchStatus(1)[$status];
        })->label();
        $show->field('view_num', __('显示次数'));
        $show->field('click_num', __('点击次数'));
        $show->field('type', __('Type'))->as(function ($type) {
            return Ad::getType(1)[$type];
        })->label();;
        $show->field('customer', __('特殊标识'));
        $show->field('sort', __('排序'));

        $show->field('updated_at', __('最近更新'))->as(function ($updated_at) {
            return date('Y-m-d H:i:s', $updated_at);
        });
        $show->field('created_at', __('创建时间'))->as(function ($created_at) {
            return date('Y-m-d H:i:s', $created_at);
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
        $form = new Form(new Ad);

        $form->text('title', '标题')->rules('required', [
            'required' => '标题必须填写',
        ]);
        $form->text('desc', '广告简述');
        $form->image('img', __('图片'))
            ->move(config('app.name') . '/ad/imgs')
            ->required()->uniqueName()->destroy();

        $form->text('url', '链接地址')->required();
        $form->select('type', '显示类型')->options(Ad::getType())->required();
        $form->number('sort', '排序')->default(100)->min(1)->max(250);
        $form->switch('status', '状态')->states(Ad::switchStatus())->default(Ad::STATUS_0);
        $form->text('customer', '特殊标识')->default('0');

        $form->tools(function (Form\Tools $tools) {
            // 去掉跳转列表按钮
            $tools->disableListButton();
        });
        $form->footer(function ($footer) {
            // 去掉`重置`按钮
            // $footer->disableReset();
            // 去掉`提交`按钮
            // $footer->disableSubmit();
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();
            // 去掉`继续创建`checkbox
            // $footerer->disableCreatingCheck();
        });
        $form->saved(function (Form $form) {
            $id = $form->model()->id;
            $url = $form->model()->url;
            if (strpos($url, '/ad/toshow') !== false) {
                Ad::where('id', $id)->update(['url' => '/ad/toshow?aid=' . $id]);
            }
            $this->lmCacheForget($id);
        });

        return $form;
    }

    public function destroy($id) {
        //查看改广告是否在广告位里面
        $ad_positions = AdPosition::where(['ad_id' => $id])->count();
        if ($ad_positions > 0) {
            return response()->json([
                'status' => 0,
                'message' => '存在广告位绑定该广告，删除失败',
            ]);
        }
        Ad::where(['id' => $id])->delete();
        return response()->json([
            'status' => 1,
            'message' => '删除成功',
        ]);
    }
    /**
     * 清除文章列表缓存
     */
    public function lmCacheForget($id = 0) {
        $key = config('app.name'). 'ad2tpl_info_'.$id;
        if (Cache::has($key)) {
            Cache::forget($key);
        }
        $key = config('app.name') . 'ad_alink_list_lb_hongbao';
        if (Cache::has($key)) {
            Cache::forget($key);
        }
        $key = config('app.name') . 'ad_alink_list_lb_hongbao_back';
        if (Cache::has($key)) {
            Cache::forget($key);
        }
        $key = config('app.name') . 'ad_alink_list_lb_hongbao_friend';
        if (Cache::has($key)) {
            Cache::forget($key);
        }
        $key = config('app.name') . 'ad_alink_list_lb_hongbao_timeline';
        if (Cache::has($key)) {
            Cache::forget($key);
        }
        $key = config('app.name') . 'ad_rand_id_list_back';
        if (Cache::has($key)) {
            Cache::forget($key);
        }
        $key = config('app.name') . 'ad_rand_id_list_friend';
        if (Cache::has($key)) {
            Cache::forget($key);
        }
        $key = config('app.name') . 'ad_rand_id_list_timeline';
        if (Cache::has($key)) {
            Cache::forget($key);
        }
        $key = config('app.name') . 'ad_rand_id_list';
        if (Cache::has($key)) {
            Cache::forget($key);
        }
        $key = config('app.name') . 'ad_page_ads_egt1';
        if (Cache::has($key)) {
            Cache::forget($key);
        }
        $key = config('app.name') . 'ad_page_ads_egt2';
        if (Cache::has($key)) {
            Cache::forget($key);
        }
        $key = config('app.name') . 'ad_page_ads_egt3';
        if (Cache::has($key)) {
            Cache::forget($key);
        }

    }
}
