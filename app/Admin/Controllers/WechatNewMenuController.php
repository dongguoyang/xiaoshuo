<?php

namespace App\Admin\Controllers;

use App\Admin\Models\CommonSet;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class WechatNewMenuController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '公共配置信息';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CommonSet);

        $grid->column('id', __('Id'));
        //if (request()->input('zmrtest')) {
            $grid->column('type', __('类型'));
            $grid->column('name', __('名称'));
        //}
        $grid->column('title', __('配置标题'));
        $grid->column('value', __('配置信息'))->display(function ($value, $column) {
            switch ($this->value_type) {
                case 'switch':
                    $ret = $column->switch(CommonSet::switchStatus());
                    break;
                case 'select':
                    $selects_json = trim(substr($this->title, strpos($this->title, '||') + 2));
                    if(IsJson($selects_json)) {
                        $selects = json_decode($selects_json, true);
                        $ret = $column->editable('select', $selects);
                    } else {
                        $ret = $selects_json;
                    }
                    break;
                case 'image':
                    if(Storage::exists($value)) {
                        $ret = $column->image('', 200, 200);
                    } else {
                        if (strpos($value, 'http') === 0) {
                            $ret = $column->image('', 200, 200);
                        } else {
                            $ret = $column->image('//'.request()->server('HTTP_HOST'), 200, 200);
                        }
                    }
                    break;
                case 'show':
                case 'json':
                default:
                    $ret = $value;
                    break;
            }
            return $ret;
        });
        // $grid->column('value_type', __('取值类型'));
        $grid->column('status', __('状态'))->switch(CommonSet::switchStatus());
        // $grid->column('sort', __('排序'));
        // $grid->column('created_at', __('创建时间'));
        $grid->column('updated_at', __('最近更新'));

        $grid->disableExport(); // 去掉导出按钮
        // 添加表格行工具
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function ($actions) use ($uri) {
            // $actions->disableDelete();// 去掉删除
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            // $actions->append('<label data-url="/'.config('admin.route.prefix').'/domains/dels" data-status="2" title="删除" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-trash"></i> 删除</label>');
            if ($actions->row['value_type'] == 'image') {
                $actions->append('<a href="/'. (config('admin.route.prefix').'/commonsetimg/'.$actions->getKey() .'/edit?value_type='.$actions->row['value_type'].'&title='.$actions->row['title']) .'" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
            } else {
                $actions->append('<a href="'. ($uri.'/'.$actions->getKey() .'/edit?value_type='.$actions->row['value_type'].'&title='.$actions->row['title']) .'" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
            }

            $actions->append('<a href="javascript:void(0);" data-id="' .$actions->getKey(). '" class="grid-row-delete grid-row-delete2 btn btn-xs btn-danger" title="删除"><i class="fa fa-trash"></i> 删除</a>');
        });
        /*$grid->filter(function($filter){
            $filter->disableIdFilter();// 去掉默认的id过滤器
            $filter->column(1/2, function ($filter) {
                $filter->equal('type', '类型');
                $filter->equal('name', '名称');
            });
            $filter->column(1/2, function ($filter) {
                $filter->like('title', '配置描述');
                $filter->equal('status', '状态')->select(CommonSet::switchStatus(1, ['停用', '正常']));
            });
        });*/

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
        $show = new Show(CommonSet::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('类型'));
        $show->field('name', __('名称'));
        $show->field('value', __('值'));
        $show->field('value_type', __('取值类型'));
        $show->field('title', __('配置描述'));
        $show->field('status', __('状态'));
        $show->field('sort', __('排序'));
        $show->field('created_at', __('创建时间'));
        $show->field('updated_at', __('最近更新'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CommonSet);



        if (request()->route()->getActionMethod() === 'edit') {
            // $form->display('type', __('类型'));
            // $form->display('name', __('名称'));
        } else {
            $form->text('type', __('类型'))
                ->rules('required', [
                    'required' => '类型必填',
                ]);
            $form->text('name', __('名称'))
                ->rules('required', [
                    'required' => '名称必填',
                ]);
            $form->select('value_type', __('取值类型'))
                ->options(['show' => '直接显示值', 'switch' => '开关形式', 'select' => '下拉选择形式', 'json' => 'JSON格式'])
                ->rules('required', [
                    'required' => '请选择值类型',
                ])->default('show');
        }

        if (request()->input('value_type') == 'json') {
            $form->jsonvalue('value', request()->input('title'));
        }elseif (request()->input('value_type') == 'image') {
            $form->image('value', request()->input('title'));
        } else {
            $form->text('title', __('配置名称'));
            $form->text('value', __('值'));
        }
        $form->switch('status', __('状态'))->states(CommonSet::switchStatus())->default(CommonSet::STATUS_1);
        $form->number('sort', __('排序[越小越靠前]'))->default(100)->min(1)->max(250);

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
        $form->saved(function (Form $form) {
            $type = $form->model()->type;
            $value = $form->model()->value;
            $name = $form->model()->name;

            $info = CommonSet::where('type', $type)->where('name', $name)->first();
            if ($info['value_type'] == 'switch') {
                $value = ($value == 'on' || $value == 1) ? 1 : 0;
                CommonSet::where('type', $type)->where('name', $name)->update(['value' => $value]);
            }
            $this->lmCacheForget($type, $name);
            // return redirect('/administrator/commonsettypes/index/' . $type);
        });
        return $form;
    }
    // 清除配置缓存
    public function lmCacheForget($type, $name='') {
        if ($type=='ad_alink' && $name){
            $key = config('app.name') . 'ad_alink_list_'.$name;
            if (Cache::has($key)){
                Cache::forget($key);
            }
        }
        if ($type=='adpage_ad'){
            $AdCont = new AdsController();
            $AdCont->lmCacheForget();
        }


        $key = config('app.name') . 'common_set_type_'.$type;
        if (Cache::has($key)){
            Cache::forget($key);
        }

        $key = config('app.name') . 'common_set_details_type_'.$type;
        if (Cache::has($key)){
            Cache::forget($key);
        }

    }

}
