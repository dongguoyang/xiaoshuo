<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

// Admin 使用视图默认在 view/admin 文件夹内
app('view')->prependNamespace('admin', resource_path('views/admin'));
\Encore\Admin\Admin::js('https://cdn.staticfile.org/jquery.qrcode/1.0/jquery.qrcode.min.js');
\Encore\Admin\Admin::js('/admin/js/actions.js'); // 添加后台自定义 js
\Encore\Admin\Admin::css('/admin/css/style.css'); // 添加后台自定义 css

// Encore\Admin\Form::forget(['map', 'editor']);


\Encore\Admin\Form::extend('fencurrency', \App\Admin\Extensions\Forms\FenCurrency::class); // 扩展金额元到分插件
\Encore\Admin\Grid\Column::extend('fen2yuan', \App\Admin\Extensions\Grids\Shows\Fen2Yuan::class); // 不知道为什么没有生效
\Encore\Admin\Form::extend('urlcatch', \App\Admin\Extensions\Forms\ArcCatch::class);
\Encore\Admin\Form::extend('jsonvalue', \App\Admin\Extensions\Forms\JsonValues::class);