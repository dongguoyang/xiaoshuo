<?php
/**
 * Created by PhpStorm.
 * User: Raytine
 * Date: 2020/6/9
 * Time: 18:48
 */
namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use App\Logics\Models\Novel;
use App\Logics\Models\NovelSection;
class DelController extends BaseController{

    public function DelBook(){
        $model = new Novel();
        $section_model = new NovelSection();
        $book = $model->where(['serial_status'=>1,'status'=>2])->get();
        //$book = Db::select('select * from novels where serial_status = 1 and status = 0');
        if($book){
            foreach ($book as $item=>$value){
                //var_dump($value['id']);
                $result  = $section_model->where('novel_id','=',$value['id'])->delete();
                var_dump($result);
            }
        }
    }
}