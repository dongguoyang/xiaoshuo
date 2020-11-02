<?php

namespace App\Admin\Controllers;

use App\Admin\Models\CommonSet;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Content;

class CommonSetTypesController extends Controller {
    use HasResourceActions;

    public function index($type, Content $content) {
        $content->header('公共配置');
        $content->description('分类管理列表');
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公共配置', 'url' => '#'],
            ['text' => '分类管理']
        );

        if ($type == 1) {
            $list = CommonSet::get()->toArray();
        } else {
            $list = CommonSet::where('type', $type)->get()->toArray();
        }

        // 填充页面body部分，这里可以填入任何可被渲染的对象
        $content->body(view('admin.common_set_types.list', compact('list')));
        return $content;

    }
    public function open_value($type, $id, $value) {
        CommonSet::where('type', $type)->where('id', $id)->update(['value' => $value]);

        $this->lmCacheForget($type, $id);
        return redirect("/".config('admin.route.prefix')."/commonsettypes/index/$type");
    }
    public function open_status($type, $id, $status) {
        if ($status == 0) {
            CommonSet::where('type', $type)->where('id', $id)->update(['status' => 1]);
        } else {
            CommonSet::where('type', $type)->where('id', $id)->update(['status' => 0]);
        }
        $this->lmCacheForget($type, $id);
        return redirect("/".config('admin.route.prefix')."/commonsettypes/index/$type");
    }
    /**
     * 清除缓存
     */
    public function lmCacheForget($type = '', $id = '') {
        if ($type && $id) {
            $commonSet = new CommonSetsController();
            $info = CommonSet::select(['name'])->find($id);
            $commonSet->lmCacheForget($type, $info['name']);
            return true;
        }
    }
}
