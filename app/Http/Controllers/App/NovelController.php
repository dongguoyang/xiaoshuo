<?php
namespace App\Http\Controllers\App;

use App\Http\Controllers\BaseController;
use App\Logics\Services\src\NovelService;

class NovelController extends BaseController
{
    public function __construct(NovelService $service)
    {
        $this->service = $service;
    }
    /**
     * 跳转对应客户的小说首页
     */
    public function ToIndex() {
        try
        {
            return $this->service->ToIndex();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 推广链接跳转
     */
    public function ExtentLink($id) {
        try
        {
            return $this->service->ExtentLink($id);
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 推广落地页
     */
    public function ExtentPage() {
        try
        {
            return $this->service->ExtentPage();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 跳转对应客户的小说首页
     * route("novel.tosection", ['novel_id'=>$novel_id, 'section_id'=>$section_id], false);
     */
    public function ToSection() {
        try
        {
            return $this->service->ToSection();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 小说分类获取
     */
    public function NovelTypes() {
        try
        {
            return $this->service->NovelTypes();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 小说首页数据
     */
    public function IndexData() {
        try
        {
            return $this->service->IndexData();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 小说首页数据-更多
     */
    public function IndexDataMore() {
        try
        {
            return $this->service->IndexDataMore();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 小说信息获取
     */
    public function NovelInfo() {
        try
        {
            return $this->service->NovelInfo();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 小说章节信息获取
     */
    public function NovelSection() {
        try
        {
            return $this->service->NovelSection();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 小说章节信息获取；没有正文内容
     */
    public function SectionInfo() {
        try
        {
            return $this->service->SectionInfo();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 小说章节列表获取
     */
    public function SectionList() {
        try
        {
            return $this->service->SectionList();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 小说查询
     */
    public function SearchNovel() {
        try
        {
            return $this->service->SearchNovel();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 小说书架图书
     */
    public function BookStore() {
        try
        {
            return $this->service->BookStore();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 小说书架图书删除
     */
    public function DelBookStore() {
        try
        {
            return $this->service->DelBookStore();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 小说阅读记录
     */
    public function ReadList() {
        try
        {
            return $this->service->ReadList();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 删除小说阅读记录
     */
    public function DelReadLog() {
        try
        {
            return $this->service->DelReadLog();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 查询余额是否足够下一章阅读
     */
    public function BalanceEnough() {
        try
        {
            return $this->service->BalanceEnough();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 章节阅读页面的推荐小说
     */
    public function SectionRecommend() {
        try
        {
            return $this->service->SectionRecommend();
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 小说外放接口
     */
    public function GetNovel(){
        try{
            return $this->service->GetNovel();
        }catch (\Exception $e){
            return $this->result([],$e->getCode(),$e->getMessage());
        }
    }
    /**
     *
     * 小说信息获取接口
     */
    public function NovelSyncApi(){
        try{
            return $this->service->NovelSyncApi();
        }catch (\Exception $e){
            return $this->result([],$e->getCode(),$e->getMessage());
        }
    }
    /**
     *
     * 小说章节内容获取
     */
    public function NovelSectionContent(){
        try{
            return $this->service->NovelSectionContent();
        }catch (\Exception $e){
            return $this->result([],$e->getCode(),$e->getMessage());
        }
    }
}