<?php
/**
 * Created by LWL.
 * User: LUWENLONG
 * Date: 2019/3/13
 * Time: 15:15
 * 文件上传封装类
 */

namespace App\Libraries\Images;


use App\Helpers\Tools;
use App\Libraries\Facades\Secret;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManagerStatic;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class Upload
{
    private $file;  // 当前正在上传的文件

    private $type = 'file'; //file, base64, stream  文件流类型

    private $max = 3145728; // 支持的文件大小； 2097152 = 2M ； 3145728 = 3M ；5242880 = 5M

    private $exts = ['png', 'jpg', 'jpeg', 'gif'];  // 允许上传的文件类型

    private $size;  // 当前文件大小

    private $ext;   // 当前文件类型

    private $fileUrl;   // 上传文件的绝对地址

    private $disk = 'oss';  // 使用的存储类型

    private $path = ''; // 上传文件夹

    private $filename = ''; // 文件名称

    private $saveKey2Val = true;    // 上传文件保留 input name 作为 键

    private $water = [];        // 图片水印设置
    private $waterImg = null;   // 本地保存的水印图片

    public function __construct($file = null)
    {
        if ($file)
        {
            $this->file = $file;
        }

        $this->disk = config('admin.upload.disk');
    }


    /**
     * 单图上传
     * @return $this
     */
    public function send($file = null)
    {
        if ($file)
        {
            if (strlen($file) < 255) {
                if (is_file($file)) {
                    $this->file = $file; // 本地文件，文件地址
                } else {
                    $this->file = request()->file($file); // 上传的文件名
                }
            } else {
                $this->file = $file;
            }
        } else {
            // 没有文件的时候获取文件
            $file = request()->file();
            // 获取当前上传文件
            $this->file = array_pop($file);
        }
        $this->getFile()->check()->up();

        return $this->getFileInfo();
    }

    /**
     * 多图上传
     * @return array
     */
    public function mulitSend($files = null)
    {
        if (!$files) {
            $files = request()->file();
        }

        $files = $this->intergration($files); // 重置文件维度；防止多维数组问题

        foreach ($files as $file)
        {
            $this->file = $file;
            $this->getFile()->check();
        }
        $data = [];
        foreach ($files as $key => $file)
        {
            $this->file = $file;
            $data[$key] = $this->getFile()->up()->getFileInfo();
        }
        return $data;
    }

    public function getUrl()
    {
        switch ($this->disk) {
            case 'oss':
                $url = (config('filesystems.disks.oss.ssl') ? 'https://' : 'http://') .
                    config('filesystems.disks.oss.bucket') . '.' .
                    config('filesystems.disks.oss.endpoint').'/';
                break;
            case 'qiniu':
                $url = config('filesystems.disks.qiniu.url') . '/';
                break;
            default:
                $url = config('app.qiniu') . '/';
        }
        return $url;
    }

    /**
     * @param string $disk
     * @return $this
     */
    public function setDisk(string $disk)
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param string $saveKey2Val
     * @return $this
     */
    public function setSaveKey2Val(bool $key2val)
    {
        $this->saveKey2Val = $key2val;
        return $this;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename(string $filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @param int $max
     * @return $this
     */
    public function setMax(int $max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @param array $exts
     * @return $this
     */
    public function setExts(array $exts)
    {
        $this->exts = $exts;
        return $this;
    }

    /**
     * 解析文件信息
     * @return $this
     */
    private function getFile()
    {
        if (gettype($this->file) === 'string')
        {
            if (strlen($this->file) < 255) {
                // D:\www\php\laravel\app_renwuwu\public\img\zmr.logo.jpg  本地文件
                $this->type = 'local_file';
            } else {
                // base64 文件流   VUE常用base64上传文件
                $this->type = 'base64';
            }
        }
        else
        {
            // 上传的文件对象
            $this->type = 'file';
        }

        $this->getExt()->getSize();
        return $this;
    }

    /**
     * 上传完成取回数据
     * @return array
     */
    private function getFileInfo()
    {
        return ['ext' => $this->ext, 'size' => $this->size, 'url' => $this->fileUrl];
    }

    /**
     * 验证 文件大小和文件类型
     * @return $this
     */
    private function check()
    {
        $this->type === 'file' && $this->file->isValid();

        if ($this->size > $this->max)
        {
            $this->error('文件大小超过限制，请上传小于' . ($this->max / 1024 / 1024) . 'M的文件');
        }
        if ( !in_array(strtolower($this->ext), $this->exts) && !in_array(strtoupper($this->ext), $this->exts) )
        {
            $this->error('文件格式不正确，仅支持' . implode(', ', $this->exts));
        }
        return $this;
    }

    /**
     * 获取文件大小
     * @return $this
     */
    private function getSize()
    {
        switch ($this->type) {
            case 'file':
                $this->size = $this->file->getSize();
                break;
            case 'base64':
                $this->size = strlen(base64_decode($this->file)); // 还原图片；获取真实图片尺寸
                break;
            default:
                // 'local_file'
                $this->size = strlen($this->file);
                break;
        }
        return $this;
    }

    /**
     * 获取文件后缀名
     * @return $this
     */
    private function getExt()
    {
        switch ($this->type) {
            case 'file':
                $this->ext = $this->file->extension();
                break;
            case 'base64':
                $this->ext = $this->base64Ext();
                break;
            default:
                // 'local_file'
                $this->ext = $this->localFileExt();
                break;
        }
        $this->ext = strtolower($this->ext);
        return $this;
    }

    /**
     * base64流获取后缀名并将文件内容赋给$this->file
     * @return mixed
     */
    private function base64Ext()
    {
        $array = explode(',', $this->file);

        if ( !is_array($array) || count($array) != 2 || !strpos($array[0], ';base64') )
        {
            $this->error('文件异常，不是正常base64');
        }
        $this->file = $array[1];

        $ext = explode(';', explode('/', $array[0])[1])[0];
        return $ext;
    }
    /**
     * 本地文件获取后缀名并将文件内容赋给$this->file
     * @return mixed
     */
    private function localFileExt()
    {
        /*list($fW, $fH, $fT, $fA, $fB, $fM) = [
            0 => 160,
            1 => 160,
            2 => 3,
            3 => 'width="160" height="160"',
            'bits' => 8,
            'mime' => 'image/png'
        ];*/
        // 索引 0 包含图像宽度的像素值，
        // 索引 1 包含图像高度的像素值，
        // 索引 2 是图像类型的标记：1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM
        // 索引 3 图像属性，
        list($fW, $fH, $fT, $fA, $fB, $fM) = getimagesize($this->file);

        // $this->filename = $this->filename ? : basename($this->file);// 设置保存文件名称，不能用，多图上传会覆盖旧图
        $this->file = @file_get_contents($this->file);

        $types = [ 1 => 'gif', 2 => 'jpg', 3 => 'png', 4 => 'swf', 5 => 'psd', 6 => 'bmp', ];
        return $types[$fT];
    }
    /**
     * base64流获取文件内容部分
     * @return mixed
     */
    /*private function getBase64File()
    {
        if ($this->)
        // base64 只有后面部分是正文内容
        $file_arr = explode(',', $this->file);
        // 不是 base64 返回空文件名
        if (!strpos($this->file, ';base64,') || !is_array($file_arr) || count($file_arr) < 2 || strpos($file_arr[0], ';base64') === false) {
            return ['status' => 0, 'msg' => '文件格式异常'];
        }
        $this->file = $file_arr[1];
    }*/

    /**
     * @param $files
     * @return array
     */
    private function intergration($files)
    {
        $data = [];
        foreach ($files as $key => $value)
        {
            if (is_array($value))
            {
                if ($this->saveKey2Val) {
                    foreach ($value as $k=>$v) {
                        $data[$key.'__'.$k] = $v;
                    }
                } else {
                    $data = array_collapse([$data, $value]);
                }
            }
            else
            {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * 文件上传操作
     * @return $this
     */
    private function up($local = false)
    {
        $path = config('app.name') . '/';

        if ($this->water && !$local) {
            // 添加水印需先把图片水印加好了再上传oss
            $this->waterLocalImg();
        }
        switch ($this->type) {
            case 'file':
                $path .= $this->path ?: 'common/'. date('Ymd');
                Storage::disk($this->disk)->makeDirectory($path);
                if ($this->filename) {
                    $filename = Storage::disk($this->disk)->putFileAs($path, $this->file, $this->filename);
                } else {
                    $filename = Storage::disk($this->disk)->putFile($path, $this->file);
                }
                break;
            case 'base64':
                if ($this->filename) {
                    $filename = $this->filename;
                } else {
                    $filename = date('His') . \App\Libraries\Facades\Tools::RandCode(15) . '.' . $this->ext;
                }

                $path .= $this->path ?: 'common/base64';
                $filename = $path . date('/Ymd/') . $filename;

                // $this->getBase64File();
                if (!Storage::disk($this->disk)->put($filename, base64_decode($this->file)))
                {
                    $this->error('未知错误');
                }
                break;
            default:
                // 'local_file'
                if ($this->filename) {
                    $filename = $this->filename;
                } else {
                    $filename = date('His') . \App\Libraries\Facades\Tools::RandCode(15) . '.' . $this->ext;;
                }
                $path .= $this->path ?: 'common/local';
                $filename = $path . date('/Ymd/') . $filename;

                if (!Storage::disk($this->disk)->put($filename, $this->file))
                {
                    $this->error('未知错误');
                }
                break;
        }
        if ($this->water && !$local) {
            // 删除本地的水印图片
            @unlink($this->waterImg);
        }

        $this->fileUrl = Storage::disk($this->disk)->url($filename);
        return $this;
    }

    public function qrcode($path, $file)
    {
        if (is_file($file))
        {
            $file = file_get_contents($file);
        }
        return Storage::disk($this->disk)->put($path, $file);
    }

    /**
     * 图片水印配置
     * @param array $conf
     * @param string $origin_img 有 $origin_img 表示需要返回配置；没有表示直接返回当前对象
     */
    public function setWater(array $conf, string $origin_img = null)
    {
        if(!isset($conf['waterImg']) && !isset($conf['waterText'])){
            $this->error('水印图片和文字均未设置！');
        }

        if (isset($conf['waterText'])) {
            $srcInfo = @getimagesize($origin_img);
            $srcImg_w    = $srcInfo[0];
            $srcImg_h    = $srcInfo[1];
            // 水印字体大小
            $conf['font']['size'] = isset($conf['font']['size']) ? $conf['font']['size'] : intval($srcImg_w * 7 / 100);
            // 水印字体文件
            $conf['font']['path'] = isset($conf['font']['path']) && is_file($conf['font']['path']) ? $conf['font']['path'] : resource_path('ttf/vista.ttf');
            // 单个字符的宽度
            $conf['font']['width'] = $conf['font']['size'] * strlen($conf['waterText']) / 3 / 2;
            // 水印字体颜色
            $conf['font']['color'] = isset($conf['font']['color']) ? $conf['font']['color'] : '#669999';
            // 水印文字水平位置
            $conf['font']['align'] = isset($conf['font']['align']) ? $conf['font']['align'] : 'center';
            // 水印文字垂直位置
            $conf['font']['valign'] = isset($conf['font']['valign']) ? $conf['font']['valign'] : 'left';
            // 水印文字旋转角度
            $conf['font']['angle'] = isset($conf['font']['angle']) ? $conf['font']['angle'] : 45;
            if ($origin_img) {
                if (!isset($conf['x']) || !isset($conf['y'])) {
                    $imgH = isset($srcInfo['height']) ? $srcInfo['height'] : 0;
                    if ($imgH < 300) {
                        if ($imgH < 150) {
                            // 图片 < 150px 只添加一个水印
                            $conf['x'][] = intval($srcImg_w / 2 - $conf['font']['width']);
                            $conf['y'][] = intval($srcImg_h * 0.7);
                        } else {
                            // 图片 150px < 300px 添加两个水印
                            $conf['x'][] = intval($srcImg_w / 2 - $conf['font']['width']);
                            $conf['x'][] = intval($srcImg_w / 4 * 3 - $conf['font']['width']);
                            $conf['y'][] = intval($srcImg_h * 0.7);
                            $conf['y'][] = intval($srcImg_h * 0.9);
                        }
                    } else {
                        // 图片 > 300px 添加3个水印
                        $conf['x'][] = intval($srcImg_w / 4 - $conf['font']['width']);
                        $conf['x'][] = intval($srcImg_w / 2 - $conf['font']['width']);
                        $conf['x'][] = intval($srcImg_w / 4 * 3 - $conf['font']['width']);
                        $conf['y'][] = intval($srcImg_h * 0.2);
                        $conf['y'][] = intval($srcImg_h * 0.7);
                        $conf['y'][] = intval($srcImg_h * 0.9);
                    }
                }
            }
        } else {
            // 水印图片配置
            // 水印图片位置
            $conf['img']['position'] = isset($conf['img']['position']) ? $conf['img']['position'] : 'top-right';
            if ($origin_img) {
                if (!isset($conf['x']) || !isset($conf['y'])) {
                    $srcInfo = @getimagesize($origin_img);
                    $srcImg_w    = $srcInfo[0];
                    $srcImg_h    = $srcInfo[1];

                    $srcInfo2 = @getimagesize($conf['waterImg']);
                    $srcImg_w2    = $srcInfo2[0];
                    $srcImg_h2    = $srcInfo2[1];

                    $conf['x'] = intval(($srcImg_w - $srcImg_w2 * 2) / 2);
                    $conf['y'] = intval(($srcImg_h - $srcImg_h2 * 2) / 2);
                }
            }
        }

        if ($origin_img) {
            // 直接返回配置信息
            return $conf;
        }

        $this->water = $conf;
        return $this;
    }

    /**
     * 添加水印
     * @param string $originImg 源图片地址（待添加水印的图片）
     * @param array $conf
     * $conf 详情 ===================================
     * $conf[saveImg]:添加水印后保存的文件名 没有就直接覆盖源文件
     * $conf[waterImg]:水印图片
     * $conf[waterText]:水印文字
     * $conf[font]:字体详情
     * $conf[font][path]:字体文件路径
     * $conf 详情 ===================================
     * 注:
     * $conf[waterImg] 与 $conf[waterText] 不同时存在，即可判断是采用文字还是图片水印
     *
     * Image::make(Input::file('photo'))->resize(300, 200)->save('foo.jpg'); 直接上传时候添加水印
     *
     */
    public function addWater($originImg, $conf = []) {
        if (!$conf) {
            $conf = $this->water;
        }
        $conf = $this->setWater($conf, $originImg);

        if (isset($conf['waterText'])) {
            // 文字水印
            if (!$conf['waterText']) {
                $this->error('水印文字未设置！');
            }

            $fontInfo = $conf['font'];
            // Intervention\Image\ImageManagerStatic
            $imgMa = Image::make($originImg);

            if (is_array($conf['x'])) {
                foreach ($conf['x'] as $k=>$v) {
                    $imgMa = $imgMa->text($conf['waterText'], $conf['x'][$k], $conf['y'][$k], function ($font) use ($fontInfo){
                        $font->file($fontInfo['path']);
                        $font->size($fontInfo['size']);
                        $font->color($fontInfo['color']);
                        $font->align($fontInfo['align']);
                        $font->valign($fontInfo['valign']);
                        $font->angle($fontInfo['angle']);
                    });
                }
            } else {
                $imgMa = $imgMa->text($conf['waterText'], $conf['x'], $conf['y'], function ($font) use ($fontInfo){
                    $font->file($fontInfo['path']);
                    $font->size($fontInfo['size']);
                    $font->color($fontInfo['color']);
                    $font->align($fontInfo['align']);
                    $font->valign($fontInfo['valign']);
                    $font->angle($fontInfo['angle']);
                });
            }
        } else {
            // 图片水印
            if (!$conf['waterImg']) {
                $this->error('水印图片未设置！');
            }
            // Intervention\Image\ImageManagerStatic
            $imgMa = Image::make($originImg)
                ->insert( $conf['waterImg'],
                          $conf['position'],
                          intval(isset($conf['x']) ? $conf['x'] : 0),
                          intval(isset($conf['y']) ? $conf['y'] : 0)
                );
        }

        if(!isset($conf['saveImg']) || !$conf['saveImg']){
            $conf['saveImg'] = $originImg;
            // $num = strrpos($originImg, '.');
            // $conf['saveImg'] = substr($originImg, 0, $num).'_water'.substr($originImg, $num);
        }
        if ($imgMa->save($conf['saveImg']) ){
            return $conf['saveImg'];
        } else {
            $this->error('保存失败！');
        }
    }
    /**
     * 图片需要添加水印
     * 先保存本地；再替换 $this->file
     */
    private function waterLocalImg()
    {
        // 保存网盘配置
        $end_disk = $this->disk;
        $this->disk = 'local';
        // 上传图片到本地
        $this->up(true);
        // 添加水印，并获得添加水印后的图片
        $this->file = $this->waterImg = $this->addWater($this->fileUrl);
        // 水印添加完成后恢复 disk 配置
        $this->disk = $end_disk;
        // 获取当前上传图片的文件信息
        $this->getFile();
    }

    /**
     * ERROR
     * @param  string $error 错误提示信息
     * @param  int $code 错误码
     */
    private function error($error, $code = 2001)
    {
        throw new UploadException($error, $code);
    }
}