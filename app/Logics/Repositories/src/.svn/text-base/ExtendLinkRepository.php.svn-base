<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\ExtendLink;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class ExtendLinkRepository extends Repository {
	public function model() {
		return ExtendLink::class;
	}

	public $validTime = 1728000;// 20 * 86400; // 20天有效期

	/**
     * 获取推广链接信息
     * @param int $id
     * @param array $update_cache
     */
	public function ExtendInfo($id, $update_cache = false) {
	    $key = config('app.name').'extend_link_'.$id;
	    if ($update_cache) {
	        Cache::forget($key);
        }

	    return Cache::remember($key, 1440, function () use ($id){
	        $info = $this->find($id, ['id', 'customer_id', 'novel_id', 'novel_section_num', 'title', 'link', 'type', 'must_subscribe', 'subscribe_section', 'status', 'updated_at']);
	        $info = $this->toArr($info);
            /*$info['page_conf'] = json_decode($info['page_conf'], 1);
            $info['data_info'] = json_decode($info['data_info'], 1);*/

            return $info;
        });
    }

    /**
     * 更新推广链接产生的收益数据
     * @param int $id
     * @param array $update_data
     */
    public function UpdateInfo($id, $update_data) {
        $data = $this->find($id, ['data_info', 'updated_at', 'status']);
        if (!$data || !$data['status'] || ($data['updated_at'] + $this->validTime) < time()) {
            return false;
        }

        $old_data = json_decode($data['data_info'], 1);
        $date = date('Y-m-d');
        foreach ($update_data as $k=>$v) {
            if (!isset($old_data[$date])) {
                $old_data[$date] = [
                    'recharge'      => 0, // 充值笔数
                    'recharge_succ' => 0, // 充值成功笔数
                    'money'         => 0, // 充值金额
                    'user'          => 0, //阅读人数
                    'subscribe'     => 0, //关注人数
                ];
            }
            $old_data[$date][$k] += $v;
        }
        $old_data = json_encode($old_data);
        $this->update(['data_info'=>$old_data], $id);
    }
    /**
     * 保存或者添加推广链接增加的用户
     * @param int $first_account    主用户ID
     * @param int $sub_userid       子用户ID
     * @param int $created_at       子用户注册时间
     */
    public function ExtendSubUser($first_account, $sub_userid = null, $created_at = 0) {
        $key = config('app.name') . 'extend_sub_user_' . $first_account;
        if ($sub_userid === null) {
            return Cache::get($key);
        }

        $ttl = 7 * 86400;
        if ($created_at) {
            $ttl = $created_at + $ttl - time();
            if ($ttl <= 0) {
                return false;
            }
        }
        if (Cache::has($key)) {
            return Cache::put($key, $sub_userid, $ttl);
        } else {
            return Cache::add($key, $sub_userid, $ttl);
        }
    }



    public function ExtendPageInfos() {
        $titles = [
            '去老师办公室的他，却被眼前的一幕羞红了脸！',
            '单身女子在外租房一定要小心这样的房东，一不小心就......',
            '村卫生所只有我一个妇科医生，让我享尽快乐！',
            '身为按摩师的他，这个手法让美女爱不释手！',
            '白天优雅又知性的姐姐,到了晚上...',
            '我去医院看病，却没想到女医生会要求我做......',
            '清晨醒来，床上多了个绝世美女......',
            '小伙子赖在寝室不想走，原来是美女天天给他这般照顾！',
            '大半夜没想到隔壁妹子来找我看这种让人羞红了脸的病......',
            '男子为了满足直播的粉丝，摄影机对准了自己和隔壁的美女！',
            '女子和隔壁单身汉同居，半夜发现居然偷偷钻进了......',
            '小伙子回到家乡，在偏僻处对清新美女露出了......',
            '小河边美女传来哗哗水声，偷偷一看，可把老胡急坏了！',
            '美女面试官在面试时做的事情，让人脸红......',
            '猥琐小伙在公交车上对美女做了这种事情，让美女又爱又恨！',
            '隔壁房间传来喘息，单身小伙偷偷打开门看到......',
            '一个备胎男的自白……',
            '我的深情表白，她却视而不见……',
            '一个决定错了，一切就开始脱轨了……',
            '为了得到她，我耍起了手段……',
            '劫后余生之后就是醉卧美人膝……',
            '本以为是穷乡僻壤，却是世外桃源……',
            '为了让我帮她隐瞒，竟然提出这样的条件……',
            '一次机缘巧合，我发现了她的秘密……',
            '女人美不美，看大腿就知道……',
            '老司机的车技，她试过就……',
            '主要有技术，老树也能又逢春',
            '捡到一个美女回家，各种诡异之事开启……',
            '隔壁传来的声音，让我无法入睡',
            '一场空难，我与她们流落无人荒岛……',
            '女人都有一个共同的弱点……',
            '莫欺少年穷，一旦逆袭……',
            '一不小心，捡到一个惊天大便宜……',
            '美女晕倒了，我只能……',
            '本来发给女友的短信，发给了女上司',
            '开启撩妹模式，直男快来学学……',
            '我在女性内衣公司上班……',
            '在酒吧工作久了，什么画面都能看到……',
            '她一弯腰，我看到了……',
            '史上最幸福的上门女婿，缺人速来……',
            '天赋异禀，惹来无数男人嫉妒',
            '本该享受晚年时，焕发了第二春',
            '有些东西，只能傻的时候能看到……',
            '父母出国工作，邻居小姐姐照顾我……',
            '没想到当保安，居然还要满足她的特殊……',
            '做女上司的贴身级助理，还得……',
            '我半夜出门，发现她在树林里……',
            '懵逼！我相亲的对象竟然是她……',
            '我隔壁的女邻居很漂亮，让人很是……',
            '她穿成这样在我面前，叫我如何把持……',
            '我低调到公司视察，却被美女经理拦住……',
            '再好的感情，也会毁于理所当然的……',
            '夫妻越老越薄情，真相居然是……',
            '她半夜的举动，让我惊醒……',
            '雇佣兵之王勇闯都市，却躲不过她的娇媚……',
            '给美女当司机，想不到有这么多好处……',
            '被困荒岛，我独自和美女们……',
            '她没钱交房租，竟向我提出这个交易方式……',
            '兵王回归都市，身边美女如云……',
            '被她那样使唤，我想反抗但又舍不得那感觉……',
            '高手都是怎么追美女的？套路你绝对想不到……',
            '老婆电话查岗，结果隔壁女同事说了句往那边睡点……',
            '火车上和漂亮妹子同座，车票值了……',
            '上班第一天才发现公司就我一个男人……',
            '隔壁传来的声音，让我无心睡眠',
            '特种兵当小保安，美女艳福不断',
            '她上门来找我，我该不该答应她……',
            '电梯故障，我与高冷女总裁困在了一起……',
            '见面第一眼，我就因为她……',
            '提前回家，居然发现家门口有男人的……',
            '进乡的第一天，我就遇上了这样的事……',
            '挖阴坟，葬阴尸……一个毛骨悚然的民间禁忌',
            '回村第一天，竟然发现儿时女老师正在……',
            '我去上司办公室，发现她坐在桌上……',
            '她带我去跟闺蜜们跳舞，结果她们竟然当着我的面……',
            '做梦都想不到自己的女神，会跟老板在办公室里……',
            '她总是加班，我好奇去找她，结果……',
            '听着她的声音，我好想去……',
            '大晚上逛公园，没想到看到这么刺激的……',
            '我上班忘拿钥匙，回家后傻了眼……',
            '天哪，还没有看过这样的她……',
            '太好看了，看完以后……',
            '我拨通了她的手机，传来的却是一个男人的……',
            '我是上门女婿，新婚夜老婆却不让我……',
            '退伍兵王隐瞒身份入赘豪门遭妻子看不起，显露身份后……',
            '特种兵王退伍当保镖，没想到保护的竟是……',
            '请远离这些特征的人，都很渣',
            '小伙儿路边晕倒，美女护士及时抢救，结局竟然……',
            '相亲美女不按套路出牌，竟然提出了这种要求……',
            '毕业之后我成为一名妇科男医生，上班第一天就遇到美女……',
            '特种兵王退役回家结婚，却惨遭处处嫌弃……',
            '美女遭遇小偷，竟然用这个方法挽回损失……',
            '见我进来，她慌忙把东西藏了起来',
            '她让我过去，要我帮她缓解……',
            '她半夜总发出很大的声音，受不了的我去敲了她的房门结果……',
            '乡村小神医上门出诊，没想到看到这种场面……',
            '夜晚无聊到湖边溜达，邂逅了美丽的她……',
            '工作上毫无进展，我却跟女上司走得越来越近',
            '美女可真有上进心，放假期间还让我去单独教她……',
        ];

        $banners = [
            '/home/extendpage/478f82148e4b.png',
            '/home/extendpage/d9711ab1edcf.png',
            '/home/extendpage/06e730ec0d48.png',
            '/home/extendpage/30cdcab2f2b2.png',
            '/home/extendpage/eaec672db3c9.png',
            '/home/extendpage/f98d1c1563ca.png',
            '/home/extendpage/b171cb9ebf09.png',
            '/home/extendpage/53788660ab5d.png',
            '/home/extendpage/1cdf30d16d0e.png',
            '/home/extendpage/53410b1d3962.png',
            '/home/extendpage/039ca653f748.png',
            '/home/extendpage/0aaec0f987dc.png',
            '/home/extendpage/058e0cfa6b34.png',
            '/home/extendpage/fbfd71fd52ef.png',
            '/home/extendpage/8f8c3287ff03.png',
            '/home/extendpage/a98b1f581f61.png',
            '/home/extendpage/c3d03e0f18a1.png',
            '/home/extendpage/31890d886b67.png',
            '/home/extendpage/ededa7e456d7.png',
            '/home/extendpage/c8d2b1b2178d.png',
            '/home/extendpage/5099ac81b702.png',
            '/home/extendpage/8a15e53b377a.png',
            '/home/extendpage/10c41613e27a.png',
            '/home/extendpage/7b3f6df9060c.png',
            '/home/extendpage/424e92328f23.png',
            '/home/extendpage/52d7dfb08556.png',
            '/home/extendpage/a9dac646ef18.png',
            '/home/extendpage/c773acd8ca72.png',
            '/home/extendpage/3c6ef4791754.png',
            '/home/extendpage/696e61999e1d.jpg',
            '/home/extendpage/d0b76fa87b3c.jpg',
            '/home/extendpage/7468178efbf3.jpg',
            '/home/extendpage/1678e1abb488.jpg',
            '/home/extendpage/65a5ecbbf4ae.jpg',
            '/home/extendpage/ebc83a82d11c.jpg',
            '/home/extendpage/8c664de5f39b.jpg',
            '/home/extendpage/23f9310b0978.jpg',
            '/home/extendpage/27157a2434aa.jpg',
            '/home/extendpage/ad2be0c340ef.jpg',
            '/home/extendpage/35c192ec808c.jpg',
            '/home/extendpage/5a07c5567bb1.jpg',
            '/home/extendpage/aa2326af1f7c.jpg',
            '/home/extendpage/4cd41eb4eac0.jpg',
            '/home/extendpage/28ba7e713646.jpg',
            '/home/extendpage/f2355f757e14.jpg',
            '/home/extendpage/3d1e9a2cdec0.jpg',
            '/home/extendpage/7373301105a5.jpg',
            '/home/extendpage/59ccb48ff19c.jpg',
            '/home/extendpage/cff881595dff.jpg',
            '/home/extendpage/f36dc365399a.jpg',
            '/home/extendpage/e579dbaba685.jpg',
            '/home/extendpage/f3b690d4c838.jpg',
            '/home/extendpage/771dbfee7544.jpg',
            '/home/extendpage/0b68daf88b74.jpg',
            '/home/extendpage/75eb28ac2156.jpg',
            '/home/extendpage/66c9d213a87f.jpg',
            '/home/extendpage/a635dac919cb.jpg',
            '/home/extendpage/f7a965a7df7d.jpg',
            '/home/extendpage/845496fa8485.jpg',
            '/home/extendpage/1031069e462a.jpg',
            '/home/extendpage/d2c9ac74c065.jpg',
            '/home/extendpage/cbcec52f3f4e.jpg',
            '/home/extendpage/abf4c6fffea2.jpg',
            '/home/extendpage/fe9c3fe44dc7.jpg',
            '/home/extendpage/eab00aaa7647.png',
            '/home/extendpage/242a7d324333.jpg',
            '/home/extendpage/acd02e681ec0.png',
            '/home/extendpage/896d3131965c.jpg',
            '/home/extendpage/a794d1afe398.jpg',
            '/home/extendpage/508e2a4c9265.jpg',
            '/home/extendpage/0539881c9757.jpg',
            '/home/extendpage/2caff85b9110.png',
            '/home/extendpage/6ddcee4dd41c.jpg',
            '/home/extendpage/e5c88059a0ac.jpg',
            '/home/extendpage/e49c9390d3d4.png',
            '/home/extendpage/714c2841ac01.jpg',
            '/home/extendpage/5ce27105f7f5.jpg',
            '/home/extendpage/f8d7f91c1557.jpg',
            '/home/extendpage/22f6fad9cb95.jpg',
            '/home/extendpage/a06b226afd48.jpg',
            '/home/extendpage/5628bb7b012f.jpg',
            '/home/extendpage/fb1496f26660.jpg',
            '/home/extendpage/3f71a25da42a.jpg',
            '/home/extendpage/95ba5d3ea245.jpg',
            '/home/extendpage/28f762b02742.jpg',
            '/home/extendpage/c3b46d757742.jpg',
            '/home/extendpage/3c54a4a6641c.jpg',
            '/home/extendpage/92bc17466785.jpg',
            '/home/extendpage/c498fbbd6908.jpg',
            '/home/extendpage/8a878b73bbac.jpg',
            '/home/extendpage/fe2bec1cb43e.jpg',
            '/home/extendpage/66aa953f149a.jpg',
            '/home/extendpage/0f16b66e4b3f.jpg',
            '/home/extendpage/0176362c41b3.jpg',
            '/home/extendpage/620fc3652ba0.jpg',
            '/home/extendpage/f5f26b05a0df.jpg',
            '/home/extendpage/7f967d90ed48.jpg',
            '/home/extendpage/d154999ffb2b.jpg',
            '/home/extendpage/895440fc3e7e.jpg',
            '/home/extendpage/8431e1e41307.jpg',
        ];

        $bodys = [
            '/home/extendpage/body1.jpg',
            '/home/extendpage/body2.jpg',
            '/home/extendpage/body3.jpg',
            '/home/extendpage/body4.jpg',
            '/home/extendpage/body5.jpg',
            '/home/extendpage/body6.jpg',
            '/home/extendpage/body7.jpg',
            '/home/extendpage/body8.jpg',
            '/home/extendpage/body9.jpg',
            '/home/extendpage/body10.jpg',
            '/home/extendpage/body11.jpg',
            '/home/extendpage/body12.jpg',
        ];

        $footers = [
            '/home/extendpage/footer1.gif',
            '/home/extendpage/footer8.gif',
            '/home/extendpage/footer2.gif',
            '/home/extendpage/footer3.gif',
            '/home/extendpage/footer7.gif',
            '/home/extendpage/footer6.gif',
            '/home/extendpage/footer5.gif',
            '/home/extendpage/footer4.gif',
        ];

        $qrcodes = [
            '/home/extendpage/qr-footer8-preview.png',
            '/home/extendpage/qr-footer1-preview.png',
            '/home/extendpage/qr-footer7-preview.png',
            '/home/extendpage/qr-footer6-preview.png',
            '/home/extendpage/qr-footer5-preview.png',
            '/home/extendpage/qr-footer4-preview.png',
            '/home/extendpage/qr-footer3-preview.png',
            '/home/extendpage/qr-footer2-preview.png',
        ];

        return ['titles'=>$titles, 'bodys'=>$bodys, 'banners'=>$banners, 'footers'=>$footers, 'qrcodes'=>$qrcodes];
    }
}