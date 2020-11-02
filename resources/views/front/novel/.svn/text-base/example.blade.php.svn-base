

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>爱情最后的依靠 - 参考文案</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="//bfstatic.roushongkanshu.com/static/wenan/vendor/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="//bfstatic.roushongkanshu.com/static/wenan/vendor/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="//bfstatic.roushongkanshu.com/static/wenan/vendor/toastr.js/2.1.3/toastr.min.css" rel="stylesheet">
    <script src="//bfstatic.roushongkanshu.com/static/wenan/vendor/jquery/1.12.4/jquery.js"></script>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/vendor/lodash.js/4.17.4/lodash.min.js"></script>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/vendor/toastr.js/2.1.3/toastr.min.js"></script>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/vendor/handlebars.js/4.0.10/handlebars.min.js"></script>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/vendor/knockout/3.4.1/knockout-min.js"></script>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/vendor/jquery-validate/1.16.0/jquery.validate.min.js"></script>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/vendor/jquery-validation-unobtrusive/3.2.6/jquery.validate.unobtrusive.min.js"></script>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/vendor/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/vendor/clipboard.js/1.6.1/clipboard.min.js"></script>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/static/vendor/rasterizeHTML.allinone.js"></script>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/static/vendor/qrcode.js"></script>
    <script>
        toastr.options.positionClass = 'toast-bottom-right';
    </script>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/static/js/admin.js?v=39"></script>

    <link rel="stylesheet" href="//bfstatic.roushongkanshu.com/static/wenan/static/v1/css/page_mp_article.css" />
    <link rel="stylesheet" href="//bfstatic.roushongkanshu.com/static/wenan/static/v1/css/page_mp_article_improve_combo.css" />
    <link rel="stylesheet" href="//bfstatic.roushongkanshu.com/static/wenan/static/v1/css/admin.css?v=5"/>

    <!--[if lte IE 8]>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/static/v1/assets/js/html5shiv.min.js"></script>
    <script src="//bfstatic.roushongkanshu.com/static/wenan/static/v1/assets/js/respond.min.js"></script>
    <![endif]-->

    <style type="text/css">
        #editor-bar .dropdown-menu {
            height: auto;
            max-height: 400px;
            overflow-x: hidden;
        }

        .searchbar{ padding:3px 20px;}
        .searchbar input{ display:block; width:200px; padding:0 5px; border-radius:4px; border:1px solid #ccc;}
    </style>
</head>
<body>
<div class="rich_media">
    <div class="rich_media_inner" style="padding-top:0">
        <div class="rich_media_area_primary">
            <h1 id="wx-article-title" class="rich_media_title"></h1>
            <div class="rich_media_content">
                <div id="wx-article-content">
                    <div id="wx-article-cover"></div>
                    <div id="wx-article-body"></div>
                    <div id="wx-article-qrcode" style="text-align:center;"></div>
                    <div id="wx-article-footer"></div>
                    <!-- wx requires at least one character -->
                    <section style="color:white;">.</section>
                </div>
            </div>
        </div>
        <div style="padding-top:20px; display:none;" class="panel-referral-link">
            <div class="input-group">
                <span class="input-group-addon">原文链接</span>
                <input type="text" id="txt-referral-link" readonly style="background:white" class="form-control" onclick="this.select()"/>
                <span class="input-group-btn">
                        <button type="button" data-toggle="copy-link" class="btn btn-default"><i class="fa fa-copy"></i> 复制</button>
                    </span>
            </div>
        </div>
    </div>
</div>

<nav id="editor-bar" class="navbar navbar-default navbar-fixed-bottom">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#editor-menu" aria-expanded="false">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="editor-menu">
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-header"></i> 文案标题 <span class="caret"></span></a>

                    <ul class="dropdown-menu">
                        <div class="searchbar"><input type="text" id="searchtit" placeholder="请输入标题检索" onKeyUp="searchtit($(this));"></div>
                        <!-- ko foreach: titles -->
                        <li><a href="#" data-bind="text: title, click: $root.changeTitle"></a></li>
                        <!-- /ko -->
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-image"></i> 文案封面 <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <!-- ko foreach: covers -->
                        <li>
                            <a href="#" data-bind="click: $root.changeCover">
                                <img style="width:100%" data-bind="attr: { src: cover_url }" />
                            </a>
                        </li>
                        <!-- /ko -->
                    </ul>
                </li>

                <li class="dropdown" style="display:none;">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-bind="html: currentEditorHtml()"></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:void(0);" data-editor="text" data-bind="click: switchEditor"><i class="ace-icon glyphicon glyphicon-text-width"></i> 文本模式</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" data-editor="image" data-bind="click: switchEditor"><i class="ace-icon glyphicon glyphicon-picture"></i> 图片模式</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" data-editor="background" data-bind="click: switchEditor"><i class="ace-icon glyphicon glyphicon-picture"></i> 背景图模式</a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">正文模板 <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <!-- ko foreach: body_templates -->
                        <li style="border-bottom:#eee 1px solid;">
                            <a href="#" data-bind="click: $root.changeBodyTemplate">
                                <img style="max-height: 40px;" data-bind="attr: { src: preview_img }" />
                            </a>
                        </li>
                        <!-- /ko -->
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">原文引导模板 <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <!-- ko foreach: footer_templates -->
                        <li style="border-bottom:#eee 1px solid;">
                            <a href="#" data-bind="click: $root.changeFooterTemplate">
                                <img style="max-height: 40px;" data-bind="attr: { src: preview_img }" />
                            </a>
                        </li>
                        <!-- /ko -->
                        <li style="border-bottom:#eee 1px solid;">
                            <a href="#" style="color:#777;" data-bind="click: $root.changeFooterTemplate">
                                ----------- 无 -----------
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">二维码引导模板 <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <!-- ko foreach: qrcode_templates -->
                        <li style="border-bottom:#eee 1px solid;">
                            <a href="#" data-bind="click: $root.changeQRCodeTemplate">
                                <img style="max-height: 40px;" data-bind="attr: { src: preview_img }" />
                            </a>
                        </li>
                        <!-- /ko -->
                        <li style="border-bottom:#eee 1px solid;">
                            <a href="#" style="color:#777;" data-bind="click: $root.changeQRCodeTemplate">
                                ----------- 无 -----------
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-copy"></i> 复制 <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:void(0);" data-toggle="copy-text" data-clipboard-target="#wx-article-title">复制标题</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" data-toggle="copy-text" data-clipboard-target="#wx-article-content">复制正文</a>
                        </li>
                    </ul>
                </li>

            </ul>
            <div class="navbar-form navbar-right">
                <span data-bind="visible: is_novel_online() || is_testing()" style="display:none;">
                    <button type="button" class="btn btn-primary" data-bind="click: openReferralLinkModal, visible: !referral_link_id()">
                        <i class="fa fa-link"></i> 生成外推链接
                    </button>
                    <span class="btn-group dropup" data-bind="visible: referral_link_id()">
                        <button type="button" class="btn btn-primary" data-toggle="get-link"><i class="fa fa-link"></i> 生成外推链接</button>
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" data-bind="click: editReferralLink"><i class="fa fa-fw fa-edit"></i> 修改链接属性</a></li>
                            <li><a href="#" data-bind="click: openReferralLinkModal"><i class="fa fa-fw fa-plus"></i> 生成新链接</a></li>
                        </ul>
                    </span>
                </span>
                <span data-bind="visible: !is_novel_online() && !is_testing()" style="display:none;">
                    <button type="button" class="btn btn-disabled">小说已下架</button>
                </span>
            </div>
            <div class="navbar-form navbar-right">
                <span>
                    <button type="button" class="btn btn-primary" data-bind="click: getTmpReferralLink" data-toggle="tooltip" title="获取文案预览链接给接单人员复制文案">
                        <i class="fa fa-link"></i> 文案预览链接
                    </button>
                </span>
            </div>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<div class="modal fade" id="tmp-referral-link-modal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-bind="click: close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">文案公开链接</h4>
            </div>
            <div class="modal-body">
                <div data-bind="visible: loading" class="loading-panel">
                    <i class="fa fa-spin fa-spinner"></i>
                </div>
                <div style="display: none" data-bind="visible: !loading()">
                    <div data-bind="visible: !edit_mode() && !tmp_referral_link_url()">
                        该推广链接还没有添加文案
                    </div>
                    <div data-bind="visible: tmp_referral_link_url()">
                        <div class="input-group form-group">
                            <span class="input-group-addon">链接</span>
                            <input type="text" id="tmp-referral-link-url" data-bind="value: tmp_referral_link_url()" readonly class="form-control" onclick="this.select()"/>
                            <span class="input-group-btn">
                                <button type="button" data-toggle="copy-tmp-referral-link" data-clipboard-target="#tmp-referral-link-url" class="btn btn-default"><i class="fa fa-copy"></i> 复制</button>
                            </span>
                        </div>
                        <div>
                            有效期至: <span style="color:darkred;" data-bind="text: expired_at()"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-editor-mode="text" data-bind="visible: !edit_mode() && !tmp_referral_link_url(), click: open_editor">编辑文字文案</button>
                <button type="button" class="btn btn-primary" data-editor-mode="image" data-bind="visible: !edit_mode() && !tmp_referral_link_url(), click: open_editor">编辑图片文案</button>
                <!--<button type="button" class="btn btn-primary" data-editor-mode="background" data-bind=" click: open_editor">编辑背景图文案</button>-->
                <button type="button" class="btn btn-primary" data-bind="visible: !edit_mode() && tmp_referral_link_url(), click: open_editor">编辑文案</button>
                <button type="button" class="btn btn-primary" data-bind="visible: tmp_referral_link_url(), click: renew">重置过期时间</button>
            </div>
        </div>
    </div>
</div>

<script>
    var GetTmpReferralLinkModal = function () {
        var self = this;
        var $modal = null;

        var model = {
            loading: ko.observable(false),
            submitting: ko.observable(false),
            referral_link_id: ko.observable(),
            tmp_referral_link_id: ko.observable(),
            tmp_referral_link_url: ko.observable(),
            expired_at: ko.observable(''),
            edit_mode: ko.observable(false),
            editor: ko.observable(''),

            open_editor: function (item, event) {
                var editor = $(event.target).data('editor-mode');
                editor = editor || model.editor();
                var url = '/backend/wx_article_editor?referral_link_id=' + model.referral_link_id();

                window.open(url + '&mode=' + editor);
            },
            close: function () {
                self.close();
            },
            renew: function () {
                Modal.confirm({
                    title: '重置过期时间',
                    message: '重置后有效期将延长到当前时间算起的7天内, 确定重置吗?'
                })
                    .then(function () {
                        $.ajax({
                            url: '/index/wenan/api_renew?sid=' + model.tmp_referral_link_id(),
                            type: 'POST',
                            contentType: 'application/json'
                        })
                            .then(function (data) {
                                model.expired_at(data.expired_at);

                                toastr.success('过期时间重置成功');
                            })
                            .fail(handleAjaxError);
                    });
            },
            copy_link: function(item, event) {
                var clipboard = new Clipboard(event.target, {
                    text: function () {
                        return $('#txt-referral-link').val();
                    }
                });
                clipboard.on('success', function (e) {
                    e.clearSelection();
                    toastr.success('链接复制成功');
                });
            }
        };

        self.open = function (options) {
            self.reset();

            options = options || {};

            if (!$modal) {
                $modal = $('#tmp-referral-link-modal');
                ko.applyBindings(model, $modal.find('.modal-content')[0]);
            }

            model.loading(true);

            model.edit_mode(options.edit_mode);
            model.referral_link_id(options.referral_link_id);

            $.get('/index/wenan/get_link_tmp?sid=' + options.referral_link_id, function (data) {
                model.tmp_referral_link_id(data.id);
                model.tmp_referral_link_url(data.url);
                model.expired_at(data.expired_at);
                model.editor(data.mode);
            });

            model.loading(false);

            $modal.modal('show');
        };

        self.close = function () {
            $modal.modal('hide');
        };

        self.reset = function () {
            model.loading(false);
            model.submitting(false);

            model.referral_link_id(null);
            model.tmp_referral_link_id(null);
            model.tmp_referral_link_url(null);
            model.expired_at(null);
            model.editor(null);
        }
    };

    GetTmpReferralLinkModal.instance = new GetTmpReferralLinkModal();

    $(function() {
        $('[data-toggle="copy-tmp-referral-link"]').each(function () {
            var clipboard = new Clipboard(this);
            clipboard.on('success', function (e) {
                e.clearSelection();
                toastr.success('链接复制成功');
            });
        });
    })
</script>

<script>
    var EditorBar = function (options) {
        var self = this;
        var editor = options.editor;
        var model = {
            article_id: 70491,
            is_novel_online: ko.observable(options.is_novel_online),
            is_testing: ko.observable(options.is_testing),
            referral_link_id: ko.observable(options.referral_link_id),
            titles: ko.observableArray(options.titles),
            covers: ko.observableArray(options.covers),
            body_templates: ko.observableArray(options.body_templates),
            footer_templates: ko.observableArray(options.footer_templates),
            qrcode_templates: ko.observableArray(options.qrcode_templates),

            changeTitle: function (title) {
                scrollToElement('#wx-article-title', { offset: 10 }, function () {
                    editor.changeTitle(title.id);
                });
            },

            changeCover: function (cover) {
                scrollToElement('#wx-article-cover', { offset: 10 }, function () {
                    editor.changeCover(cover.id);
                });
            },

            changeBodyTemplate: function (template) {
                scrollToElement('#wx-article-body', { offset: 10 }, function () {
                    editor.changeBodyTemplate(template.id);
                });
            },

            changeFooterTemplate: function (template) {
                scrollToElement('#wx-article-footer', { offset: 10 }, function () {
                    editor.changeFooterTemplate(template.id);
                });
            },

            changeQRCodeTemplate: function (template) {
                if (template && template.id && !model.referral_link_id()) {
                    Modal.open({
                        'title': '提示',
                        'body': '插入二维码需要先生成推广链接',
                        'buttons': [
                            {
                                'text': '取消',
                                'click': function () {
                                    this.close();
                                }
                            },
                            {
                                'text': '立即生成',
                                'className': 'btn-primary',
                                'click': function () {
                                    this.close();
                                    model.openReferralLinkModal({
                                        open_qrcode_modal : false
                                    }).then(function() {
                                        scrollToElement('#wx-article-footer', { offset: 10 }, function () {
                                            editor.changeQRCodeTemplate(template.id);
                                        })
                                    });
                                }
                            }
                        ]
                    });
                } else {
                    scrollToElement('#wx-article-footer', { offset: 10 }, function () {
                        editor.changeQRCodeTemplate(template.id);
                    });
                }
            },

            openReferralLinkModal: function (options) {
                var defer = $.Deferred();

                // ko事件触发的该方法的调用会传递ko的上下文做options参数，这里默认只取需要的参数避免错误.
                options = options ? _.pick(options, 'open_qrcode_modal') : {};
                _.assign(options, { article_id: editor.nextArticleId });

                GetReferralLinkModal.instance
                    .open(options)
                    .then(function (link) {
                        editor.onReferralLinkGenerated(link);
                        model.referral_link_id(link.id);

                        // 每次生成链接要保存文案
                        editor.saveTmpReferralLink().then(function() {
                            defer.resolve();
                        });
                    });

                return defer.promise();
            },

            editReferralLink: function () {
                GetReferralLinkModal.instance
                    .open({
                        id: editor.referralLinkId,
                        article_id: editor.nextArticleId
                    });
            },

            switchEditor: function (data, event) {
                var param = {
                    aid: model.article_id,
                    mode: $(event.target).data('editor')
                };

                if (model.referral_link_id()) {
                    param.referral_link_id = model.referral_link_id();
                }

                location.href = '/backend/wx_article_editor?' + $.param(param);
            },

            currentEditorHtml: function () {
                var qs = parseQueryString();
                if (qs.mode == 'image') {
                    return '<i class="ace-icon glyphicon glyphicon-picture"></i> 图片模式<span class="caret"></span>';
                } else if (qs.mode == 'text') {
                    return '<i class="ace-icon glyphicon glyphicon-text-width"></i> 文本模式<span class="caret"></span>';
                } else if (qs.mode == 'background') {
                    return '<i class="ace-icon glyphicon glyphicon-text-width"></i> 背景图模式<span class="caret"></span>';
                }
            },

            openTmpReferralLinkModal: function () {
                GetTmpReferralLinkModal.instance.open({
                    edit_mode: true,
                    referral_link_id: model.referral_link_id()
                })
            },

            getTmpReferralLink: function () {
                if (!model.referral_link_id()) {
                    Modal.open({
                        'title': '提示',
                        'body': '获取文案公开链接需要先生成推广链接',
                        'buttons': [
                            {
                                'text': '取消',
                                'click': function () {
                                    this.close();
                                }
                            },
                            {
                                'text': '立即生成',
                                'className': 'btn-primary',
                                'click': function () {
                                    this.close();
                                    model.openReferralLinkModal({
                                        open_qrcode_modal : false
                                    }).then(function () {
                                        model.openTmpReferralLinkModal();
                                    })
                                }
                            }
                        ]
                    });
                } else {
                    editor.saveTmpReferralLink().then(function(data) {
                        model.openTmpReferralLinkModal();
                    });
                }
            }
        };

        self.init = function () {
            ko.applyBindings(model, document.getElementById('editor-bar'));
        }
    };
</script>
<div class="modal fade" id="create-referral-link-modal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-bind="click: close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" data-bind="text: title"></h4>
            </div>
            <div class="modal-body">
                <div data-bind="visible: loading" class="loading-panel">
                    <i class="fa fa-spin fa-spinner"></i>
                </div>
                <form class="form-horizontal" style="display: none" data-bind="visible: !loading()">
                    <div class="form-group">
                        <label class="control-label col-sm-3">入口页面</label>
                        <div class="col-sm-7">
                            <p class="form-control-static">
                                <span>墨清欢</span>
                                <!--<span data-bind="visible: type() == 0">小说阅读页</span>
                                <span data-bind="visible: type() == 1">首页</span>
                                <span data-bind="visible: type() == 2">热门推荐</span>
                                <span data-bind="visible: type() == 3">VIP年费充值</span>-->
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-3" style="color:#1E9FFF;"><span class="required">*</span> 外推渠道名称</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" maxlength="100" name="description"
                                   data-val="true"
                                   data-val-required="请填写外推渠道名称"
                                   data-bind="value: description" />
                            <p class="help-block help-block-error" data-valmsg-for="description" data-valmsg-replace="true"></p>
                        </div>
                    </div>
                    <div class="form-group" >
                        <label class="control-label col-sm-3"><span class="required">*</span> 开启强制关注</label>
                        <div class="col-sm-7">
                            <label class="radio-inline">
                                <input type="radio" name="referrer_type" value="verified_mp"
                                       data-bind="checked: referrer_type"
                                       data-val="true"                                       data-val-required="请选择是否开启强制关注"/>
                                <span>开启</span>
                            </label>
                            <!--<label class="radio-inline">-->
                            <!--<input type="radio" name="referrer_type" value="not_verified_mp"-->
                            <!---->
                            <!--data-bind="checked: referrer_type"/>-->
                            <!--<span>关闭</span>-->
                            <!--</label>-->
                            <!--<label class="radio-inline" style="color:red; cursor:default;"><span>*未认证订阅号一定要打开</span></label>-->
                            <p class="help-block help-block-error" data-valmsg-for="referrer_type" data-valmsg-replace="true"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3"><span class="required"></span> 渠道成本</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" maxlength="100" name="cost"
                                   data-val="true"
                                   data-bind="value: cost"
                                   placeholder="如：1200"
                                   onkeyup="this.value=this.value.replace(/[^0-9-]+/,'');"
                            />
                            <p class="help-block help-block-error" data-valmsg-for="cost" data-valmsg-replace="true"></p>
                        </div>
                    </div>

                    <div data-bind="visible: type() == 0" style="display:none">
                        <div class="form-group">
                            <div class="col-sm-7 col-sm-offset-3">
                                <p class="form-control-static">
                                    <img style="width:80px" data-bind="attr: { src: novel_avatar }" />
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-3">阅读原文章节</label>
                            <div class="col-sm-7">
                                <p class="form-control-static">
                                    <strong data-bind="html: article_title"></strong>
                                </p>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="control-label col-sm-3">关注章节序号</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" maxlength="100" name="force_follow_chapter_idx"
                                       placeholder="可选, 如不填则使用小说默认设置"
                                       data-val="true"
                                       data-val-digits="请输入数字"
                                       data-bind="value: force_follow_chapter_idx" />
                                <div data-bind="visible: force_follow_chapter_id" style="display:none;margin-top:5px">
                                    <span data-bind="text: force_follow_chapter_title"></span>
                                </div>
                                <p class="help-block help-block-error" data-valmsg-for="force_follow_chapter_idx" data-valmsg-replace="true"></p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bind="click: submit, text: id() ? '保存修改' : '生成链接'"></button>
            </div>
            <!--<div style="height:800px;">还有很高超出一屏的</div>-->
        </div>
    </div>
</div>

<script>
    var GetReferralLinkModal = function () {
        var self = this;
        var $modal = null;
        var callbacks = null;
        var defer = null;
        var opts = null;
        var link = null; // 生成的链接，{ id, url }
        var model = {
            loading: ko.observable(false),
            submitting: ko.observable(false),

            title: ko.observable(),

            id: ko.observable(),
            type: ko.observable(0),
            article_id: ko.observable(),
            novel_id: ko.observable(),
            novel_avatar: ko.observable(),
            novel_title: ko.observable(),
            article_title: ko.observable(),
            referrer_type: ko.observable('verified_mp'),
            description: ko.observable(),
            cost: ko.observable(),
            force_follow_chapter_idx: ko.observable(),
            force_follow_chapter_id: ko.observable(),
            force_follow_chapter_title: ko.observable(),
            open_qrcode_modal: true,

            submit: function () {
                self.submit();
            },
            close: function () {
                self.close();
            }
        };

        self.open = function (options) {
            self.reset();

            defer = $.Deferred();

            opts = options;

            if (!$modal) {
                $modal = $('#create-referral-link-modal');
                ko.applyBindings(model, $modal.find('.modal-content')[0]);

                model.force_follow_chapter_idx.subscribe(function (idx) {
                    if (!idx || !/^\d+$/.test(idx)) {
                        model.force_follow_chapter_id(null);
                        model.force_follow_chapter_title(null);
                    } else {
                        self.tryFetchForceFollowChapterInfo();
                    }
                });
            }

            callbacks = options.callbacks || {};

            model.title(options.title || (options.id ? '修改外推链接属性' : '生成外推链接'));

            if (options.type !== undefined) {
                model.type(options.type);
            }

            if (options.open_qrcode_modal !== undefined) {
                model.open_qrcode_modal = options.open_qrcode_modal;
            }

            model.loading(true);

            var promise = $.Deferred().resolve().promise();

            if (options.id) {
                model.id(options.id);

                promise = $.get('/index/subchannel/api_get?wntype=txt&sid=' + options.id, function (result) {
                    model.type(result.type);
                    model.article_id(result.article_id);
                    model.description(result.description);
                    model.cost(result.cost);
                    model.referrer_type(result.referrer_type);
                    var chapter_idx = result.force_follow_chapter_idx?result.force_follow_chapter_idx:'';
                    model.force_follow_chapter_idx(chapter_idx);
                });
            } else {
                model.article_id(options.article_id);
            }

            promise.then(function () {
                self.tryFetchNovelArticleInfo();
                self.tryFetchForceFollowChapterInfo();
                model.loading(false);
            });

            $modal.modal('show');

            return defer.promise();
        };

        self.tryFetchNovelArticleInfo = function () {
            var articleId = model.article_id();
            if (!articleId) {
                return $.Deferred().resolve();
            }

            return $.get('/index/sucai/get_short_article_info?type=txt&id=' + articleId)
                .then(function (result) {
                    model.novel_id(result.novel.id);
                    model.novel_avatar(result.novel.avatar);
                    model.novel_title(result.novel.title);
                    model.article_title(result.title);
                });
        };

        self.tryFetchForceFollowChapterInfo = function () {
            var idx = model.force_follow_chapter_idx();
            if (idx) {
                idx = parseInt(idx);
            }

            if (!idx) {
                return false;
            }

            // 如果 novel_id 未加载，也忽略，novel_id 加载后会再触发一次获取关注章节信息
            if (!model.novel_id()) {
                return false;
            }

            $.get('/index/sucai/api_get_basic_info_by_idx', {
                id: model.article_id(),
                nid: model.novel_id(),
                idx: model.force_follow_chapter_idx()
            })
                .then(function (result) {
                    model.force_follow_chapter_id(result.id);
                    model.force_follow_chapter_title(result.title);
                })
                .fail(handleAjaxError);
        };

        self.submit = function () {
            if (model.submitting()) {
                return false;
            }

            if (!$modal.find('form').valid()) {
                return false;
            }

            model.submitting(true);

            $.ajax({
                url: '/index/subchannel/api_save',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    id: model.id(),
                    type: model.type(),
                    wntype: 'txt',
                    article_id: model.article_id(),
                    referrer_type: model.referrer_type(),
                    force_follow_chapter_idx: model.force_follow_chapter_idx(),
                    description: model.description(),
                    cost: model.cost(),
                    infans:0                })
            })
                .then(function (result) {
                    if(result.err > 0){
                        alert(result.msg);
                        return false;
                    }else{
                        link = result;


                        if (callbacks.link_generated) {
                            callbacks.link_generated(result);
                        }

                        self.close();

                        if (model.open_qrcode_modal) {
                            GetReferralLinkQrcodeModal.instance.open({
                                url: link.url,
                                callbacks: {
                                    close: function () {
                                        defer.resolve(link);
                                    }
                                }
                            });
                        } else {
                            defer.resolve(link);
                        }
                    }

                })
                .fail(handleAjaxError)
                .always(function () {
                    model.submitting(false);
                });
        };

        self.close = function () {
            $modal.modal('hide');
        };

        self.reset = function () {
            link = null;

            model.loading(false);
            model.submitting(false);

            model.id(null);
            model.article_id(null);
            model.novel_avatar(null);
            model.novel_title(null);
            model.article_title(null);
            model.referrer_type('verified_mp');
            model.force_follow_chapter_idx(null);
            model.force_follow_chapter_id(null);
            model.force_follow_chapter_title(null);
            model.description(null);
            model.cost(null);
        }
    };

    GetReferralLinkModal.instance = new GetReferralLinkModal();

    $(function () {
        $(document).on('click', '[data-toggle="create-referral-link"]', function () {
            GetReferralLinkModal.instance.open({
                article_id: $(this).data('article-id')
            });

            return false;
        });
    });
