<link rel="stylesheet" href="/admin/css/novel/main.css">
<link href="/admin/layui/css/layui.css" rel="stylesheet">
{{--<link rel="stylesheet" href="/admin/css/novel/reset.css">--}}
<div class="novel-list-wrapper">
    <input id="current-page" type="hidden" value="1">
    <div class="novel-categories-wrapper">
        <div class="novel-category">
            <p class="novel-category-title">频道:</p>
            <div
                class="novel-category-item @if (!isset($params['suitable_sex']) || !isset($suitable_sex[$params['suitable_sex']])) active @endif"
                onclick="window.location.href='{{ $base0_url }}{{ (isset($params['type_id']) ? '&type_id='.$params['type_id'] : '') }}{{ (isset($params['serial_status']) ? '&serial_status='.$params['serial_status'] : '') }}{{ (isset($params['word_count_filter']) ? '&word_count_filter='.$params['word_count_filter'] : '') }}';">
                全部
            </div>
            @foreach ($suitable_sex as $key => $sex)
                <div
                    class="novel-category-item @if (isset($params['suitable_sex']) && $params['suitable_sex'] == $key) active @endif"
                    onclick="window.location.href='{{ $base0_url }}{{ ('&suitable_sex='.$key) }}{{ (isset($params['type_id']) ? '&type_id='.$params['type_id'] : '') }}{{ (isset($params['serial_status']) ? '&serial_status='.$params['serial_status'] : '') }}{{ (isset($params['word_count_filter']) ? '&word_count_filter='.$params['word_count_filter'] : '') }}';">{{ $sex }}</div>
            @endforeach
        </div>

        <div class="novel-category">
            <p class="novel-category-title">分类:</p>
            <div
                class="novel-category-item @if (!isset($params['type_id']) || !isset($types[$params['type_id']])) active @endif"
                onclick="window.location.href='{{ $base0_url }}{{ (isset($params['suitable_sex']) ? '&suitable_sex='.$params['suitable_sex'] : '') }}{{ (isset($params['serial_status']) ? '&serial_status='.$params['serial_status'] : '') }}{{ (isset($params['word_count_filter']) ? '&word_count_filter='.$params['word_count_filter'] : '') }}';">
                全部
            </div>
            @foreach ($types as $key => $type)
                <div
                    class="novel-category-item @if (isset($params['type_id']) && $params['type_id'] == $key) active @endif"
                    onclick="window.location.href='{{ $base0_url }}{{ (isset($params['suitable_sex']) ? '&suitable_sex='.$params['suitable_sex'] : '') }}{{ ('&type_id='.$key) }}{{ (isset($params['serial_status']) ? '&serial_status='.$params['serial_status'] : '') }}{{ (isset($params['word_count_filter']) ? '&word_count_filter='.$params['word_count_filter'] : '') }}';">{{ $type['name'] }}</div>
            @endforeach
        </div>

        <div class="novel-category">
            <p class="novel-category-title">状态:</p>
            <div
                class="novel-category-item @if (!isset($params['serial_status']) || !isset($serial_status[$params['serial_status']])) active @endif"
                onclick="window.location.href='{{ $base0_url }}{{ (isset($params['suitable_sex']) ? '&suitable_sex='.$params['suitable_sex'] : '') }}{{ (isset($params['type_id']) ? '&type_id='.$params['type_id'] : '') }}{{ (isset($params['word_count_filter']) ? '&word_count_filter='.$params['word_count_filter'] : '') }}';">
                全部
            </div>
            @foreach ($serial_status as $key => $status)
                <div
                    class="novel-category-item @if (isset($params['serial_status']) && $params['serial_status'] == $key) active @endif"
                    onclick="window.location.href='{{ $base0_url }}{{ (isset($params['suitable_sex']) ? '&suitable_sex='.$params['suitable_sex'] : '') }}{{ (isset($params['type_id']) ? '&type_id='.$params['type_id'] : '') }}{{ ('&serial_status='.$key) }}{{ (isset($params['word_count_filter']) ? '&word_count_filter='.$params['word_count_filter'] : '') }}';">{{ $status }}</div>
            @endforeach
        </div>

        <div class="novel-category">
            <p class="novel-category-title">字数:</p>
            <div
                class="novel-category-item @if (!isset($params['word_count_filter']) || !isset($word_count_filter[$params['word_count_filter']])) active @endif"
                onclick="window.location.href='{{ $base0_url }}{{ (isset($params['suitable_sex']) ? '&suitable_sex='.$params['suitable_sex'] : '') }}{{ (isset($params['type_id']) ? '&type_id='.$params['type_id'] : '') }}{{ (isset($params['serial_status']) ? '&serial_status='.$params['serial_status'] : '') }}';">
                全部
            </div>
            @foreach ($word_count_filter as $key => $filter)
                <div
                    class="novel-category-item @if (isset($params['word_count_filter']) && $params['word_count_filter'] == $key) active @endif"
                    onclick="window.location.href='{{ $base0_url }}{{ (isset($params['suitable_sex']) ? '&suitable_sex='.$params['suitable_sex'] : '') }}{{ (isset($params['type_id']) ? '&type_id='.$params['type_id'] : '') }}{{ (isset($params['serial_status']) ? '&serial_status='.$params['serial_status'] : '') }}{{ ('&word_count_filter='.$key) }}';">{{ $filter[0] }}</div>
            @endforeach
        </div>

        <div class="options">
            <div val="{{ $params['search_type'] ?? '' }}" class="novel-select">
                <div class="novel-select-title">请选择</div>
                <img class="arrow-icon" src="http://novelsys.oss-cn-shenzhen.aliyuncs.com/front/icon_jiantou.png"
                     alt="">
                <div class="novel-dropdown-menu hide">
                    @foreach ($search_types as $key => $search_type)
                        <p val="{{ $key }}" class="novel-dropdown-menu-item">{{ $search_type[0] }}</p>
                    @endforeach
                </div>
            </div>
            <input class="novel-search" type="text" placeholder="请输入关键字" value="{{ $params['search_value'] ?? '' }}">
            <button class="btn-search">搜索</button>
            <button class="btn-data-check novel-check-action" title="{{ str_replace('<br/>', '    ', $description) }}" modal="app-admin-actions-novel-novelcheckaction">数据检测</button>
        </div>
    </div>

    <table cellspacing="0" cellpadding="0" border="0" align="center" valign="middle" class="novel-list">
        <thead>
        <td class="novel-list-col-title">书ID</td>
        <td class="novel-list-col-title">封面</td>
        <td class="novel-list-col-title">作品信息</td>
        <td class="novel-list-col-title">充值转化率</td>
        <td class="novel-list-col-title">我的推广次数</td>
        <td class="novel-list-col-title">人气推广次数</td>
        <td class="novel-list-col-title">操作</td>
        </thead>
        <tbody class="novel-list-content">
        @foreach ($novel_list as $novel)
            <tr class="novel-list-content-row">
                <td class="novel-list-content-item">{{ $novel['id'] }}</td>
                <td class="novel-list-content-item">
                    <img style="border-radius:3px;" class="cover" src="{{ $novel['img'] }}">
                </td>
                <td class="novel-list-content-item">
                    <div class="bookinfo">
                        <span title="#" style="font-weight:bold;">{{ $novel['title'] }}</span>
                        <p class="p1">
                            频道-类别：{{ $suitable_sex[$novel['suitable_sex']] }}{{ (isset($novel['types']) && !empty($novel['types']) ? '-'.$novel['types'][0]['name'] : '') }}</p>
                        <p class="p1">更新状态：
                            @if ($novel['serial_status'] == 2)<span
                                class="finished">已完结</span>@elseif ($novel['serial_status'] == 1)<span
                                class="continuing">连载</span>@elseif ($novel['serial_status'] == 3)<span
                                class="continuing">限免</span>@else<span>未知</span>@endif
                        </p>
                        <p class="p1">字数：{{ $novel['word_count'] }} 万字</p>
                        <p class="p1">章节总数：{{ $novel['sections'] }}</p>
                        <p class="p1">最新章节：{{ $novel['latest_chapter_desc'] }}</p>
                    </div>
                </td>
                <td class="novel-list-content-item">1</td>
                <td class="novel-list-content-item">1</td>
                <td class="novel-list-content-item">1</td>
                <td class="novel-list-content-item">
                    <div class="options-wrapper">
                        <button class="tui out" onclick="window.location.href='{{ route('novelPromotionList', ['novel_id' => $novel['id'], 'target' => 'outer']) }}';"><img
                                src="http://novelsys.oss-cn-shenzhen.aliyuncs.com/front/link_icon.png" alt="">获取外推链接
                        </button>
                        <button class="tui in" onclick="window.location.href='{{ route('novelPromotionList', ['novel_id' => $novel['id'], 'target' => 'inner']) }}';"><img
                                src="http://novelsys.oss-cn-shenzhen.aliyuncs.com/front/link_icon.png" alt="">获取内推链接
                        </button>
                        <button class="tui out" style="background: #605ca8;" onclick="window.location.href='{{ route('novelPromotionList', ['novel_id' => $novel['id'], 'target' => 'qrcode']) }}';"><img
                                src="http://novelsys.oss-cn-shenzhen.aliyuncs.com/front/link_icon.png" alt="">获取推广二维码
                        </button>
                        <button class="tui edit" onclick="window.open('/administrator/novels/{{ $novel['id'] }}/edit');"><img src="http://novelsys.oss-cn-shenzhen.aliyuncs.com/front/edit.png" alt="">编辑修改</button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="pagebar pagebar2">
        <ul class="pagination">
            <ul class="pagination">
                @foreach ($page_list_config['page_list'] as $page)
                    @if (!empty($page['href']))
                        <li><a href="{{ $page['href'] }}">{{ $page['text'] }}</a></li>
                    @else
                        <li class="{{ $page['class'] }}"><span>{{ $page['text'] }}</span></li>
                    @endif
                @endforeach
            </ul>
        </ul>
    </div>
