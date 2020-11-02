<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Ad;
use App\Admin\Models\AdPosition;
use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Traits\OfficialAccountTrait;
use App\Logics\Traits\PushmsgTrait;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class OtherDoController extends AdminController
{
    use OfficialAccountTrait;
    /**
     * 指定用户的客服消息推送
     */
    public function customerMsg(Content $content)
    {
        $ids = request()->input('ids');
        $ids = ExplodeStr($ids);
        if (!count($ids)) die('没有用户ID');
        $domainRep = new DomainRepository();
        $userRep = new UserRepository();
        foreach ($ids as $user_id) {
            $user = $userRep->UserCacheInfo($user_id);
            $customer_id = $user['view_cid'] > 0 ? $user['view_cid'] : $user['customer_id'];
            if ($customer_id == $user['customer_id']) {
                $openid = $user['openid'];
            } else {
                $openid = $userRep->GerRealOpenid($user['first_account'], $customer_id);
            }
            $url = $domainRep->randOne(2, $customer_id) . route('jumpto', ['cid'=>$customer_id, 'customer_id'=>$customer_id, 'dtype'=>2, 'route'=>'center'], false);

            $text = "您充值的书币已到账\r\n\r\n由于系统原因，导致延迟到账，给您带来的不便，请谅解，我们将竭诚为您服务\r\n\r\n<a href='{$url}'点我查看书币</a>";

            $content = [
                'touser'    => $openid,
                'msgtype'   => 'text',
                'text'      => [
                    'content'   => $text,
                ],
            ];
            $this->SendCustomMsg($customer_id, $content, true);
        }
    }
}
