<?php

namespace App\Admin\Controllers;

use App\Admin\Models\NovelSection;
use App\Admin\Models\Novel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Admin\Actions\Novel\ImportNovelSectionAction;
use App\Admin\Forms\ImportNovelSection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class NovelSectionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '章节管理';
    protected $file_notice = '仅支持 .txt、.docx 类型的文件，文件命名最好遵循规范如：[小说名]第N章 - 章节标题.txt / .docx . 内容规范：非空的首行必须包含章节序号（章节数）和标题（标题可空），格式：[序号N] . 单次请勿上传过多文件';
    const SECTIONS_FIELD = 'sections';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new NovelSection);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('章节ID'));
        $grid->column('novel', __('小说'))->display(function () {
            return '【'.$this->novel['id'].'】'.$this->novel['title'];
        });
        $grid->column('num', __('序号'));
        $grid->column('title', __('名称'));
        $grid->column('content', __('内容节点'));
        $grid->column('updated_at', __('更新于'));
        $grid->column('created_at', __('创建于'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('title', '名称');
            $filter->equal('novel_id', '小说ID');
            $filter->equal('num', '序号');
        });
        $grid->disableExport();
        //$grid->disableRowSelector();
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($uri) {
            $actions->disableDelete();
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            $actions->append('<a href="'. $uri.'/'.$actions->getKey() .'/edit" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
            $actions->append('<a href="javascript:void(0);" data-id="' .$actions->getKey(). '" class="grid-row-delete grid-row-delete2 btn btn-xs btn-danger" title="删除"><i class="fa fa-trash"></i> 删除</a>');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new ImportNovelSectionAction());
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
        $show = new Show(NovelSection::findOrFail($id));

        $show->field('id', __('章节ID'));
        $show->field('novel', __('小说'))->as(function () {
            return '【'.$this->novel['id'].'】'.$this->novel['title'];
        });
        $show->field('num', __('序号'));
        $show->field('title', __('名称'));
        $show->field('content', __('内容节点'));
        $show->field('updated_num', __('更正次数'));
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
        $form = new Form(new NovelSection);
        if($form->isCreating()) {
            $form->select('novel_id', __('小说'))->ajax('/administrator/api/novels')->required();
        } else {
            $form->select('novel_id', __('小说'))->options(function ($novel_id) {
                $novel = Novel::find($novel_id);
                if ($novel) {
                    return [$novel->id => $novel->title];
                }
            })->ajax('/administrator/api/novels')->readonly();
        }
        $form->number('num', __('序号'))->rules([
            'required', 'integer', 'between:1,99999',
            Rule::unique('novel_sections')->where(function ($query) use ($form) {
                $query->where('id', '<>', $form->model()->id);
            })
        ]);
        $form->text('title', __('名称'))->rules([
            'required',
            Rule::unique('novel_sections')->where(function ($query) use ($form) {
                $query->where('id', '<>', $form->model()->id);
            })
        ]);;
        $form->url('content', __('内容节点'))->required();
        $form->switch('updated_num', __('更正次数'))->default(1);

        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
        });
        $form->footer(function (Form\Footer $footer) {
            // 去掉`重置`按钮
            $footer->disableReset();

            // 去掉`查看`checkbox
            $footer->disableViewCheck();

            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();

            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        });

        return $form;
    }

    public function import(Content $content) {
        return $content
            ->title($this->title())
            ->description('导入章节')
            ->body(new ImportNovelSection);
    }

    public function importForm() {
        set_time_limit(0);
        $form = new Form(new NovelSection);

        $form->setAction(route('novel_sections_import_do'));

        $form->number('novel_id', __('小说ID'))->required();
        $form->multipleFile(self::SECTIONS_FIELD, '请选择文件')
            ->help($this->file_notice)
            ->removable()
            ->required();

        $form->tools(function (Form\Tools $tools) {
            // 去掉`列表`按钮
            // $tools->disableList();

            // 去掉`删除`按钮
            $tools->disableDelete();

            // 去掉`查看`按钮
            $tools->disableView();
        });
        $form->footer(function (Form\Footer $footer) {
            // 去掉`重置`按钮
            $footer->disableReset();

            // 去掉`查看`checkbox
            $footer->disableViewCheck();

            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();

            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        });
        return $form;
    }

    public function doImport() {
        $request = request();
        if(!$request->isMethod('post')) {
            return response('Content Not Found', 404);
        }
        // 检查小说是否存在
        if(!$request->has('novel_id') || 1 > $novel_id = $request->post('novel_id', 0)) {
            return response('导入失败！请填写正确的小说ID', 200);
        }
        dump($novel_id);
        $novel = Novel::find($novel_id);
        $novel = $novel ? $novel->toArray() : [];
        dump($novel);
        if(!$request->hasFile(self::SECTIONS_FIELD) || $request->file(self::SECTIONS_FIELD)->isValid()) {
            return response('导入失败！请上传数据文件（'.$this->file_notice.'）', 200);
        }
        $sections = $request->file(self::SECTIONS_FIELD);
        dd($sections);
    }
    // 去除章节异常字符
    public function FreshSpecailChart() {
        set_time_limit(0);
        $novel_id = request()->input('novel_id');
        $section  = request()->input('section');
        $repstr   = request()->input('repstr');
        if (!$novel_id || !$section) {
            exit('<h1 style="color: red;">novel_id / section 参数错误，可用 repstr 附加替换的字符串，多个用||分隔开！；如想一次处理多个章节 section=1-100 的形式处理1-100章</h1>');
        }
        if (strpos($section, '-')) {
            $arr = explode('-', $section);
            $section = $arr[0];
            $max = $arr[1];
        } else {
            $max = $section;
        }

        while ($section <= $max) {
            $info = NovelSection::where('novel_id', $novel_id)->where('num', $section)->select(['id', 'content'])->first();
            if (!$info) break;
            if (strpos($info['content'], 'http')===false) {
                $info['content'] = config('app.url') . $info['content'];
            }
            $content = @file_get_contents($info['content']);
            $rep = ['&amp;', 'amp;', 'quot;'];
            if ($repstr) {
                $reparr = explode('||', urldecode($repstr));
                $rep = array_merge($rep, $reparr);
            }
            $content = str_replace($rep, '', $content);
            if ($content && strlen($content)>300) {
                $path = explode('.com/', $info['content'])[1];
                if (Storage::disk('oss')->put($path, $content)) {
                    $url = Storage::disk('oss')->url($path);
                    $info->content = $url;
                    $info->save();
                    $key = config('app.name') . 'novel_'.$novel_id.'_section_'.$section.'_info';
                    Cache::forget($key);
                    echo $url . "<br>\r\n\r\n<br>";
                }
            }
            echo $content;
            echo "<br><br><br><br><br><br>";
            $section++;
        }
        exit('修改完毕');
    }
    // 重置章节样式
    public function ResetStyle() {
        set_time_limit(0);
        $novel_id = request()->input('novel_id');
        $section  = request()->input('section');
        $repstr   = request()->input('repstr');
        if (!$novel_id || !$section) {
            exit('<h1 style="color: red;">novel_id / section 参数错误，可用 repstr 附加替换的字符串，多个用||分隔开！；如想一次处理多个章节 section=1-100 的形式处理1-100章</h1>');
        }
        if (strpos($section, '-')) {
            $arr = explode('-', $section);
            $section = $arr[0];
            $max = $arr[1];
        } else {
            $max = $section;
        }

        while ($section <= $max) {
            $info = NovelSection::where('novel_id', $novel_id)->where('num', $section)->select(['id', 'content'])->first();
            if (!$info) break;
            if (strpos($info['content'], 'http')===false) {
                $info['content'] = config('app.url') . $info['content'];
            }
            $content = @file_get_contents($info['content']);
            $rep = ['<br>', '<br/>', '<br />'];
            if ($repstr) {
                $reparr = explode('||', urldecode($repstr));
                $rep = array_merge($rep, $reparr);
            }
            $content = str_replace($rep, '</p><p>', $content);
            if ($content && strlen($content)>300) {
                $path = explode('.com/', $info['content'])[1];
                if (Storage::disk('oss')->put($path, $content)) {
                    $url = Storage::disk('oss')->url($path);
                    $info->content = $url;
                    $info->save();
                    $key = config('app.name') . 'novel_'.$novel_id.'_section_'.$section.'_info';
                    Cache::forget($key);
                    echo $url . "<br>\r\n\r\n<br>";
                }
            }
            echo $content;
            echo "<br><br><br><br><br><br>";
            $section++;
        }
        exit('修改完毕');
    }
    // 追加章节到另一本小说后面
    public function AppendSection2Novel() {
        $novel_id = request()->input('novel_id');
        $append2novel  = request()->input('append2novel');
        if (!$novel_id || !$append2novel) {
            exit('<h1 style="color: red;">novel_id / append2novel 参数错误，novel_id 是需要复制的小说；append2novel 是复制后追加到这个小说后</h1>');
        }

        $end_str = '';
        $novel = Novel::select(['id', 'sections'])->find($append2novel);
        $end_str .= '追加前章节数 = ' . $novel['sections'];
        $appsection = NovelSection::where('novel_id', $append2novel)->orderBy('num', 'desc')->first();
        if (!$appsection) {
            exit('<h1 style="color: red;">追加小说异常！</h1>');
        }
        $num = $appsection['num'];
        $page = 1;
        while (true) {
            $list = NovelSection::where('novel_id', $novel_id)
                ->orderBy('num', 'asc')
                ->offset(($page - 1) * 200)
                ->limit(200)
                ->select(['title', 'content', 'num', 'updated_num', 'spider_url'])
                ->get();
            $list = $list->toArray();
            if (!$list) break;
            $sec_num = $num;
            foreach ($list as $k=>$item) {
                $sec_num++;
                $item['title'] = $this->resetAppendSecTitle($item['title'], $sec_num);
                $item['novel_id'] = $append2novel;
                $item['spider_url'] = "novel:{$novel_id},section:{$item['num']};append end.";
                $item['num'] = $sec_num;
                $item['updated_num']++;
                $list[$k] = $item;
            }
            if (NovelSection::insert($list)) {
                Novel::where('id', $novel['id'])->update(['sections' => $sec_num]);
                $page++;
                $num = $sec_num;
            }
        }
        Novel::where('id', $novel['id'])->update(['sections' => $num]);
        $end_str .= '____追加后章节数 = ' . $num;

        exit($end_str);
    }
    private function resetAppendSecTitle($title, $num) {
        if (mb_strpos($title, '第') !== false && mb_strpos($title, '章') !== false) {
            $arr = explode('章', $title);
            $title = '第'.$num.'章 ' . (isset($arr[1]) ? $arr[1] : '');
        } else {
            $title = '第'.$num.'章 ' . $title;
        }
        return $title;
    }

}
