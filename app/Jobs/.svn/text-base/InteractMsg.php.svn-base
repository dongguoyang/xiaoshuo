<?php
/**
 * #延时任务 QUEUE_CONNECTION 需配置；不能采用sync（sync 是同步执行，没有延时功能）
 *
 * 互动消息任务
 * @param array $msgUsers 最近有互动的用户列表
 * @param array $msgContents 待发送的互动消息
 */

namespace App\Jobs;

use App\Logics\Repositories\src\InteractMsgRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Traits\OfficialAccountTrait;
use App\Logics\Traits\WechatTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InteractMsg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, OfficialAccountTrait;

    protected $platWechat;  // 需要互动的公众号
    // protected $msgUsers; // 需要发送互动消息的用户列表
    protected $msgContents; // 需要发送互动消息的内容
    protected $testUser;    // 测试消息用户ID

    protected $interactMsgs;
    protected $users;
    /**
     * Create a new job instance.
     *
     * @param array $msgUsers 最近有互动的用户列表
     * @param array $msgContents 待发送的互动消息
     * @return void
     */
    public function __construct($platWechat, /*$msgUsers,*/ $msgContents, $testUser = null)
    {
        //
        $this->platWechat     = $platWechat;
        //$this->msgUsers     = $msgUsers;
        $this->msgContents  = $msgContents;
        $this->testUser     = intval($testUser);
        $this->users        = new UserRepository();
        $this->interactMsgs = new InteractMsgRepository();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $customer_id = $this->platWechat['customer_id'];
        $content = $this->interactMsgContent();
        if ($this->msgContents['send_to'] != 1) {
            $send_type = json_decode($this->msgContents['send_to'], 1);
        }
        if ($this->testUser) {
            $user = $this->users->find($this->testUser, ['id', 'openid']);
            $msgUsers[$user['id']] = [$user['openid'], (time()+86400)];
        } else {
            $msgUsers = $this->users->NoteInteractInfo($this->platWechat);
        }

        $note = ['succ'=>0, 'fail'=>0, 'need'=>0];
        if ($msgUsers) {
            foreach ($msgUsers as $user_id => $user) {
                if ($user[1] < time()) continue; // 过期了跳过
                if ($this->msgContents['send_to'] != 1) {
                    $user = $this->users->UserCacheInfo($user_id);
                    if ($send_type['sex'] && $send_type['sex'] != user['sex']) continue;
                    if ($send_type['recharge']) {
                        if ($send_type['recharge'] == 1 && !$user['recharge_money']) continue; //1已充值;未充值用户跳过
                        elseif($send_type['recharge'] == 2 && $user['recharge_money']>0) continue;//2已充值;已充值用户跳过
                    }
                }

                $note['need']++;
                if ($this->attempts() > 3) {
                    Log::info("user_id=".$user_id.'; openid='.$user['0'].'; 互动消息 attempts > 3 后发送失败！');
                } else {
                    $content['touser'] = $user[0]; // 赋值openid
                    try {
                        $this->SendCustomMsg($customer_id, $content, ($this->testUser ? false : true));
                        $note['succ']++;
                    } catch (\Exception $e) {
                        $note['fail']++;
                        Log::info("user_id=".$user_id.'; openid='.$user['0'].'; 互动消息发送失败！'."\n" . $e->getMessage());
                    }
                }
            }
        }
        if ($note['need'] == 0 || ($note['need'] > 0 && $note['succ'] / $note['need'] >= 0.5)) {
            // 设置任务发送成功
            if (isset($this->msgContents['id']) && $this->msgContents['id']) {
                $this->interactMsgs->update(['status' => 1], $this->msgContents['id']);
            }
        } else {
            // 设置任务发送失败
            if (isset($this->msgContents['id']) && $this->msgContents['id']) {
                $this->interactMsgs->update(['status' => -1], $this->msgContents['id']);
            }
        }

    }

    private function interactMsgContent() {
        if ($this->msgContents['type'] == 2) {
            // 发送文本消息
            /*
             {
                "touser":"OPENID",
                "msgtype":"text",
                "text":
                {
                     "content":"Hello World"
                }
            }
            */
            $content = [
                'touser'    => null,
                'msgtype'   => 'text',
                'text'      => [
                    'content'   => $this->msgContents['content'],
                ],
            ];
        } else {
            /*
            {
                "touser":"OPENID",
                "msgtype":"news",
                "news":{
                    "articles": [
                     {
                         "title":"Happy Day",
                         "description":"Is Really A Happy Day",
                         "url":"URL",
                         "picurl":"PIC_URL"
                     }
                     ]
                }
            }
            */
            $msgcontent = json_decode($this->msgContents['content'], 1);
            $content = [
                'touser'    => null,
                'msgtype'   => 'news',
                'news'      => [
                    'articles'  =>  [
                        [
                            'title'         => $msgcontent['title'],
                            'description'   => $msgcontent['description'],
                            'url'           => $msgcontent['url'],
                            'picurl'        => $msgcontent['image'],
                        ],
                    ],
                ],
            ];
        }

        return $content;
    }
    /**
     * 处理一个失败的任务
     *
     * @return void
     */
    public function failed()
    {
        Log::error("InteractMsg 队列任务执行失败！\n" . date('Y-m-d H:i:s'));
    }
}