</div>
<div class="modal in" tabindex="-1" role="dialog" id="app-admin-actions-novel-novelcheckaction" style="display: none; padding-right: 17px;" aria-hidden="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <form id="app-admin-actions-novel-novelcheckaction-form">
                <div class="modal-body">
                    <div class="form-group">
                        <label>检测类型</label>

                        <select class="form-control type action select2-hidden-accessible" style="width: 100%;" name="type" required="1" data-value="1" tabindex="-1" aria-hidden="true">
                            <option value=""></option>
                            @foreach ($check_types as $key => $check_type)
                            <option value="{{ $key }}" @if ($key == 1)selected="selected"@endif>{{ $check_type }}</option>
                            @endforeach
                        </select>
                        <span class="select2 select2-container select2-container--default" dir="ltr" style="width: 100%;">
                            <span class="selection">
                                <span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-labelledby="select2-type-ne-container">
                                    <span class="select2-selection__rendered" id="select2-type-ne-container" title="{{ $check_types[1] }}">
                                        <span class="select2-selection__clear">×</span>{{ $check_types[1] }}
                                    </span>
                                    <span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span>
                                </span>
                            </span>
                            <span class="dropdown-wrapper" aria-hidden="true"></span>
                        </span>
                        <span class="help-block">
                            <i class="fa fa-info-circle"></i>&nbsp;{!! $description !!}
                        </span>
                    </div>


                    <div class="form-group">
                        <label>是否从上次停下处继续</label>
                        <div>
                            <span class="icheck">
                                <label class="radio-inline">
                                    <div class="iradio_minimal-blue checked" style="position: relative;" aria-checked="false" aria-disabled="false"><input type="radio" name="continue" value="1" class="minimal continue action" checked="" style="position: absolute; opacity: 0;"><ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255) none repeat scroll 0% 0%; border: 0px none; opacity: 0;"></ins></div>&nbsp;是&nbsp;&nbsp;
                                </label>
                            </span>
                            <span class="icheck">
                                <label class="radio-inline">
                                    <div class="iradio_minimal-blue" style="position: relative;" aria-checked="false" aria-disabled="false"><input type="radio" name="continue" value="0" class="minimal continue action" style="position: absolute; opacity: 0;"><ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255) none repeat scroll 0% 0%; border: 0px none; opacity: 0;"></ins></div>&nbsp;否&nbsp;&nbsp;
                                </label>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <button type="submit" class="btn btn-primary">提交</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<script src="/admin/js/promise.js"></script>
