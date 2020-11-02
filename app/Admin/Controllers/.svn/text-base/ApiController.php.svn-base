<?php
namespace App\Admin\Controllers;

use App\Logics\Repositories\src\NovelSectionRepository;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Admin\Models\User;
use App\Admin\Models\Customer;
use App\Admin\Models\ExtendLink;
use App\Admin\Models\PlatformWechat;
use App\Admin\Models\Author;
use App\Admin\Models\Type;
use App\Admin\Models\Novel;
use App\Admin\Models\NovelSection;
use App\Admin\Models\Prize;
use App\Admin\Models\Goods;
use App\Admin\Models\Coupon;
use App\Admin\Models\Material;
use App\Logics\Traits\ApiResponseTrait;

class ApiController extends AdminController
{
    use ApiResponseTrait;
    public function users(Request $request) {
        $q = $request->get('q');
        return User::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }

    public function customers(Request $request) {
        $q = $request->get('q');
        return Customer::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }

    public function extendLinks(Request $request) {
        $q = $request->get('q');
        return ExtendLink::where('title', 'like', "%$q%")->paginate(null, ['id', 'title as text']);
    }

    public function platformWechats(Request $request) {
        $q = $request->get('q');
        return PlatformWechat::where('app_name', 'like', "%$q%")->paginate(null, ['id', 'app_name as text']);
    }

    public function authors(Request $request) {
        $q = $request->get('q');
        return Author::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }

    public function types(Request $request) {
        $q = $request->get('q');
        return Type::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }

    public function novels(Request $request) {
        $q = $request->get('q');
        return Novel::where('title', 'like', "%$q%")->paginate(null, ['id', 'title as text']);
    }

    public function prizes(Request $request) {
        $q = $request->get('q');
        return Prize::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }

    public function goods(Request $request) {
        $q = $request->get('q');
        return Goods::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }

    public function novelSections(Request $request) {
        $q = $request->get('q');
        return NovelSection::where('title', 'like', "%$q%")->paginate(null, ['id', 'title as text']);
    }

    public function novelSectionsRel(Request $request) {
        $q = $request->get('q');
        return NovelSection::where('novel_id', '=', $q)->get(['id', DB::raw('title as text')]);
    }

    public function coupons(Request $request) {
        $q = $request->get('q');
        return Coupon::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }

    public function imageTitles(Request $request) {
        $type = $request->input('type', 1);
        return optional(Material::where([
            ['type', '=', $type],
            ['status', '=', 1]
        ])->get())->toArray();
    }

    public function getChapters() {
        $params = request()->input();
        if(isset($params['chapter_id']) && $params['chapter_id']) {
            $current_chapter = NovelSection::find($params['chapter_id']);
            $current_chapter = $current_chapter ? $current_chapter->toArray() : [];
            $novelSectionRep = new NovelSectionRepository();
            $_chapters = $novelSectionRep->ExtendLinkSections($current_chapter['novel_id'], $current_chapter['num']);
            $chapters = [];
            foreach($_chapters as $chapter) {
                $content = $this->resetContentGeShi($chapter['content']);
                $chapters[] = [
                    'id'    =>  $params['chapter_id'],
                    'idx'   =>  $chapter['num'],
                    'title' =>  '第'.$chapter['num'].'章·'.$chapter['title'],
                    'paragraphs'    =>  [
                        $content
                    ]
                ];
            }
            return $chapters;
        } else {
            return [];
        }
    }
    // 使标签闭合
    private function resetContentGeShi($content) {
        $content = trim($content);
        if (substr($content, 0, 4) != '<div' && substr($content, -6) == '</div>') {
            $content = '<div>' . $content;
        }
        if (substr($content, 0, 2) != '<p' && substr($content, -4) == '</p>') {
            $content = '<p>' . $content;
        }

        return $content;
    }

    public function getChapter() {
        $params = request()->input();
        if(isset($params['chapter_id']) && $params['chapter_id']) {
            $current_chapter = NovelSection::find($params['chapter_id']);
            $current_chapter = $current_chapter ? $current_chapter->toArray() : [];
            $current_chapter['content'] = $current_chapter['content'] ? file_get_contents($current_chapter['content']) : '';
            return $current_chapter;
        } else {
            return [];
        }
    }

    public function getChapterByIndex() {
        $params = request()->input();
        $chapter_id = $params['chapter_id'] ?? 0;
        $novel_id = $params['nid'] ?? 0;
        $index = $params['idx'] ?? 0;
        if($novel_id < 1 || $index < 1 || $chapter_id < 1) {
            return $this->result(null, -20001, '找不到指定小说');
        }
        $chapter = NovelSection::find($chapter_id);
        if (!$chapter) {
            return $this->result(null, -20001, '找不到指定章节');
        }
        $chapter = $chapter->toArray();
        if($chapter['novel_id'] != $novel_id) {
            return $this->result(null, -20001, '数据不匹配');
        }
        $novel = Novel::find($novel_id);
        if (!$novel) {
            return $this->result(null, -20001, '找不到指定小说');
        }
        $novel = $novel->toArray();
        $chapter_to_subscribe = NovelSection::where([
            ['novel_id', '=', $novel_id],
            ['num', '=', $index]
        ])
            ->orderBy('num', 'asc')
            ->orderBy('id', 'desc')
            ->first();
        if (!$chapter_to_subscribe) {
            return $this->result(null, -20001, '找不到指定章节');
        }
        $chapter_to_subscribe = $chapter_to_subscribe->toArray();
        return $this->result([
            'id'    =>  $chapter_id,
            'idx'   =>  $index,
            'title' =>  '第'.$chapter_to_subscribe['num'].'章·'.$chapter_to_subscribe['title'],
            'welth' =>  $novel['need_buy_section'] <= $chapter_to_subscribe['num'] ? 1 : 0
        ], 0, 'OK');
    }
}