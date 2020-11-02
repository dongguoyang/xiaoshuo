<?php
/**
 * 公共方法类
 */
namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Libraries\Facades\Upload;

class CommonController extends Controller
{
    /**
     * ckeditor 文件上传接口
     */
    public function CkeditorUpload()
    {
        $rel['msg'] = '上传成功！';
        $rel['error'] = null;
        $rel['uploaded'] = 1;
        try
        {
            $path = 'admin/ckeditor/' . date('Ymd');
            $file = Upload::setPath($path)->send();
            $rel['fileName'] = basename($file['url']);
            $rel = array_merge($rel, $file);
            return $rel;
        }
        catch (\Exception $e)
        {
            $rel['msg'] = '上传失败！';
            $rel['error'] = $e->getMessage();
            $rel['uploaded'] = 0;
            return $rel;
        }
    }
}