</script>
<div class="modal fade" id="get-referral-link-qrcode-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">原文链接</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>原文链接:</strong>
                            <div style="margin:10px 0;word-break:break-all;" class="text-primary link-url"></div>
                            <div style="margin:10px 0;color:red;font-weight:bold;">
                                <i class="fa fa-info-circle"></i> 请务必使用上方链接作为文案的原文链接，不要使用微信中点开后手工复制的链接
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="qrcode" style="padding-left:20px"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <span style="display:inline-block;margin-right:10px;color:red;vertical-align:middle;" class="copy-success-hint"></span>
                <button type="button" class="btn btn-primary btn-copy-ref-link"><i class="fa fa-copy"></i> 复制链接</button>
            </div>
        </div>
    </div>
</div>

<script>
    var GetReferralLinkQrcodeModal = function () {
        var self = this;
        var $modal = null;
        var opts = null;

        this.open = function (options) {
            opts = options;

            if (!$modal) {
                $modal = $('#get-referral-link-qrcode-modal');
                $modal.on('hidden.bs.modal', function () {
                    if (opts.callbacks && opts.callbacks.close) {
                        opts.callbacks.close.apply(self, []);
                    }
                });
                $modal.on('shown.bs.modal', function () {
                    var clipboard = new Clipboard($modal.find('.btn-copy-ref-link')[0], {
                        text: function () {
                            return opts.url;
                        }
                    });
                    clipboard.on('success', function () {
                        var $hint = $modal.find('.copy-success-hint');
                        $hint.html('复制成功!').show();
                    });
                });
            }

            $modal.find('.copy-success-hint').hide();
            $modal.find('.link-url').html(opts.url);

            $modal.find('.qrcode').html('<div></div>');

            new QRCode($modal.find('.qrcode>div')[0], {
                text: opts.url,
                width: 200,
                height: 200,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });

            $modal.modal('show');
        };

        this.close = function () {
            $modal.modal('hide');
        }
    };

    GetReferralLinkQrcodeModal.instance = new GetReferralLinkQrcodeModal();
