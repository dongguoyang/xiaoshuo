//       title           desc            提示类型
// swal('提示标题！', '提示具体信息！', 'error');
$(function () {
    // 批量操作 带选框输入的
    $('body').on('click', '.confirm2tip', function() {
        var my = this;
        var all = $(my).data('allids') == 1 ? true : false;
        selectIds(my, all);
        swal({
            title: "确定要执行该操作吗？",
            html: tipFunc($(my).data('tipfunc')),
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "确认",
            showLoaderOnConfirm: true,
            cancelButtonText: "取消",
            preConfirm: function() {
                return new Promise(function(resolve) {
                    var data = $(my).data();
                    data.ids = selectIds(my, all);
                    data._token = LA.token;

                    var tipmsg = $("#tip2msg").val();
                    tipmsg = tipmsg ? tipmsg : $("#tip2sel").val();
                    if(tipmsg == ''){
                        swal('请输入原因！', '', 'error');
                        return;
                    }
                    if(data.ids.length == 0){
                        swal('请选择操作对象！', '', 'error');
                        return;
                    }

                    data.tipmsg = tipmsg;

                    $.ajax({
                        method: 'post',
                        url: $(my).data('url'),
                        data: data,
                        success: function (data) {
                            if (data.err_code == 0) {
                                $.pjax.reload('#pjax-container');
                                swal('执行成功',data.err_msg, 'success');
                            } else {
                                swal('执行失败', data.err_msg, 'error');
                            }
                        },
                        error: function (data) {
                            swal('执行失败', '接口异常！', 'error');
                        }
                    });
                });
            }
        });
    });

    // 确认对话框执行
    $('body').on('click', '.confirm2do', function() {
        var my = this;
        var all = $(my).data('allids') == 1 ? true : false;
        selectIds(my, all);
        swal({
            title: "确定要执行操作吗？",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "确认",
            showLoaderOnConfirm: true,
            cancelButtonText: "取消",
            preConfirm: function() {
                return new Promise(function(resolve) {
                    var data = $(my).data();
                    data.ids = selectIds(my, all);
                    data._token = LA.token;
                    if(data.ids.length == 0){
                        swal('请选择操作对象！', '', 'error');
                        return;
                    }

                    $.ajax({
                        method: 'post',
                        url: $(my).data('url'),
                        data: data,
                        success: function (data) {
                            if (data.err_code == 0) {
                                $.pjax.reload('#pjax-container');
                                swal('执行成功', data.err_msg, 'success');
                            } else {
                                swal('执行失败', data.err_msg, 'error');
                            }
                        },
                        error: function (data) {
                            swal('执行失败', '接口异常！', 'error');
                        }
                    });
                });
            }
        });
    });

    // 确认对话框执行
    $('body').on('click', '.confirm2doall', function() {
        var my = this;
        if ($(this).data('allids') == 1 || $(this).data('allids') == true) {
            var ids = 'all';
        }  else {
            var ids = new Array();
            $("input.grid-row-checkbox[type='checkbox']:checked").each(function () {
                var id = $(this).data('id');
                if (id > 0) {
                    ids.push(id);
                }
            });
            ids = ids.join(',')
        }
        swal({
            title: "确定要执行操作吗？",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "确认",
            showLoaderOnConfirm: true,
            cancelButtonText: "取消",
            preConfirm: function() {
                return new Promise(function(resolve) {
                    var data = $(my).data();
                    data.ids = ids;
                    data._token = LA.token;
                    if(data.ids.length == 0){
                        swal('请选择操作对象！', '', 'error');
                        return;
                    }

                    $.ajax({
                        method: 'post',
                        url: $(my).data('url'),
                        data: data,
                        success: function (data) {
                            if (data.err_code == 0) {
                                $.pjax.reload('#pjax-container');
                                swal('执行成功', data.err_msg, 'success');
                            } else {
                                swal('执行失败', data.err_msg, 'error');
                            }
                        },
                        error: function (data) {
                            swal('执行失败', '接口异常！', 'error');
                        }
                    });
                });
            }
        });
    });

    // js 跳转编辑页面
    $(".grid-row-edit2").on('click', function () {
        var url = window.location.href;
        url = url.split('?')[0] + '/' + $(this).data('id') + '/edit';
        if ($(this).data('param') != undefined) {
            url += '?' + $(this).data('param');
        }
        location.href = url;
    });

    $('body').on('click','.copybtn',(function (e) {
        // 复制功能
        var content = $(this).data('content');
        var temp = $('<input>');
        $("body").append(temp);
        temp.val(content).select();
        document.execCommand("copy");
        temp.remove();
        $(this).tooltip('show');
        var copysucc = '<div class="copysucc" style="position: fixed;top: 47%;left: 49%;padding: 10px 20px;background: rgba(0,0,0,.7);color:#fff;border-radius: 5px;display: none;">' +
            '复制成功！' +
            '</div>';
        $("body").append(copysucc);
        $('.copysucc').fadeIn();
        setTimeout(function () {
            $('.copysucc').fadeOut();
            setTimeout(function () {
                $(".copysucc").remove();
            }, 1000);
        }, 2000);
    }));
});


/**
 * 选中操作行并返回选中的id列表
 * @param my 当前点击元素DOM
 * @param all 是否选中所有
 * @return array
 */