<script src="/admin/layui/layui.js"></script>
<script>
    var sel = '{{ $params['search_type'] ?? '' }}';
    var layer;
    layui.use(['laydate', 'element', 'form', 'layer'], function () {
        var form = layui.form;
        var element = layui.element;
        var laydate = layui.laydate;
        layer = layui.layer;
    });

    $(function () {
        // 点击body取消所有下拉框弹出
        $(document).on("click", function () {
            $(".novel-dropdown-menu").each(function (i, val) {
                if (!$(val).hasClass("hide")) {
                    $(val).toggleClass("hide");
                }
            });
        });

        function choseSelected(ths) {
            $(ths).find(".novel-dropdown-menu-item").removeClass("active");
            $(ths).find(".novel-dropdown-menu-item").each(function (i, val) {
                if ($(val).attr("val") === $(ths).attr("val")) {
                    $(ths).find(".novel-select-title").text($(val).text());
                    $(val).toggleClass("active");
                }
            });
        }

        $(".novel-select").each(function (i, val) {
            choseSelected(val);
            $(val).find(".novel-dropdown-menu-item").click(function () {
                $(val).attr("val", $(this).attr("val"));
                choseSelected(val);
                $(val).trigger("change", $(this).attr("val"));
            });
        });

        $(document).on("click", ".novel-select", function (e) {
            e.stopPropagation();
            var item = $(this).find(".novel-dropdown-menu");
            if (item.hasClass("hide")) {
                item.css("display", "block");
                setTimeout(function () {
                    item.toggleClass("hide");
                });
            } else {
                item.toggleClass("hide");
                setTimeout(function () {
                    item.css("display", "none");
                }, 300);
            }

        });

        $(document).on('click', '.btn-search', function (e) {
            var type = sel;
            var searchInput = $(".novel-search").val();

            if (!type) {
                layer.msg('请先选择查询类型');
                return false;
            }
            if (searchInput === "") {
                layer.msg('请输入查询关键字');
                return false;
            }
            var url = '?search_type=' + type + '&search_value=' + searchInput;
            if (getQueryString('status')!=='') url += '&status=' + getQueryString('status');
            window.location.href = url;
        });

        $('.novel-check-action').off('click').on('click', function() {
            var data = $(this).data();
            var target = $(this);
            var modalId = $(this).attr('modal');
            console.log('modalId: ' + modalId);
            console.log(data);
            Object.assign(data, []);

            $('#'+modalId).modal('show');
            $('#'+modalId+'-form').off('submit').on('submit', function (e) {
                e.preventDefault();
                var form = this;
                var process = new Promise(function (resolve,reject) {
                    Object.assign(data, {
                        _token: $.admin.token,
                        _action: 'App_Admin_Actions_Novel_NovelCheckAction',
                    });

                    var formData = new FormData(form);
                    for (var key in data) {
                        formData.append(key, data[key]);
                    }

                    $.ajax({
                        method: 'POST',
                        url: '/administrator/_handle_action_',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            resolve([data, target]);
                            if (data.status === true) {
                                $('#'+modalId).modal('hide');
                            }
                        },
                        error:function(request){
                            reject(request);
                        }
                    });
                });
                process.then(actionResolver).catch(actionCatcher);
            });
        });
    });

    $(function () {
        $(".novel-select").on("change", function (e, val) {
            sel = val;
        });
    });
    var actionResolver = function (data) {

        var response = data[0];
        var target   = data[1];

        if (typeof response !== 'object') {
            return $.admin.swal({type: 'error', title: 'Oops!'});
        }

        var then = function (then) {
            if (then.action == 'refresh') {
                $.admin.reload();
            }

            if (then.action == 'download') {
                window.open(then.value, '_blank');
            }

            if (then.action == 'redirect') {
                $.admin.redirect(then.value);
            }

            if (then.action == 'location') {
                window.location = then.value;
            }
        };

        if (typeof response.html === 'string') {
            target.html(response.html);
        }

        if (typeof response.swal === 'object') {
            $.admin.swal(response.swal);
        }

        if (typeof response.toastr === 'object' && response.toastr.type) {
            $.admin.toastr[response.toastr.type](response.toastr.content, '', response.toastr.options);
        }

        if (response.then) {
            then(response.then);
        }
    };
    var actionCatcher = function (request) {
        if (request && typeof request.responseJSON === 'object') {
            $.admin.toastr.error(request.responseJSON.message, '', {positionClass:"toast-bottom-center", timeOut: 10000}).css("width","500px")
        }
    };


    // 获取参数值 name 参数名称；val 没有该参数则添加默认值
    function getQueryString(name, val = '') {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        // var r = window.location.search.substr(1).match(reg);
        var r = window.top.location.search.substr(1).match(reg);
        
        if (r != null)
            return unescape(r[2]);
        return val;
    }
</script>