</script>
<div class="modal fade" id="content-img-modal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">正文图片</h4>
            </div>
            <div class="modal-body">
                <ul class="list-group" data-bind="foreach: items">
                    <li class="list-group-item">
                        <span style="display:inline-block;margin-top:4px;">
                            <span style="font-size:16px;" data-bind="text: name"></span>
                            <span data-bind="visible: status() === 'generating'"><i class="fa fa-spin fa-spinner"></i></span>

                            <span class="text-muted" style="display: none;" data-bind="visible: status() === 'generated'">
                                (<span data-bind="text: width"></span> x <span data-bind="text: height"></span>)
                            </span>
                        </span>
                        <button type="button" class="btn btn-sm btn-default pull-right" data-bind="visible: status() === 'generated', click: $root.download"><i class="fa fa-download"></i> 下载</button>

                        <div style="color:red;display:none;" data-bind="visible: err_msg, html: err_msg"></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    var ContentImgModal = function () {
        var self = this;
        var $modal = null;
        var model = {
            items: ko.observableArray(),
            download: function (item) {
                var canvas = item.canvas;
                if (!canvas.toBlob && !window.URL.createObjectURL) {
                    alert('浏览器版本过低, 请更新版本或使用谷歌浏览器');
                    return false;
                }

                canvas.toBlob(function (blob) {
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = item.name + '.png';
                    link.click();
                }, 'image/png');
            }
        };

        this.open = function () {
            ensureModal();
            $modal.modal('show');
        };

        this.reset = function () {
            model.items([]);
        };

        this.addItem = function (item) {
            model.items.push({
                id: item.id,
                name: item.name,
                status: ko.observable('generating'),
                width: ko.observable(),
                height: ko.observable(),
                err_msg: ko.observable(),
                canvas: null
            })
        };

        this.markCompleted = function (itemId, canvas) {
            var item = _.find(model.items(), function (it) {
                return it.id === itemId;
            });

            item.status('generated');
            item.canvas = canvas;
            item.width(canvas.width);
            item.height(canvas.height);
        };

        function ensureModal() {
            if (!$modal) {
                $modal = $('#content-img-modal');
                ko.applyBindings(model, $modal.find('.modal-content')[0]);
            }
        }
    };

    ContentImgModal.instance = new ContentImgModal();
