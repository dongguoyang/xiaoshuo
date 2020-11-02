<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{config('admin.title')}} | {{ trans('admin.login') }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/font-awesome/css/font-awesome.min.css") }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css") }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/AdminLTE/plugins/iCheck/square/blue.css") }}">

    <style>
        .login-box {
            position: absolute;
            border-radius: 20px;
            width: 1094px;
            height: 600px;
            left: 50%;
            top: 50%;
            transform: translateX(-50%) translateY(-50%) !important;
        }

        .login-box .title {
            z-index: 999;
            position: absolute;
            left: 86px;
            top: 396px;
            font-size: 38px;
            color: #ffffff;
        }

        .login-box .overlay {
            top: 76px;
            left: 50%;
            transform: translateX(-50%);
            width: 1004px;
            height: 577px;
            position: absolute;
            z-index: 500;
        }

        .login-box .user-logo {
            height: 100%;
            display: block;
            position: absolute;
            z-index: 600;
            left: 0;
            top: 0;
        }

        /*.login-page{background-image: url("/admin/img/login.bg.jpg");background-size: cover;background-repeat: no-repeat;}*/
        .login-box-body {
            border-radius: 0 20px 20px 0;
            height: 100%;
            width: 578px;
            position: absolute;
            right: 0;
            top: 0;
            z-index: 999;
            background: rgba(255, 255, 255, .8);
        }

        .login-page {
            height: 100%;
            overflow: hidden;
            width: 100%;
        }

        .login-page > .bg-wrapper {
            display: block;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .login-page > .bg-wrapper > img {
            display: block;
            width: 100%;
        }

        .login-box {
            margin: 0 !important;
        }

        .login-logo {
            text-align: left;
            margin-left: 10px !important;
        }

        .login-logo > a {
            text-align: left;
            font-size: 48px !important;
            color: #333 !important;
        }

        .login-box-body {
            background: #ffffff !important;
            padding-left: 100px;
            padding-top: 0 !important;
        }

        .login-box-body .btn-login {
            outline: none;
            background: linear-gradient(to right, #3f34db, #5280f9);
            border: none;
            color: #ffffff;
            font-size: 20px;
            margin-top: 40px;
            width: 376px;
            border-radius: 33px;
            height: 56px;
            text-align: center;
        }

        .field-wrapper {
            position: relative;
            margin-bottom: 30px;
            padding-left: 24px;
            line-height: 48px;
            font-size: 0;
            border-radius: 24px;
            background: #fafafa;
            width: 376px;
            height: 48px;
            text-align: left;
        }

        .field-wrapper .icon {
            margin-right: 20px;
            vertical-align: middle;
            height: 18px;
            display: inline-block;
            width: 18px;
        }

        .field-wrapper .icon img {
            display: block;
            width: 100%;
        }

        .field-wrapper .input-field {
            background: transparent;
            outline: none;
            color: #999;
            vertical-align: middle;
            font-size: 16px;
            line-height: 48px;
            display: inline-block;
            border: none;
        }

        .login-box-msg {
            text-align: left !important;
            padding: 0 !important;
            font-size: 32px;
            color: #333333 !important;
            margin-top: 75px !important;
            margin-bottom: 58px !important;
        }

        .captcha-img {
            border: 1px solid #f1f1f1;
            vertical-align: top;
            cursor: pointer;
            height: 48px;
            width: 128px;
            border-radius: 24px;
            margin-left: 20px;
        }

        .tip-wrapper {
            font-size: 12px;
            left: 65px;
            position: absolute;
            color: red;
            z-index: 999;
            width: 100%;
            top: 40px;
        }
    </style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition login-page">
<div class="bg-wrapper">
    {{--<img class="bg" src="/admin/img/login-2.bg.jpg" alt="">--}}
    <img class="bg" src="http://novelsys.oss-cn-shenzhen.aliyuncs.com/front/web_bg.png" alt="">
</div>

<div class="login-box">
    <p class="title">造梦人后台管理系统</p>
    <img class="overlay" src="http://novelsys.oss-cn-shenzhen.aliyuncs.com/front/web_tmc.png" alt="">
    <img class="user-logo" src="http://novelsys.oss-cn-shenzhen.aliyuncs.com/front/web_person.png" alt="">
{{--<div class="login-logo">--}}
{{--<a href="{{ admin_base_path('/') }}" style="color:black"><b>{{config('admin.name')}}</b></a>--}}
{{--</div>--}}
<!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg" style="color:white">{{ trans('admin.login') }}</p>

        <form action="{{ admin_base_path('auth/login') }}" method="post">
            <div class="username-wrapper field-wrapper">
                <div class="icon">
                    <img src="/admin/img/mine_icon.png" alt="">
                </div>
                <input placeholder="请输入用户名" class="input-field" type="text" name="username">
                <div class="tip-wrapper">
                    @if($errors->has('username'))
                        @foreach($errors->get('username') as $message)
                            <label class="control-label" for="inputError"><i
                                    class="fa fa-times-circle-o"></i>{{$message}}
                            </label></br>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="password-wrapper field-wrapper">
                <div class="icon">
                    <img src="/admin/img/password_icon.png" alt="">
                </div>
                <input placeholder="请输入密码" class="input-field" type="password" name="password">
                <div class="tip-wrapper">
                    @if($errors->has('password'))
                        @foreach($errors->get('password') as $message)
                            <label class="control-label" for="inputError"><i
                                    class="fa fa-times-circle-o"></i>{{$message}}
                            </label></br>
                        @endforeach
                    @endif
                </div>
            </div>
            <div style="width: 218px; display: inline-block;" class="captcha-wrapper field-wrapper">
                <div class="icon">
                    <img src="/admin/img/secure_icon.png" alt="">
                </div>
                <input style="width: 100px;" placeholder="验证码" class="input-field" type="text" name="captcha">
                <div class="tip-wrapper" style="margin-bottom: 5px;">
                    @if($errors->has('captcha'))
                        <label class="control-label"><i class="fa fa-times-circle-o"></i>{{$errors->first('captcha')}}</label>
                    @endif
                </div>
            </div>
            <img src="{{captcha_src('math')}}" class="captcha-img"
                 style="cursor: pointer; height: 48px; width: 128px; border-radius: 24px;"
                 onclick="this.src='{{captcha_src('math')}}'+Math.random()">

            <button class="btn-login" type="submit">点击登录</button>
        </form>

        {{--<form action="{{ admin_base_path('auth/login') }}" method="post">--}}
        {{--<div class="form-group has-feedback {!! !$errors->has('username') ?: 'has-error' !!}">--}}

        {{--@if($errors->has('username'))--}}
        {{--@foreach($errors->get('username') as $message)--}}
        {{--<label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}--}}
        {{--</label></br>--}}
        {{--@endforeach--}}
        {{--@endif--}}

        {{--<input type="input" class="form-control" placeholder="{{ trans('admin.username') }}" name="username"--}}
        {{--value="{{ old('username') }}">--}}
        {{--<span class="glyphicon glyphicon-envelope form-control-feedback"></span>--}}
        {{--</div>--}}

        {{--<div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">--}}

        {{--@if($errors->has('password'))--}}
        {{--@foreach($errors->get('password') as $message)--}}
        {{--<label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}--}}
        {{--</label></br>--}}
        {{--@endforeach--}}
        {{--@endif--}}

        {{--<input type="password" class="form-control" placeholder="{{ trans('admin.password') }}" name="password">--}}
        {{--<span class="glyphicon glyphicon-lock form-control-feedback"></span>--}}
        {{--</div>--}}
        {{--<div class="form-group row">--}}
        {{--<div class="form-group row">--}}
        {{--<div class="col-md-4">--}}
        {{--<img src="{{captcha_src('math')}}" style="cursor: pointer" onclick="this.src='{{captcha_src('math')}}'+Math.random()">--}}
        {{--</div>--}}
        {{--<div class="col-md-8">--}}
        {{--<input id="captcha" placeholder="验证码" class="form-control" type="captcha" name="captcha" value="{{ old('captcha')  }}" required>--}}
        {{--@if($errors->has('captcha'))--}}
        {{--<div>--}}
        {{--<p class="text-danger text-left"><strong>{{$errors->first('captcha')}}</strong></p>--}}
        {{--</div>--}}
        {{--@endif--}}
        {{--</div>--}}
        {{--</div>--}}
        {{--</div>--}}

        {{--<div class="form-group row">--}}

        {{--<!-- /.col -->--}}
        {{--<div class="col-md-12">--}}
        {{--<input type="hidden" name="_token" value="{{ csrf_token() }}">--}}
        {{--<button type="submit" class="btn btn-primary btn-block btn-flat">{{ trans('admin.login') }}</button>--}}
        {{--</div>--}}
        {{--<!-- /.col -->--}}
        {{--</div>--}}
        {{--</form>--}}

    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<!-- jQuery 2.1.4 -->
<script src="{{ admin_asset("/vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js")}} "></script>
<!-- Bootstrap 3.3.5 -->
<script src="{{ admin_asset("/vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js")}}"></script>
<!-- iCheck -->
<script src="{{ admin_asset("/vendor/laravel-admin/AdminLTE/plugins/iCheck/icheck.min.js")}}"></script>
<script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    });
</script>
</body>
</html>
