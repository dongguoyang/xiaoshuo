/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	
	// %REMOVE_START%
	// The configuration options below are needed when running CKEditor from source files.
	config.plugins = 'dialogui,dialog,about,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,notification,button,toolbar,clipboard,panelbutton,panel,floatpanel,colorbutton,colordialog,templates,menu,contextmenu,copyformatting,div,resize,elementspath,enterkey,entities,popup,filetools,filebrowser,find,fakeobjects,flash,floatingspace,listblock,richcombo,font,forms,format,horizontalrule,htmlwriter,iframe,wysiwygarea,image,indent,indentblock,indentlist,smiley,justify,menubutton,language,link,list,liststyle,magicline,maximize,newpage,pagebreak,pastetext,pastetools,pastefromgdocs,pastefromword,preview,print,removeformat,save,selectall,showblocks,showborders,sourcearea,specialchar,scayt,stylescombo,tab,table,tabletools,tableselection,undo,lineutils,widgetselection,widget,notificationaggregator,uploadwidget,uploadimage,wsc';
	config.skin = 'office2013';// kama moono-lisa office2013
	// %REMOVE_END%

	// Define changes to default configuration here. For example:
	config.language = 'zh-cn';
	// config.uiColor = '#AADC6E';


	config.uploadUrl = '/administrator/common/ckeditorupload?command=QuickUpload&type=Files&responseType=json&refer=CKEDITOR';
	config.filebrowserUploadUrl = '/administrator/common/ckeditorupload?command=QuickUpload&type=Files&responseType=json&refer=CKEDITOR';	// 超链接上传地址 
	config.filebrowserImageUploadUrl = '/administrator/common/ckeditorupload?command=QuickUpload&type=Images&responseType=json&refer=CKEDITOR';	// 超链接上传地址 

	// config.extraPlugins = 'colordialog,colorbutton,panelbutton,font,qrcodemuban';
	config.extraPlugins = 'qrcodemuban';
	config.font_names='宋体/SimSun;新宋体/NSimSun;仿宋_GB2312/FangSong_GB2312;楷体_GB2312/KaiTi_GB2312;黑体/SimHei;微软雅黑/Microsoft YaHei;Arial;Comic Sans MS;Courier New;Tahoma;Verdana';

};
