<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Novel;
use App\Admin\Models\Author;
use App\Admin\Models\NovelCheckLog;
use App\Admin\Models\NovelSection;
use App\Admin\Models\Type;
use App\Admin\Models\ExtendLink;
use App\Admin\Models\Wechat;
use App\Admin\Actions\Novel\NovelCheckAction;
use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\ExtendLinkRepository;
use App\Logics\Repositories\src\NovelSectionRepository;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Logics\Traits\ApiResponseTrait;
use mysql_xdevapi\Exception;

class NovelController extends AdminController
{
    use ApiResponseTrait;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '小说管理';
    public $types = [];
    public $suitable_sex = [1 => '男生', 2 => '女生'];
    public $serial_status = [1 => '连载', 2 => '完结', 3 => '限免'];
    public $word_count_filter = [
        /*
         * number   =>  ['中文描述', '最小数', '最大数']
         */
        1   =>  ['10万字以下', 0, 10],
        2   =>  ['10-50万字', 10, 50],
        3   =>  ['50-100万字', 50, 100],
        4   =>  ['100-200万字', 100, 200],
        6   =>  ['200万字以上', 200, 99999999.99],
    ];
    public $search_types = [
        /*
         * number   =>  ['中文字段', '数据库字段']
         */
        1   =>  ['书名', 'title'],
        2   =>  ['作者', 'author_name']
    ];
    public $page_size = 10;
    public $customer;
    public $check_types = [
        1   =>  '小说封面'
    ];

    public $promotion_targets = [
        'inner' =>  '内推',
        'outer' =>  '外推',
        'qrcode' =>  '二维码',
    ];
    public $target_type_map = [
        'inner' =>  [2, '#FF5722'],
        'outer' =>  [1, '#1E9FFF'],
        'qrcode' =>  [3, '#605ca8'],
    ];

    public $latest_check_results = [];
    public $latest_check_description = '';

    public function customer() {
        if(!empty($this->customer)) {
            return $this->customer;
        } else {
            return $this->customer = Admin::user();
        }
    }