</script>    <script>
    $(function () {
        var editor = new WxArticleEditor({
            novel_id: 4791,
            is_novel_online: true,
            is_testing: false,
            article_id: 70491,
            next_article_id: 70491,
            category_id: 15,
            referral_link_id: null,
            referral_link_url: '',
            title_id: null,
            cover_id:  null,
            body_template_id: '',
            footer_template_id: '',
            qrcode_template_id: null            });

        editor.init();

        $('[data-toggle="copy-text"]').each(function () {
            new Clipboard(this).on('success', function (e) {
                e.clearSelection();
                toastr.success('复制成功');
            });
        });

        $('[data-toggle="copy-link"]').each(function () {
            var clipboard = new Clipboard(this, {
                text: function () {
                    return $('#txt-referral-link').val();
                }
            });
            clipboard.on('success', function (e) {
                e.clearSelection();
                toastr.success('链接复制成功');
            });
        });

        $('[data-toggle="get-link"]').click(function () {
            GetReferralLinkQrcodeModal.instance.open({
                url: $('#txt-referral-link').val()
            });
            return false;
        });

        $('#btn-create-content-img').click(function () {
            var modal = ContentImgModal.instance;
            modal.reset();

            var $chapters = editor.$body().find('.chapter');

            $chapters.each(function (i) {
                modal.addItem({
                    id: i + 1,
                    name: '第' + (i + 1) + '章'
                });
            });

            modal.open();

            $chapters.each(function (i) {
                editor.drawHTML($(this)).then(function (canvas) {
                    modal.markCompleted(i + 1, canvas);
                });
            });

            return false;
        });
    });

    var WxArticleEditor = function (options) {
        var self = this;
        var previewArticles = [];
        var covers = [];
        var titles = [];
        var footerTemplates = [];
        var qrcodeTemplates = [];
        var bodyTemplates = [];

        var $title = $('#wx-article-title');
        var $cover = $('#wx-article-cover');
        var $body = $('#wx-article-body');
        var $qrcode = $('#wx-article-qrcode');
        var $footer = $('#wx-article-footer');

        var editorBar = null;

        this.titleId = options.title_id;
        this.title = null;

        this.coverId = options.cover_id;
        this.coverUrl = null;

        this.bodyTemplateId = options.body_template_id;

        this.footerTemplateId = options.footer_template_id;
        this.footerUrl = null;

        this.qrcodeTemplateId = options.qrcode_template_id;
        this.qrcodeUrl = null;

        this.referralLinkId = options.referral_link_id;

        this.referralLinkUrl = options.referral_link_url;

        this.isNovelOnline = options.is_novel_online;

        this.isTesting = options.is_testing;

        this.novelId = options.novel_id;

        this.categoryId = options.category_id;

        this.articleId = options.article_id;

        this.nextArticleId = options.next_article_id;

        this.init = function () {
            return $.when(
                loadCovers(),
                loadTitles(),
                loadFooterTemplates(),
                loadQRCodeTemplates(),
                loadBodyTemplates(),
                loadPreviewArticles()
            )
                .then(function () {
                    editorBar = new EditorBar({
                        editor: self,
                        is_novel_online: options.is_novel_online,
                        is_testing: options.is_testing,
                        referral_link_id: options.referral_link_id,
                        titles: titles,
                        covers: covers,
                        body_templates: _.map(bodyTemplates, function (it) {
                            return { id: it.id, preview_img: it.preview_img };
                        }),
                        footer_templates: _.map(footerTemplates, function (it) {
                            return { id: it.id, preview_img: it.preview_img };
                        }),
                        qrcode_templates: _.map(qrcodeTemplates, function (it) {
                            return { id: it.id, preview_img: it.preview_img };
                        })
                    });

                    editorBar.init();



                    if (self.titleId) {
                        self.changeTitle(self.titleId);
                    } else {
                        renderTitle(_.sample(titles));
                    }

                    if (self.coverId) {
                        self.changeCover(self.coverId);
                    } else {
                        renderCover(_.sample(covers));
                    }

                    if (self.bodyTemplateId) {
                        self.changeBodyTemplate(self.bodyTemplateId);
                    } else {
                        renderBody(_.sample(bodyTemplates));
                    }

                    if (self.footerTemplateId) {
                        self.changeFooterTemplate(self.footerTemplateId);
                    } else {
                        renderFooter(_.sample(footerTemplates));
                    }

                    if (self.qrcodeTemplateId) {
                        self.changeQRCodeTemplate(self.qrcodeTemplateId);
                    }

                    self.initReferralPanel();
                });
        };

        this.$body = function () {
            return $body;
        };

        this.changeTitle = function (id) {
            var title = _.find(titles, function (it) {
                return it.id == id;
            });

            renderTitle(title);
        };

        this.changeCover = function (id) {
            var cover = _.find(covers, function (it) {
                return it.id == id;
            });

            renderCover(cover);
        };

        this.changeBodyTemplate = function (id) {
            var template = _.find(bodyTemplates, function (it) {
                return it.id == id;
            });

            renderBody(template);
        };

        this.changeFooterTemplate = function (id) {
            var template = _.find(footerTemplates, function (it) {
                return it.id == id;
            });

            renderFooter(template);
        };

        this.changeQRCodeTemplate = function (id) {
            if(!self.referralLinkUrl) {
                return;
            }

            var template = _.find(qrcodeTemplates, function (it) {
                return it.id == id;
            });

            if (!template) {
                // 不设置二维码
                renderQRCode(null);
                return;
            }

            $.ajax({
                url: '/index/sucai/generate_qrcode_referral_image',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    referral_id: self.referralLinkId,
                    template_id : template.id
                })
            })
                .then(function (data) {
                    template = {
                        'id': template.id,
                        'preview_img': data.url,
                        'template': '<img style="max-width:100%" src="' + data.url + '"/>'
                    };

                    renderQRCode(template);
                })
                .fail(handleAjaxError);
        };

        this.drawHTML = function ($el) {
            $el.find('img').each(function () {
                var src = $(this).attr('src');
                if (src && src.indexOf('http') === 0) {
                    $(this).attr('src', '/backend/wx_article_editor/proxy?url=' + encodeURIComponent(src));
                }
            });

            var canvas = document.createElement('canvas');

            var scaleFactor = 2;
            var targetWidth = 320;

            var fontFamily = 'Kaiti, STKaiti, STSong, NSimSun, "Microsoft YaHei", sans-serif';
            var fontWeight = 'normal';
            var fontSize = '20px !important';

            var html = $el.html();
            html = '<html>' +
                '<head>' +
                '<style>' +
                'html, body { padding:0;margin:0;font-family: ' + fontFamily + '; } ' +
                '.chapter-title { font-size: 20px !important; }' +
                '.chapter-content p {margin:0.6em 0; font-weight:' + fontWeight + '; text-indent:2em; font-size:' + fontSize + '; line-height:1.6em !important; } ' +
                '</style>' +
                '</head>' +
                '<body style="background-color:white"><div style="width:' + targetWidth + 'px">' + html + '</div></body></html>';

            return rasterizeHTML.drawHTML(html, null, {
                zoom: scaleFactor,
                width: parseInt(targetWidth, 10) * scaleFactor
            })
                .then(function (result) {

                    var targetHeight = Math.floor(result.image.height / result.image.width * targetWidth);

                    canvas.style.width = targetWidth + 'px';
                    canvas.style.height = targetHeight + 'px';

                    canvas.width = targetWidth * scaleFactor;
                    canvas.height = targetHeight * scaleFactor;

                    var ctx = canvas.getContext('2d');
                    ctx.fillStyle = 'white';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    canvas.getContext('2d').drawImage(result.image, 0, 0);

                    return canvas;
                });
        };

        this.onReferralLinkGenerated = function (link) {
            if (link) {
                self.referralLinkId = link.id;
                self.referralLinkUrl = link.url;

                self.initReferralPanel();

                if (self.qrcodeTemplateId) {
                    self.changeQRCodeTemplate(self.qrcodeTemplateId);
                }
            }
        };

        this.initReferralPanel = function () {
            if ((self.isNovelOnline || self.isTesting) && self.referralLinkId) {
                $('#txt-referral-link').val(self.referralLinkUrl);
                $('.panel-referral-link').show();
            } else {
                $('#txt-referral-link').val('');
                $('.panel-referral-link').hide();
            }
        };

        this.saveTmpReferralLink = function () {
            var defer = $.Deferred();
            if ((self.isNovelOnline || self.isTesting) && self.referralLinkId && self.referralLinkUrl) {
                $.ajax({
                    url: '/index/subchannel/api_save_temp',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        article_id:self.articleId,
                        referral_link_id: self.referralLinkId,
                        mode: 1,
                        settings : {
                            referral_link_url: self.referralLinkUrl,
                            title_id: self.titleId,
                            title: self.title,
                            cover_id: self.coverId,
                            cover_url: self.coverUrl,
                            body_template_id: self.bodyTemplateId,
                            footer_template_id: self.footerTemplateId,
                            footer_url: self.footerUrl,
                            qrcode_template_id: self.qrcodeTemplateId,
                            qrcode_url: self.qrcodeUrl
                        }
                    })
                })
                    .then(function (data) {
                        if(data.err > 0){
                            alert(data.msg);
                        }else{
                            defer.resolve(data);
                        }
                    })
                    .fail(handleAjaxError);
            }
            return defer.promise();
        };

        function renderTitle(title) {
            if (!title) {
                self.title = null;
                return false;
            }

            self.titleId = title.id;
            self.title = title.title;

            $title.html(title.title);
        }

        function renderCover(cover) {
            if (!cover) {
                self.coverUrl = null;
                return false;
            }

            self.coverId = cover.id;
            self.coverUrl = cover.cover_url;

            $cover.html('<img style="width:100%;display:block;margin-bottom:20px;" src="' + cover.cover_url + '" />');
        }

        function renderBody(template) {
            if (!template) {
                self.bodyTemplateId = null;
                return false;
            }

            self.bodyTemplateId = template.id;


            if (!template.compiled_template) {
                template.compiled_template = Handlebars.compile(template.template);
            }

            $body.html(template.compiled_template({ chapters: previewArticles }));
        }

        function renderFooter(template) {
            if (!template) {
                self.footerTemplateId = null;
                self.footerUrl = null;
                $footer.html('');
            } else {
                //console.log(template.id);
                self.footerTemplateId = template.id;
                self.footerUrl = template.preview_img;
                $footer.html(template.template);
            }
        }

        function renderQRCode(template) {
            if (!template) {
                self.qrcodeTemplateId = null;
                self.qrcodeUrl = null;
                $qrcode.html('');
            } else {
                self.qrcodeTemplateId = template.id;
                self.qrcodeUrl = template.preview_img;
                $qrcode.html(template.template);
            }
        }

        function loadPreviewArticles() {
            return $.get('/index/sucai/get_preview_articles?current_article_id=' + self.articleId, function (data) {
                previewArticles = data;

                _.each(previewArticles, function (article) {
                    article.paragraphs = _.map(article.paragraphs, function (para) {
                        return para.replace(/^[　\s]+/, '');
                    });
                })
            });
        }

        function loadCovers() {
            return $.get('/index/sucai/get_covers?type=1&per_page=80&cid=' + self.categoryId, function (data) {
                covers = data;
            });
        }

        function loadTitles() {
            return $.get('/index/sucai/get_titles?type=1&cid=' + self.categoryId, function (data) {
                titles = data;
            });
        }

        function loadFooterTemplates() {
            return $.get('/index/sucai/get_footers', function (data) {
                footerTemplates = data;
            });
        }

        function loadQRCodeTemplates() {
            return $.get('/index/sucai/get_qrcode_templates', function (data) {
                qrcodeTemplates = data;
            });
        }

        function loadBodyTemplates() {
            return $.get('/index/sucai/get_body_templates?mode=text', function (data) {
                bodyTemplates = data;
            });
        }
    }
</script>
<script>
    //标题加检索
    function searchtit($this){
        var filter = $this.val();
        var oLi = $this.parent().parent().find('li');
        if (filter) {
            oLi.each(function(){
                var oneLi = $(this);
                var tit = oneLi.html();
                if(tit.indexOf(filter) != -1){
                    oneLi.show();
                }else{
                    oneLi.hide();
                }
            });
        } else {
            oLi.show();
        }
    }
</script>
</body>
</html>