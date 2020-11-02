<style type="text/css">
.tip-num { font-size: 20px; font-weight: bold; }
.red { color: #f00; }
.green { color: green; }
.alert-tip { display: none; }
.alert-tip { width: 400px; position: fixed; top: 40%; left: 50%; margin-left: -200px; }
</style>
<div class="table-responsive" style="background: #fff;padding: 10px;">
    <div class="form-group" style="padding-bottom: 30px;">
        <div class="col-sm-12">
            <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#demo"> 关闭说明 </button>

            <label class="checkbox-inline">
                <input type="radio" name="en_check_time" class="normalinfo_en_check0" value="0"> 关闭检测
            </label>
            <label class="checkbox-inline">
                <input type="radio" name="en_check_time" class="normalinfo_en_check1" value="1"> 启动检测
            </label>

            <label class="checkbox-inline">
                <select name="check_time_min" class="select2" style="width: 150px;">
                    {{--<option value="5">5 min 分钟</option>--}}
                    <option value="60">1 分钟</option>
                    <option value="120">2 分钟</option>
                    <option value="180">3 分钟</option>
                    <option value="300">5 分钟</option>
                    <option value="600">10 分钟</option>
                    <option value="3600">60 分钟</option>
                </select>
            </label>

            <button type="button" class="btn btn-info pull-right clear_abnormal">清空异常信息</button>
        </div>
    </div>
    <hr>
    @foreach($customers as $customer)
        <table class="table table-hover  table-striped table-bordered table-condensed">
            <caption><span class="green">{{$customer['name']}}</span> 域名数量信息</caption>
            <tbody>
            @foreach($domaintypes as $type)
                <tr class="c{{$customer['id']}}t{{$type['id']}}">
                    <td>{{$type['name']}} 信息:&nbsp;&nbsp;&nbsp;
                        <font class="tip-num red" id="c{{$customer['id']}}t{{$type['id']}}d-all"></font>
                    </td>
                    <td>正常数:&nbsp;&nbsp;&nbsp;
                        <font class="tip-num green" id="c{{$customer['id']}}t{{$type['id']}}d-n"></font>
                    </td>
                    <td>异常数:&nbsp;&nbsp;&nbsp;
                        <font class="tip-num red" id="c{{$customer['id']}}t{{$type['id']}}d-u"></font>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach


    <div class="alert alert-tip alert-success">
        <a href="#" class="close" data-dismiss="alert0">&times;</a>
        <span>配置信息保存成功！</span>
    </div>
    <script type="text/javascript">
        $(function () {
            var set_time = false, url = 'domainnums', customers = [@foreach($customers as $customer) {{$customer['id']}},@endforeach], types = [@foreach($domaintypes as $type) {{$type['id']}},@endforeach], en_check_time = getCookie('en_check_time'), check_time_min = getCookie('check_time_min');
            check_time_min = (check_time_min > 0) ? check_time_min : 60;

            get_domain_num();
            initConf();

            function get_domain_num() {
                console.log('get_domain_num', en_check_time, check_time_min * 1000)
                set_time = setTimeout(function () {
                    console.log('settime')
                    get_domain_num();
                }, check_time_min * 1000);
                if (en_check_time != 1) {
                    return false;
                }

                $.post(url, {'customer':customers, 'types':types}, function(data){
                    console.log(data);
                    for (cid in data) {
                        console.log(cid);
                        for (tid in data[cid]) {
                            console.log(cid);
                            $("#c"+cid+"t"+tid+"d-n").html(data[cid][tid]['n']);
                            $("#c"+cid+"t"+tid+"d-u").html(data[cid][tid]['u']);
                            $("#c"+cid+"t"+tid+"d-all").html(data[cid][tid]['u'] + data[cid][tid]['n']);
                        }
                    }
                    $(".alert-success").fadeIn().find('span').html('检测数据加载成功！');
                    setTimeout(function () { $(".alert-success").fadeOut(); }, 1000);
                });
            }

            function initConf() {
                if (en_check_time != 1) {
                    $("input[name='en_check_time'].normalinfo_en_check0").attr('checked', 'checked');
                } else {
                    $("input[name='en_check_time'].normalinfo_en_check1").attr('checked', 'checked');
                }

                $("select[name='check_time_min']").find("option[value='"+check_time_min+"']").attr('selected', 'selected');
                $(".select2").select2();
            }

            $("input[name='en_check_time']").on('click', function () {
                en_check_time = $("input[name='en_check_time']:checked").val();
                setCookie('en_check_time', en_check_time, 'd30');
                var stat = en_check_time == 1 ? '启动' : '停用';
                $(".alert-success").fadeIn().find('span').html('检测'+stat+'成功！');
                setTimeout(function () { $(".alert-success").fadeOut(); }, 2000);
                console.log('en_check_time', en_check_time);
            });
            $("select[name='check_time_min']").on('change', function () {
                check_time_min = $("select[name='check_time_min']").find('option:selected').val();
                setCookie('check_time_min', check_time_min, 'd30');
                $(".alert-success").fadeIn().find('span').html('检测间隔时间配置成功！');
                setTimeout(function () { $(".alert-success").fadeOut(); }, 2000);
                console.log('check_time_min', check_time_min);
                clearTimeout(set_time);
                get_domain_num();
            });
        });

        /* ================================ js cookie start =================================== */
        //获取cookie
        function getCookie(name) {
            var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
            if (arr = document.cookie.match(reg))
                return unescape(arr[2]);
            else
                return null;
        }
        //删除cookie
        function delCookie(name) {
            var exp = new Date();
            exp.setTime(exp.getTime() - 1);
            var cval = getCookie(name);
            if (cval != null)
                document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
        }
        //设置cookie     name   value  有效期时间
        function setCookie(name, value, time, re = 0) {
            var strsec = getsec(time);
            var exp = new Date();
            exp.setTime(exp.getTime() + strsec * 1);
            document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
            if (getCookie(name) != value && re < 3) {
                setCookie(name, value, time, ++re);
            }
        }

        function getsec(str) {
            var str1 = str.substring(1, str.length) * 1; // 时间数值
            var str2 = str.substring(0, 1); // 时间类型 s 秒、h 小时、 d 天
            if (str2 == "s") {
                return str1 * 1000;
            } else if (str2 == "h") {
                return str1 * 60 * 60 * 1000;
            } else if (str2 == "d") {
                return str1 * 24 * 60 * 60 * 1000;
            }
        }
        /**
         * 获取当前年月日时分秒信息
         */
        function dateTimeInfo() {
            var date = new Date();
            var info = new Array();
            info['y'] = date.getFullYear();
            info['m'] = date.getMonth() + 1;
            info['d'] = date.getDate();
            info['h'] = date.getHours();
            info['i'] = date.getMinutes();
            info['s'] = date.getSeconds();
            return info;
        }
    </script>
</div>