    public function __construct()
    {
        foreach($this->check_types as $type => $description) {
            $latest_check_result = NovelCheckLog::where('target_type', '=', $type)->orderBy('id', 'desc')->first();
            $this->latest_check_results[$type] = $latest_check_result;
            if($latest_check_result) {
                $this->latest_check_description .= "[[检测类型-$description; 总量: {$latest_check_result['total_count']}; 有效总量: {$latest_check_result['valid_count']}; 无效总量: {$latest_check_result['invalid_count']}; 修复/处理总量: {$latest_check_result['fixed_count']}; 最近处理目标ID: {$latest_check_result['last_target_id']}; 起始时间：{$latest_check_result['started_time']} 至 {$latest_check_result['finished_time']} ;]]";
                $this->latest_check_description .= '<br/>';
            }
        }
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Novel);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('小说ID'))->sortable();
        $grid->column('title', __('标题'))->limit(36)->sortable();
        $grid->column('img', __('封面'))->image('', 200, 200);
        $grid->column('author', __('作者'))->display(function () {
            return '【'.$this->author['id'].'】'.$this->author['name'];
        })->sortable();
        $grid->column('serial_status', __('完成状态'))->display(function ($serial_status) {
            switch ($serial_status) {
                case 1:
                    $style = 'color:#f15c5c;';
                    $title = '连载中';
                    break;
                case 2:
                    $style = 'color:#ca193d;';
                    $title = '已完结';
                    break;
                case 3:
                    $style = 'color:#45ca19;';
                    $title = '限免';
                    break;
                default:
                    $style = 'color:#07e8ff;';
                    $title = '其他';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        })->sortable();
        $grid->column('word_count', __('当前字数'))->sortable();
        $grid->column('suitable_sex', __('适用人群'))->display(function ($suitable_sex) {
            switch ($suitable_sex) {
                case 1:
                    $style = 'color:blue;';
                    $title = '男';
                    break;
                case 2:
                    $style = 'color:#ff7888;';
                    $title = '女';
                    break;
                default:
                    $style = 'color:#0aff07;';
                    $title = '其他';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        })->sortable();
        /*$grid->column('types', __('类型'))->display(function ($types) {
            $temp = '';
            foreach ($types as $type) {
                $temp .= $type['name'].'|';
            }
            return trim($temp, '|');
        });*/
        $grid->column('type_ids', __('类型'))->select(Type::where([['pid', '>', 0], ['status', 1]])->select(['id', 'name'])->get()->pluck('name', 'id'))->sortable();
        $grid->column('tags', __('标签'))->limit(36);
        $grid->column('desc', __('简介'))->limit(36);
        $grid->column('sections', __('章节数量'))->sortable();
        $grid->column('subscribe_section', __('关注章节'))->editable()->sortable();
        $grid->column('need_buy_section', __('收费章节'))->editable()->sortable();
        //$grid->column('status', __('启用状态'))->switch(Novel::switchStatus(0, ['停用', '正常']))->sortable();
        $grid->column('status', __('启用状态'))->editable('select', Novel::selectList(0, ['停用', '正常', '禁止搜索']))->sortable();
        $grid->column('read_num', __('阅读人数'))->sortable();
        $grid->column('week_read_num', __('本周阅读人数'))->sortable();
        $grid->column('updated_at', __('更新时间'))->sortable();
        $grid->column('created_at', __('创建时间'))->sortable();

        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($uri) {
            $actions->disableDelete();
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            //$actions->append('<a href="javascript:void(0);" data-id="' .$actions->getKey(). '" class="grid-row-delete grid-row-delete2 btn btn-xs btn-danger" title="删除"><i class="fa fa-trash"></i> 删除</a>');
            $actions->append('<a href="'. $uri.'/'.$actions->getKey() .'/edit" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
            // $actions->append('<br><a target="_blank" href="/'. config('admin.route.prefix').'/extend/create?novel_id='.$actions->getKey() .'&type=" class="grid-row-edit btn btn-xs btn-info" title="生成推广链接"><i class="fa fa-edit"></i> 生成推广链接</a>');
            // $actions->append('<br><a target="_blank" href="/'. config('admin.route.prefix').'/extend-links/create?novel_id='.$actions->getKey() .'" class="grid-row-edit btn btn-xs btn-success" title="生成推广文案"><i class="fa fa-edit"></i> 生成推广文案</a>');

            $actions->append('<br><label data-url="/'.config('admin.route.prefix').'/novel/clear_cache" data-status="1" data-id="' . $actions->getKey() . '" title="清除小说缓存" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-refresh"></i> 清除小说缓存</label>');
        });

        $grid->filter(function (Grid\Filter $filter) {
            $filter->like('title', '标题');
            $filter->equal('serial_status', '完成状态')->select([
                1   =>  '连载中',
                2   =>  '已完结',
                3   =>  '限免'
            ]);
            $filter->equal('suitable_sex', '适用人群')->select([
                1   =>  '男',
                2   =>  '女'
            ]);
            $filter->equal('type_ids', '类型')->select(function () {
                return optional(Type::orderBy('id', 'asc')->get())->pluck('name', 'id');
            });
            $filter->equal('author_id', '作者')->select(function () {
                return optional(Author::orderBy('id', 'asc')->limit(10)->get())->pluck('name', 'id');
            })->ajax('/administrator/api/authors');
            $filter->like('tags', '标签');
            $filter->equal('status', '启用状态')->select([0 => '停用', 1 => '正常', 2 => '禁止搜索']);
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new NovelCheckAction());
        });
        $grid->disableExport();
        //$grid->disableRowSelector();

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
        $show = new Show(Novel::findOrFail($id));

        $show->field('id', __('小说ID'));
        $show->field('title', __('标题'));
        $show->field('img', __('封面'))->image('', 200, 200);
        $show->field('author', __('作者'))->as(function () {
            return '【'.$this->author['id'].'】'.$this->author['name'];
        });
        $show->field('serial_status', __('完成状态'))->as(function ($serial_status) {
            switch ($serial_status) {
                case 1:
                    $style = 'color:#f15c5c;';
                    $title = '连载中';
                    break;
                case 2:
                    $style = 'color:#ca193d;';
                    $title = '已完结';
                    break;
                case 3:
                    $style = 'color:#45ca19;';
                    $title = '限免';
                    break;
                default:
                    $style = 'color:#07e8ff;';
                    $title = '其他';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        });
        $show->field('word_count', __('当前字数'));
        $show->field('suitable_sex', __('适用人群'))->as(function ($suitable_sex) {
            switch ($suitable_sex) {
                case 1:
                    $style = 'color:blue;';
                    $title = '男';
                    break;
                case 2:
                    $style = 'color:#ff7888;';
                    $title = '女';
                    break;
                default:
                    $style = 'color:#0aff07;';
                    $title = '其他';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        });
        $show->field('types', __('类型'))->as(function ($types) {
            $temp = '';
            foreach ($types as $type) {
                $temp .= $type['name'].'|';
            }
            return trim($temp, '|');
        });
        $show->field('tags', __('标签'));
        $show->field('desc', __('简介'));
        $show->field('sections', __('章节数量'));
        $show->field('status', __('启用状态'))->as(function ($status) {
            $sts = ['停用', '正常', '禁止搜索'];
            return isset($sts[$status]) ? $sts[$status] : '停用';
        });
        $show->field('read_num', __('阅读人数'));
        $show->field('week_read_num', __('本周阅读人数'));
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
        $form = new Form(new Novel);
        $form->text('title', __('标题'))->rules([
            // 'required', 'regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u',
            'between:2,64',
            Rule::unique('novels')->where(function ($query) use ($form) {
                $query->where('id', '<>', $form->model()->id);
            })
        ]/*, ['regex' => '标题只能由汉字、字母、数字和下划线组成']*/);
        $form->url('img_url', __('封面'));
        $form->image('img', __('封面'))->dir('novel/poster/'.date('Ym'))->uniqueName();
        if($form->isCreating()) {
            $form->hidden('author_id', __('作者ID'))->default(0);
            //$form->text('author_name', __('作者'))->required();
            $form->select('author_name', __('作者'))->options(Author::select(['id', 'name'])->get()->pluck('name', 'name'))->required();
        } else {
            $form->hidden('author_id', __('作者ID'))->default(0);
            $form->select('author_name', __('作者'))->options(Author::select(['id', 'name'])->get()->pluck('name', 'name'))->required();
        }
        $form->select('serial_status', __('完成状态'))->options([
            1   =>  '连载中',
            2   =>  '已完结',
            3   =>  '限免'
        ])->default(1);
        $form->decimal('word_count', __('当前字数'))->required();
        $form->select('suitable_sex', __('适用人群'))->options(Novel::selectList(0, [1=>'男', 2=>'女']));
        /*if($form->isCreating()) {
            $form->multipleSelect('types', __('类型'))->ajax('/administrator/api/types')->required();
        } else {
            $form->multipleSelect('types', __('类型'))->options(function ($ids) {
                return optional(Type::whereIn('id', $ids)->orderBy('id', 'asc')->get())->pluck('name', 'id');
            })->ajax('/administrator/api/types')->required();
        }*/
        $form->select('type_ids', __('类型'))->options(Type::where([['pid', '>', 0], ['status', 1]])->select(['id', 'name'])->get()->pluck('name', 'id'));
        $form->text('tags', __('标签'))->help('多个标签请使用"|"隔开');
        $form->text('desc', __('简介'));
        $form->number('sections', __('章节数量'))->default(1)->required();
        //$form->switch('status', __('启用状态'))->states(Novel::switchStatus(0, ['停用', '正常']))->default(1);
        $form->select('status', __('启用状态'))->options(Novel::selectList(0, ['停用', '正常', '禁止搜索']))->default(0);
        $form->number('read_num', __('阅读人数'));
        $form->number('need_buy_section', __('开始购买的章节序号'));
        $form->number('subscribe_section', __('强制关注的章节序号'));
        $form->number('hot_num', __('热度'))->help('越大越火');
        //$form->datetimeRange('free_start_at', 'free_end_at',  __('限时免费时间'));
        $form->saved(function (Form $form) {
            $novel_id = $form->model()->id;
            $novel = Novel::find($novel_id);
            $author = Author::where('name', $novel['author_name'])->where('status', 1)->first();
            if ($novel['author_id'] != $author['id']) {
                $novel->author_id = $author['id'];
                $novel->save();
            }
        });
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
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
        $form->disableEditingCheck();

        $form->disableCreatingCheck();

        $form->disableViewCheck();

        return $form;
    }


    public function novelList(Content $content) {
        $params = request()->input();
        $params['p'] = $page = max(1, $params['p'] ?? 1);

        $query = '';
        $_status = ['>', 0];
        if (isset($params['status'])) {
            $_status = ['=', $params['status']];
            $query.= 'status='. $params['status'] . '&';
        }

        $condition = [];
        if(!isset($params['search_type']) || !isset($this->search_types[$params['search_type']]) || !isset($params['search_value']) || empty($params['search_value'])) {
            if (isset($params['suitable_sex']) && isset($this->suitable_sex[$params['suitable_sex']])) {
                $query .= 'suitable_sex=' . $params['suitable_sex'] . '&';
                $condition[] = ['suitable_sex', '=', $params['suitable_sex']];
            }
            if (isset($params['type_id']) && isset($this->allTypes()[$params['type_id']])) {
                $query .= 'type_id=' . $params['type_id'] . '&';
            }
            if (isset($params['serial_status']) && isset($this->serial_status[$params['serial_status']])) {
                $query .= 'serial_status=' . $params['serial_status'] . '&';
                $condition[] = ['serial_status', '=', $params['serial_status']];
            }
            if (isset($params['word_count_filter']) && isset($this->word_count_filter[$params['word_count_filter']])) {
                $query .= 'word_count_filter=' . $params['word_count_filter'] . '&';
                $condition[] = ['word_count', '>', $this->word_count_filter[$params['word_count_filter']][1]];
                $condition[] = ['word_count', '<=', $this->word_count_filter[$params['word_count_filter']][2]];
            }
        } else {
            if(isset($params['suitable_sex'])) {
                unset($params['suitable_sex']);
            }
            if(isset($params['type_id'])) {
                unset($params['type_id']);
            }
            if(isset($params['serial_status'])) {
                unset($params['serial_status']);
            }
            if(isset($params['word_count_filter'])) {
                unset($params['word_count_filter']);
            }
            $query .= 'search_type=' . $params['search_type'] . '&' . 'search_value=' . $params['search_value'];
            $condition[] = [$this->search_types[$params['search_type']][1], 'like', '%'.$params['search_value'].'%'];
        }
        $query = trim($query, '&');
        $offset = $this->page_size * ($page - 1);
        if($condition) {
            if((!isset($params['search_type']) || !isset($this->search_types[$params['search_type']]) || !isset($params['search_value']) || empty($params['search_value'])) && isset($params['type_id']) && isset($this->allTypes()[$params['type_id']])) {
                $_novel_list = Novel::where('status', $_status[0], $_status[1])->whereHas('types', function ($query) use ($params) {
                    $query->where('id', '=', $params['type_id']);
                })->where($condition)->limit($this->page_size)->offset($offset)->orderBy('id', 'desc')->get();
                $total = Novel::where('status', $_status[0], $_status[1])->whereHas('types', function ($query) use ($params) {
                    $query->where('id', '=', $params['type_id']);
                })->where($condition)->count();
            } else {
                $_novel_list = Novel::where('status', $_status[0], $_status[1])->where($condition)->limit($this->page_size)->offset($offset)->orderBy('id', 'desc')->get();
                $total = Novel::where('status', $_status[0], $_status[1])->where($condition)->count();
            }
        } else {
            if((!isset($params['search_type']) || !isset($this->search_types[$params['search_type']]) || !isset($params['search_value']) || empty($params['search_value'])) && isset($params['type_id']) && isset($this->allTypes()[$params['type_id']])) {
                $_novel_list = Novel::where('status', $_status[0], $_status[1])->whereHas('types', function ($query) use ($params) {
                    $query->where('id', '=', $params['type_id']);
                })->limit($this->page_size)->offset($offset)->orderBy('id', 'desc')->get();
                $total = Novel::where('status', $_status[0], $_status[1])->whereHas('types', function ($query) use ($params) {
                    $query->where('id', '=', $params['type_id']);
                })->count();
            } else {
                $_novel_list = Novel::where('status', $_status[0], $_status[1])->limit($this->page_size)->offset($offset)->orderBy('id', 'desc')->get();
                $total = Novel::where('status', $_status[0], $_status[1])->count();
            }
        }

        $_novel_list = $_novel_list ? $_novel_list->toArray() : [];
        $page_list_config = getPageList($page, $total, $query, 10);
        // 补全数据
        $novel_list = [];
        $customer = $this->customer();
        foreach ($_novel_list as $novel) {
            // 获取最终章节
            $latest_chapter = NovelSection::where([
                    ['novel_id', '=', $novel['id']]
                ])
                ->orderBy('num', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            if($latest_chapter) {
                $novel['latest_chapter_desc'] = '第'.$latest_chapter->num.'章&nbsp;&nbsp;'.$latest_chapter->title;
            } else {
                $novel['latest_chapter_desc'] = '暂未添加任何章节';
            }
            // 统计我的推广次数
            $promotion_count = ExtendLink::where([
                    ['customer_id', '=', $customer->id],
                    ['novel_id', '=', $novel['id']]
                ])->count();
            $novel['promotion_count'] = $promotion_count;
            $novel_list[] = $novel;
        }
        $base0_url = route('novelList').'?p=1';
        if ($_status[0] == '=') {
            $base0_url .= '&status=' . $_status[1];
        }
        return $content->view('admin.novel.list', [
            'base0_url'         =>  $base0_url,
            'novel_list'        =>  $novel_list,
            'page_list_config'  =>  $page_list_config,
            'suitable_sex'      =>  $this->suitable_sex,
            'types'             =>  $this->allTypes(),
            'serial_status'     =>  $this->serial_status,
            'word_count_filter' =>  $this->word_count_filter,
            'search_types'      =>  $this->search_types,
            'params'            =>  $params,
            'description'       =>  $this->latest_check_description,
            'check_types'       =>  $this->check_types
        ]);
    }

    public function promotionList(Content $content, $novel_id, $target) {
        $params = request()->input();
        // 小说主体
        $novel = $novel_id ? Novel::find($novel_id) : [];
        if(!$novel) {
            return redirect()->route('novelList');
        }
        $novel = $novel->toArray();
        $current_timestamp = time();
        $novel['free'] = $current_timestamp >= $novel['free_start_at'] && $current_timestamp < $novel['free_end_at'] ? true : false;
        $target = isset($this->promotion_targets[$target]) ? $target : false;
        if(!$target) {
            return redirect()->route('novelList');
        }
        // 章节（仅限前20章）
        $chapters = NovelSection::where([
                ['novel_id', '=', $novel_id],
                ['num', '<=', 20]
            ])
            ->orderBy('num', 'asc')
            ->orderBy('id', 'desc')
            ->get();
        $chapters = $chapters ? $chapters->toArray() : [];

        return $content->view('admin.novel.promotion', [
            'novel'             =>  $novel,
            'chapters'          =>  $chapters,
            'params'            =>  $params,
            'promotion_targets' =>  $this->promotion_targets,
            'target'            =>  $target,
            'target_type_map'   =>  $this->target_type_map
        ]);
    }

    /**
     * 创建推广链接
     * @param int $novel_id 小说ID
     * @param int $chapter_no 章节序号
     * @param string $ctitle 渠道名称
     * @param int $cost 渠道成本（元）
     * @param int $subscribe_on 强制关注开关
     * @param int $subscribe_section 需要强制关注的章节
     * @return NovelController|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function createPromotionLink() {
        // 生成推广链接
        $params = request()->input();
        if(isset($params['chapter_id']) && $params['chapter_id']) {
            $chapter = NovelSection::find($params['chapter_id']);
            if (!$chapter) {
                return $this->result(null, -20001, '找不到指定章节');
            }
            $chapter = $chapter->toArray();
            $novel_id = $chapter['novel_id'];
            $chapter_num = $chapter['num'];
            $novel = Novel::find($novel_id);
            if (!$novel) {
                return $this->result(null, -20001, '找不到指定小说');
            }
            $novel = $novel->toArray();
        } else {
            // 小说主体
            $novel_id = isset($params['novel_id']) && $params['novel_id'] ? $params['novel_id'] : 0;
            $novel = Novel::find($novel_id);
            if (!$novel) {
                return $this->result(null, -20001, '找不到指定小说');
            }
            $novel = $novel->toArray();
            // 章节
            $chapter_num = $params['chapter_no'] ?? 0;
            $chapter = NovelSection::where([
                ['novel_id', '=', $novel_id],
                ['num', '=', $chapter_num]
            ])
                ->orderBy('num', 'asc')
                ->orderBy('id', 'desc')
                ->first();
            if (!$chapter) {
                return $this->result(null, -20001, '找不到指定章节');
            }
            $chapter = $chapter->toArray();
        }
        // 数据校验
        $channel_title = $params['ctitle'] ?? '';
        $channel_cost = $params['cost'] ?? 0;
        $channel_title_length = mb_strlen($channel_title);
        if($channel_title_length < 2 || $channel_title_length > 20 || $channel_cost < 0 || $channel_cost > 9999999) {
            return $this->result(null, -20001, '请正确填写渠道名（2-20字）及成本（0-9999999元）');
        }
        $type = $params['type'] ?? -1;
        if(!in_array($type, [1, 2])) {
            return $this->result(null, -20001, '参数错误');
        }
        // 外推链接额外参数
        if($type == 1) {
            $subscribe_on = 1;//intval($params['subscribe_on'] ?? 0);
            $subscribe_section = $params['subscribe_section'] ?? 0;
            if(!in_array($subscribe_on, [0, 1])) {
                return $this->result(null, -20001, '参数错误');
            }
            $subscribe_section = $subscribe_section ? $subscribe_section : (int)$novel['subscribe_section'];// 未设置则设为默认关注章节
            if($subscribe_on && $subscribe_section <= $chapter['num']) {
                return $this->result(null, -20001, '强制关注章节序号不能小于当前章节');
            }
        }
        // 检查公众号是否设置
        $current_wehchat = Wechat::where([
            ['customer_id', '=', $this->customer()->id],
            ['status', '=', 1]
        ])->orderBy('id', 'desc')->first();
        if(!$current_wehchat || $current_wehchat['appid'] == '') {
            return $this->result(['redirect' => '/'.config('admin.route.prefix').'/wechats'], 301, '公众号失效或未配置，请前往设置');
        }
        // 生成链接 + 更新数据
        $promotion = [
            'customer_id'       =>  $this->customer()->id,
            'novel_id'          =>  $novel_id,
            'chapter_id'        =>  $chapter['id'],
            'novel_section_num' =>  $chapter_num,
            'title'             =>  $channel_title,
            'cost'              =>  $channel_cost * 100,
            'link'              =>  '',
            'link_preview'      =>  '',
            'preview_expired_at'=>  '1970-01-01 00:00:00',
            'type'              =>  $type,// 推广链接类型；1外推；2内推（就是必须已经是本账号的用户了）
            'must_subscribe'    =>  $type == 1 ? $subscribe_on : 1,
            'subscribe_section' =>  $type == 1 ? $subscribe_section : 1,
            'status'            =>  1,
            'page_conf'         =>  '',// 推广文案配置
            'data_info'         =>  '' // 该推广链接的数据统计
        ];
        DB::beginTransaction();
        try {
            $promotion = ExtendLink::create($promotion);
            if(!$promotion) {
                throw new \Exception("初始化链接失败");
            }
            $promotion = $promotion->toArray();
            $domainRep = new DomainRepository();
            if ($type == 1) {
                $host = $domainRep->randOne(3, $promotion['customer_id']);
            } else {
                $host = $domainRep->randOne(1, $promotion['customer_id']);
            }
            /* $link = $host . route('novel.extendlink', ['sign' => encrypt(json_encode([
                    'extend_link_id'    =>  $promotion['id'],
                    'cid'               =>  $promotion['customer_id']
                ]))], false);
            */
            $host_preview = $domainRep->randOne(5, $promotion['customer_id']);
            $link = $host . route('novel.extendlink', ['id' => $promotion['id']], false);
            $link_preview = $host_preview ? $host_preview . route('novel.extendlink', ['id' => $promotion['id']], false) : route('novel.extendlink', ['id' => $promotion['id']], true);
            $res = ExtendLink::where('id', '=', $promotion['id'])->update(['link' => $link, 'link_preview' => $link_preview, 'preview_expired_at' => date('Y-m-d H:i:s')]);
            if(!$res) {
                throw new \Exception("更新链接失败");
            }
            DB::commit();
            return $this->result(['link' => $link, 'link_id' => $promotion['id'], 'link_preview' => $link_preview], 0, '创建推广链接成功');
        } catch(\Exception $e) {
            DB::rollBack();
            logger('[Promotion Link] create new link failed, detail: '.$e->getMessage());
            return $this->result(null, -10003, '处理失败，请稍后重试');
        }
    }

    /**
     * 保存推广文案设置
     */
    public function saveDocumentTemplate() {
        $params = request()->input();
        $promotion_id = $params['promotion_id'] ?? 0;
        $promotion = ExtendLink::find($promotion_id);
        if(!$promotion) {
            return $this->result(null, -20001, '该推广链接不存在');
        }
        $promotion = $promotion->toArray();
        $settings = $params['settings'];
        if(!is_array($settings)) {
            return $this->result(null, -20001, '参数有误');
        }
        if(!isset($settings['referral_link_url']) || empty($settings['referral_link_url']) /*|| !CheckUrl($settings['referral_link_url'])*/
            || !isset($settings['title_id']) || $settings['title_id'] < 1
            || !isset($settings['title']) || empty($settings['title'])
            || !isset($settings['cover_id']) || $settings['cover_id'] < 1
            || !isset($settings['cover_url']) || empty($settings['cover_url'])
            || !isset($settings['body_template_id']) || $settings['body_template_id'] < 1
            || !isset($settings['footer_template_id']) || $settings['footer_template_id'] < 1
            || !isset($settings['footer_url']) || empty($settings['footer_url'])
            /*|| !isset($settings['qrcode_template_id']) || $settings['qrcode_template_id'] < 1
            || !isset($settings['qrcode_url']) || empty($settings['qrcode_url'])*/
        ) {
            return $this->result(null, -20001, '参数有误');
        }
        $mode = 'txt';
        $settings['id'] = $promotion['chapter_id'].$mode;
        $settings['mode'] = $mode;
        $res = ExtendLink::where('id', '=', $promotion['id'])->update(['page_conf' => json_encode($settings, JSON_UNESCAPED_UNICODE)]);
        if(!$res) {
            return $this->result(null, -10003, '保存设置失败');
        }
        return $this->result(['id' => $settings['id']], 0, '创建推广链接成功');
    }

    /**
     * 获取预览链接信息
     * @return NovelController|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getPromotionTemp() {
        $params = request()->input();
        $promotion_id = $params['pid'] ?? 0;
        $promotion = ExtendLink::find($promotion_id);
        if(!$promotion) {
            return $this->result(null, -20001, '该推广链接不存在');
        }
        $promotion = $promotion->toArray();
        $settings = json_decode($promotion['page_conf'], true);
        return $this->result(['id' => $promotion['id'], 'url' => $promotion['link_preview'], 'expired_at' => $promotion['preview_expired_at'], 'mode' => $settings['mode'] ?: 'txt'], 0, 'OK');
    }

    /**
     * 重置预览链接：失效时间
     * @return NovelController|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function resetPromotionTemp() {
        $params = request()->input();
        $promotion_id = $params['pid'] ?? 0;
        $promotion = ExtendLink::find($promotion_id);
        if(!$promotion) {
            return $this->result(null, -20001, '该推广链接不存在');
        }
        $promotion = $promotion->toArray();
        $expire_at = date('Y-m-d H:i:s');
        $res = ExtendLink::where('id', '=', $promotion['id'])->update(['preview_expired_at' => $expire_at]);
        if(!$res) {
            return $this->result(null, -10003, '重置预览链接失败');
        }
        return $this->result(['expired_at' => $expire_at], 0, 'OK');
    }

    /**
     * 文案展示页面
     */
    public function documentDisplay($target) {
        $params = request()->input();
        $chapter_id = $params['chapter_id'] ?? 0;
        if(!$chapter_id) {
            abort(404);
        }
        $current_chapter = NovelSection::find($chapter_id);
        if(!$current_chapter) {
            abort(404);
        }
        $current_chapter = $current_chapter->toArray();
        $novel = Novel::find($current_chapter['novel_id']);
        if(!$novel) {
            abort(404);
        }
        $novelSectionRep = new NovelSectionRepository();
        $chapters = $novelSectionRep->ExtendLinkSections($current_chapter['novel_id'], $current_chapter['num']);
        $tpl_data = ['sections' => $chapters, 'is_admin' => true, 'current_chapter' => $current_chapter, 'novel' => $novel, 'target' => $target, 'promotion_targets' => $this->promotion_targets, 'target_type_map' => $this->target_type_map];
        $extendLinkRep = new ExtendLinkRepository();
        $tpl_data = array_merge($tpl_data, $extendLinkRep->ExtendPageInfos());
        return view('front.novel.document', $tpl_data);
    }

    public function chapterInfo() {
        $params = request()->input();
        $chapter = [];
        if(isset($params['novel_id']) && $params['novel_id'] && isset($params['chapter_no']) && $params['chapter_no']) {
            $chapter = NovelSection::where([
                    ['novel_id', '=', $params['novel_id']],
                    ['num', '=', $params['chapter_no']]
                ])
                ->orderBy('num', 'asc')
                ->orderBy('id', 'desc')
                ->first();
            $chapter = $chapter ? $chapter->toArray() : [];
            if($chapter) {
                $chapter['content_str'] = file_get_contents($chapter['content']);
            }
        }
        return $chapter;
    }

    public function allTypes() {
        if(empty($this->types)) {
            $types = Type::where([['status', '=', 1]])->orderBy('sort', 'desc')->get();
            $types = $types ? $types->toArray() : [];
            foreach($types as $type) {
                $this->types[$type['id']] = $type;
            }
        }
        return $this->types;
    }



    /**
     * 删除单本小说缓存
     */
    public function ClearCache()
    {
        $novel_id = request()->input('id');
        $novel = Novel::select(['sections', 'id'])->find($novel_id);
        $section_num = 1;
        $msg = '清理完成！';
        $clear_num = 0;
        while ($section_num <= $novel['sections']) {
            $key = config('app.name') . 'novel_'.$novel_id.'_section_'.$section_num.'_info';
            if (Cache::has($key)) {
                Cache::forget($key);
                $clear_num++;
            }
            $section_num++;
        }
        $msg .= '___section = '. $clear_num;
        $clear_num = 0;
        $key = config('app.name') . 'novel_section_first2last_'.$novel_id;
        if (Cache::has($key)) {
            Cache::forget($key);
            $clear_num++;
        }
        $msg .= '___first2last = '. $clear_num;
        $clear_num = 0;
        $key = config('app.name') . 'novel_info_'. $novel_id;
        if (Cache::has($key)) {
            Cache::forget($key);
            $clear_num++;
        }
        $msg .= '___novel_info = '. $clear_num;
        $clear_num = 0;
        //$orders = ['asc', 'desc', 'ASC', 'DESC'];

        $section_num = 1;
        while ($section_num <= 5) {
            $key = config('app.name') . 'novel_section_list_'.$novel_id.'_page_'.$section_num.'_order_asc';
            if (Cache::has($key)) {
                Cache::forget($key);
                $clear_num++;
            }
            $key = config('app.name') . 'novel_section_list_'.$novel_id.'_page_'.$section_num.'_order_ASC';
            if (Cache::has($key)) {
                Cache::forget($key);
                $clear_num++;
            }
            $key = config('app.name') . 'novel_section_list_'.$novel_id.'_page_'.$section_num.'_order_desc';
            if (Cache::has($key)) {
                Cache::forget($key);
                $clear_num++;
            }
            $key = config('app.name') . 'novel_section_list_'.$novel_id.'_page_'.$section_num.'_order_DESC';
            if (Cache::has($key)) {
                Cache::forget($key);
                $clear_num++;
            }
            $section_num++;
        }
        $msg .= '___section_list = '. $clear_num;

        return $this->result(['清理完成！'], 0, $msg);
    }
}
