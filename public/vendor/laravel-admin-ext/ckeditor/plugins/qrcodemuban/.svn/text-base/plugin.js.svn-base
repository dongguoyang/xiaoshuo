/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @fileOverview Print Plugin
 */

CKEDITOR.plugins.add( 'qrcodemuban', {
    icons: 'qrcodemuban',
    init: function( editor ) {
        editor.addCommand( 'insertQrcodemuban', {
            exec: function( editor ) {
                editor.insertHtml( '<img style="width: 180px;" class="qrcodemuban" src="https://adssystem.oss-cn-shenzhen.aliyuncs.com/article_sys/article/imgs/ade913cf6cb342e3ba99541e40682dd0.png" >' );
            }
        });
        editor.ui.addButton( 'Qrcodemuban', {
            label: '插入底部二维码模板（前端浏览会自动替换为当前页面地址二维码）',
            command: 'insertQrcodemuban',
            toolbar: 'insert'
        });
    }
});