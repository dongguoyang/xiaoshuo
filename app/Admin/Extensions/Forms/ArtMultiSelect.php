<?php
/**
 * Created by PhpStorm.
 * User: byhenry
 * Date: 2019/1/12
 * Time: 3:22 PM
 */

namespace App\Admin\Extensions\Forms;


use Encore\Admin\Admin;

class ArtMultiSelect
{
    protected $tag_ids;

    protected $ad_ids;

    protected $is_edit;

    protected $rec_ids;


    public function __construct($tag_ids, $ad_ids, $rec_ids, $is_edit = 0)
    {
        $this->tag_ids = $tag_ids;
        $this->ad_ids = $ad_ids;
        $this->is_edit = $is_edit;
        $this->rec_ids = $rec_ids;
    }

    protected function script()
    {
       if ($this->is_edit == 1) {
           return <<<SCRIPT

var tag_ids = JSON.parse($('#tag_ids').val());
var ad_ids = JSON.parse($('#ad_ids').val());
var rec_ids = JSON.parse($('#rec_ids').val());
//console.log(tag_ids);
//console.log(ad_ids);

var tags = $('.tag_id').find('option');
var all_tag = [];
tags.each(function(k, v) {
    all_tag.push(parseInt($(v).val()));
});

var need_tags = [];
tag_ids.forEach(function(v, k) {
    var index = $.inArray(v, all_tag);
    if (index > -1) {
        need_tags.push(v);
    }
});
$('.tag_id').val(need_tags).trigger('change');

var recs = $('.rec_article_id').find('option');
var all_rec = [];
recs.each(function(k, v) {
    all_rec.push(parseInt($(v).val()));
});
var need_recs = [];
if (rec_ids.length > 0) {
    rec_ids.forEach(function(v, k) {
        var index = $.inArray(v, all_rec);
        if (index > -1) {
            need_recs.push(v);
        }
    });
}
$('.rec_article_id').val(need_recs).trigger('change');

for(var i in ad_ids) {
    $("[name='pos_adv_ids["+i+"]']").val(ad_ids[i]).trigger('change');    
}

var is_catch_finish = 1;
$('.tocatch').click(function(event) {
    if (is_catch_finish == 0) {
        return false;
    } 
    var spider_url = $.trim($(this).parent().prev().val());
    var that = $(this);
    that.text('loading...');
    is_catch_finish = 0;
    $.ajax({
        url: '/administrator/articles/spider',
        type: 'post',
        data: {
            spider_url: spider_url,
            _token: LA.token
        },
        dataType: 'json',
        success: function(res) {
            is_catch_finish = 1;
            if (res.status == 1) {
                editor.txt.html(res.data.text);
                $("[name='content']").val(res.data.text);
            }else {
                swal('错误', res.info, 'error');
            }
            that.text('马上采集');
        }
    });
    return false;
});

SCRIPT;
       }else {
           return <<<SCRIPT

var is_catch_finish = 1;
$('.tocatch').click(function(event) {
    if (is_catch_finish == 0) {
        return false;
    }
    var spider_url = $.trim($(this).parent().prev().val());
    var that = $(this);
    that.text('loading...');
    is_catch_finish = 0;
    $.ajax({
        url: '/administrator/articles/spider',
        type: 'post',
        data: {
            spider_url: spider_url,
            _token: LA.token
        },
        dataType: 'json',
        success: function(res) {
            is_catch_finish = 1;
            if (res.status == 1) {
                editor.txt.html(res.data.text);
                $("[name='content']").val(res.data.text);
            }else {
                swal('错误', res.info, 'error');
            }
            that.text('马上采集');
        }
    });
    return false;
});

SCRIPT;

       }
    }

    protected function render()
    {
        Admin::script($this->script());
        if ($this->is_edit = 1) {
            $str = "<input class='hidden' id='tag_ids' value='{$this->tag_ids}' />";
            $str .= "<input class='hidden' id='ad_ids' value='{$this->ad_ids}' />";
            $str .= "<input class='hidden' id='rec_ids' value='{$this->rec_ids}'>";
            return $str;
        }
       return '';
    }

    public function __toString()
    {
        return $this->render();
    }
}