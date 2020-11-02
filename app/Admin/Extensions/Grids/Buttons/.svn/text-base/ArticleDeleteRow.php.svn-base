<?php
/**
 * Created by PhpStorm.
 * User: byhenry
 * Date: 2019/1/12
 * Time: 12:11 PM
 */

namespace App\Admin\Extensions\Grids\Buttons;


use Encore\Admin\Admin;

class ArticleDeleteRow
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function script()
    {
        return <<<SCRIPT

$('.article-delete-row').on('click', function () {
    var id = $(this).data('id');
    swal({
        title: "确认删除?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "确认",
        showLoaderOnConfirm: true,
        cancelButtonText: "取消",
        preConfirm: function() {
            return new Promise(function(resolve) {
                $.ajax({
                    method: 'post',
                    url: '/administrator/article2s/'+ id + '/delete',
                    data: {
                        _method:'delete',
                        _token:LA.token,
                    },
                    success: function (data) {
                        resolve(data);
                    }
                });
            });
        }
    }).then(function(result) {
        var data = result.value;
        if (typeof data === 'object') {
            if (data.status == 1) {
                $.pjax.reload('#pjax-container');
                swal(data.info, '', 'success');
            } else {
                swal(data.info, '', 'error');
            }
        }
    });
});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());

        return "<a class='article-delete-row' data-id='{$this->id}' style='cursor: pointer'><i class='fa fa-trash'></i></a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}