function selectIds (my, all = false) {
    if (all === true) {
        // 批量操作只能手动选择；所以不用执行下面的选择代码
        // $('input.grid-row-checkbox:checkbox, input.grid-select-all:checkbox').iCheck('check');
    } else {
        $('input.grid-row-checkbox:checkbox, input.grid-select-all:checkbox').iCheck('uncheck');
        var icheckTd = $(my).parent('td').prevAll('td');
        var id = icheckTd.find('input.grid-row-checkbox:checkbox').data('id');
        icheckTd.find('input.grid-row-checkbox:checkbox').iCheck('check');
        //icheckTd.find('input.grid-row-checkbox:checkbox[data-id="'+id+'"]').iCheck('check');
    }

    var ids = [];
    $('input.grid-row-checkbox:checkbox').each(function () {
        if($(this).is(":checked")) {
            ids.push($(this).data('id'))
        }
    });
    console.log(ids.join(','));
    return ids.join(',');
}
/**
 * 驳回提示信息选择
 * */
function tipFunc(func) {
    var options = '<option value="">使用上面文本框填写的驳回原因</option>';
    switch (func) {
        case 'rejectTips':
            options += rejectTips();
            break;
        default:
            options += rejectTips();
            break;
    }
    var html = '<br/>' +
        '<span style="color:#000;font-size:15px;"></span>' +
        '<input id="tip2msg" type="text" style="width:100%;height:35px;color:#000;text-align:center;border-radius:5px;border:1px solid #999;" placeholder="填写原因；该文本框有值就使用文本框值" />' +
        '<br/><br/>' +
        '<select id="tip2sel" style="height:35px;margin-top:5px;max-width:100%;border-radius:5px;border:1px solid #999;">' +

        options  +

        '</select>' +
        '<br/><br/>';

    return html;
}
/**
 * 驳回提示可选栏位
 * */
function rejectTips() {
    // input id = tip2msg       select id = tip2sel
    var html = '<option value="您的有效阅读数量过少！请积极任务或收徒后再次申请提现！">您的有效阅读数量过少！请积极任务或收徒后再次申请提现！</option>' +
        '<option value="请微信实名认证后再次申请提现 ">请微信实名认证后再次申请提现 </option>' +
        '<option value="今日提现次数已达上限！请明日再来提现！">今日提现次数已达上限！请明日再来提现！</option>' +
        '<option value="您的有效阅读过少！请分享文章到群或者朋友圈再次提现！">您的有效阅读过少！请分享文章到群或者朋友圈再次提现！</option>';
    return html;
}





















// ================================ 生成二维码的JS ================================
class hb_class{
    constructor(info){
        console.log(info);
        this.info = info;
        this.cvs = document.createElement("canvas");
        this.ctx = this.cvs.getContext('2d');
        this.cvs.width = this.info.back.w;
        this.cvs.height = this.info.back.h;

    }
    create(){
        var t=this;
        console.log(this)
        t.ctx.drawImage(t.img_hb_back, 0, 0, t.info.back.w, t.info.back.h);
        t.ctx.drawImage(t.img_hb_er, t.info.er.pos.left, t.info.er.pos.top, t.info.er.w, t.info.er.h);
        var imageBase64 = t.cvs.toDataURL();
        $('.qrcodemuban').attr('src',imageBase64);
        $('.loadhb').show();
        window.isGreat=true;
    }
    init(){
        var t=this;
        t.img_hb_back = new Image();
        t.img_hb_er = new Image();
        t.img_hb_back.crossOrigin="anonymous";
        t.img_hb_er.crossOrigin="anonymous";
        t.img_hb_back.src=t.info.back.src;
        console.log(t.img_hb_back.src);

        $('body').append( '<div id="qrcode-just" style="display: none;position: fixed;top: 45%;left: 48%;z-index: 999;" title="点我隐藏"></div>' );
        jQuery('#qrcode-just').qrcode({
            render: "canvas",
            width: 200,
            height: 200,
            text: t.info.er.src
        });
        t.img_hb_er.src=$('#qrcode-just').find('canvas')[0].toDataURL('image/png');
    }
}

window.isGreat=false;
var tewm="http://20110358.667nq.cn/st/000003"; // 二维码网址；地址在html中来的；所以这里注释掉；html中没有这里就不能注释
//                          背景图片信息  宽    高     二维码信息  宽   高       二维码位置
var hbinfo={back:{src:"/img/white.bg.jpg",w:220,h:220},er:{src:"",w:200,h:200,pos:{left:10,top:10,}}};
var hbobj=new hb_class(hbinfo);
hbinfo.er.src=tewm;
hbobj.init();
//hbobj.create();
function createImg(){
    hbobj.create();
    var qrcodesrc = $(".qrcodemuban").attr('src');
    if (qrcodesrc.indexOf('data:image')!=0) {
        setTimeout(function () {
            createImg();
        }, 500);
    }
}
$(function () {
    //createImg();
    $("body").on('click', '#qrcode-just', function () {
        $("#qrcode-just").remove();
    });

    
    $("body").on('click', '.array-blade', function () {
        console.log($(this).height())
        if ($(this).height() > 50) {
            $(this).css('height', '50px');
        } else {
            $(this).css('height', '100%');
        }        
    });
    
    $(".showQrcode").on('click', function () {
        $("#qrcode-just").remove();
        var href = $(this).data('href');
        var id = $(this).data('id');
        tewm = href.substr(0, href.length - 1) + id;
        hbinfo.er.src=tewm;
        hbobj.init();
        hbobj.create();
        $("#qrcode-just").show();
    })
});
// ================================ 生成二维码的JS ================================confirm2do