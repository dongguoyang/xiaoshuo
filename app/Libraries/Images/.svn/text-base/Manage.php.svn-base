<?php
/**
 * Created by LWL.
 * User: LUWENLONG
 * Date: 2019/3/14
 * Time: 15:33
 * 存储空间管理器
 */

namespace App\Libraries\Images;


use Illuminate\Support\Facades\Storage;

class Manage
{
    private $disk = 'oss';

    private $path;     // https://renwuwu.oss-cn-beijing.aliyuncs.com/default_img/default-avatar.png

    private $filename; // user/face/1/20190314/1417213666KLc7xo.png

    private $url = '';  // https://renwuwu.oss-cn-beijing.aliyuncs.com/

    public function __construct()
    {

    }

    /**
     * @return $this
     */
    private function setUrl()
    {
        $this->url = (config('filesystems.disks.oss.ssl') ? 'https://' : 'http://') .
            config('filesystems.disks.oss.bucket') . '.' .
            config('filesystems.disks.oss.endpoint').'/';
        return $this;
    }

    /**
     * @return $this
     */
    private function analysis()
    {
        $this->setUrl();
        if (is_array($this->path))
        {
            for ($i = 0; $i < count($this->path); $i++)
            {
                $this->filename[$i] = str_replace($this->url, '', $this->path[$i]);
            }
        }
        else
        {
            $this->filename = str_replace($this->url, '', $this->path);
        }
        return $this;
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
    public function setPath($path)
    {
        $this->path = $path;
        $this->analysis();
        return $this;
    }

    /**
     * 删除文件
     * @param $path
     * @return bool
     */
    public function delete($path)
    {
        $this->setPath($path);
        if ($this->has())
        {
            return Storage::disk($this->disk)->delete($this->filename);
        }
        return false;
    }

    /**
     * 检查文件是否存在
     * @return bool
     */
    public function has()
    {
        if (is_array($this->filename))
        {
            for ($i = 0; $i < count($this->filename); $i++)
            {
                if (!Storage::disk($this->disk)->exists($this->filename[$i]))   //速度慢
                {
                    unset($this->filename[$i]);
                }
            }
            return count($this->filename) > 0;
        }
        else
        {
            return Storage::disk($this->disk)->exists($this->filename);
        }
    }

    /**
     * 检索目录下的所有文件夹及文件 不递归
     * @param $directory
     * @return array
     */
    public function all($directory)
    {
        $directories = Storage::disk($this->disk)->directories($directory);
        $files = Storage::disk($this->disk)->files($directory);

        return ['directories' => $directories, 'files' => $files];
    }

    /**
     * 创建目录
     * @param $directory
     * @return mixed
     */
    public function create($directory)
    {
        return Storage::disk($this->disk)->makeDirectory($directory);
    }

    /**
     * 删除目录下的所有文件夹及所有文件  慎用！！！！
     * @param $directory
     * @return mixed
     */
    public function destory($directory)
    {
        return Storage::disk($this->disk)->deleteDirectory($directory);
    }

    /**
     * 清除空文件夹 慎用！！！
     * @param $directory
     * @return bool
     */
    public function clearEmptyDir($directory)
    {
        $directories = Storage::disk($this->disk)->allDirectories($directory);
        if (count($directories) > 0)
        {
            for ($i = count($directories); $i > 0; $i--)
            {
                $this->clearEmptyDir($directories[$i-1]);
            }
        }
        else
        {
            $files = Storage::disk($this->disk)->files($directory);
            if (count($files) == 0)
            {
                $this->destory($directory);
            }
        }
        return true;
    }
}