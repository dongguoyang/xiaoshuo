<?php
namespace App\Logics\Traits;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

trait RedisCacheTrait
{
    /**
     * 设置redis DB
     * @param $db
     * @return bool
     */
    protected function setRedisDb($db)
    {
        return $db ? Redis::select($db) : false;
    }
    /**
     * 设置redis DB 为默认配置的DB
     * @param $db
     * @return bool
     */
    public function setDefaultRedisDb()
    {
        return Redis::select( config('database.redis.default.database') );
    }

    /**
     * redis缓存读写操作
     * @param        $key
     * @param string $val
     * @param int    $exp
     * @param null   $db
     * @return mixed|null
     */
    public function LmCache($key, $val = '', $exp = 86400, $db = null)
    {
        if (empty($key))
        {
            return null;
        }
        $this->setRedisDb($db);
        return $val ? $this->setRedisCache($key, $val, $exp) : $this->getRedisCache($key);
    }

    /**
     * 删除redis缓存
     * @param $key
     * @param $type
     * @param $db
     * @return |null
     */
    public function LmCacheDel($key, $db = null)
    {
        if (empty($key)) {
            return null;
        }
        $this->setRedisDb($db);
        return Redis::del($key);
    }

    /**
     * 设置redis缓存
     * @param $key
     * @param $val
     * @param $exp
     * @return mixed
     */
    protected function setRedisCache($key, $val, $exp)
    {
        if (is_array($val))
        {
            $val = json_encode($val);
        }
        return Redis::set($key, $val, 'EX', $exp);
    }

    /**
     * 获取redis缓存
     * @param $key
     * @return mixed
     */
    protected function getRedisCache($key)
    {
        $val = Redis::get($key);
        $array = json_decode($val, true);
        return json_last_error() == JSON_ERROR_NONE ? $array : $val;
    }

    /**
     * 设置锁, 取得锁资源
     * @notice 设置锁值时，必须按照 时间戳 + ; + 随机值的方式，以确保验证逻辑。之所以提供自定义的锁值，是因为在某些情况下，可能要根据实际业务改变锁值的验证强度（一般只增强）
     * @author Neptune
     * @param string: $lock_key 锁名
     * @param mixed: $lock_value 锁值， 注：请勿设置此值为 bool 变量
     * @param int: $ttl 锁定时间(秒)
     * @param bool: $get_value 是否取得锁值
     * @param string: $db 数据库
     * @return mixed 设置结果
     */
    public function setLock($lock_key, $lock_value = 'default', $ttl = 5, $get_value = false, $db = null) {
        if($lock_key == '') {
            return false;
        }
        $this->setRedisDb($db);

        // [默认为]处于等待锁资源状态
        // 基本思想：
        //     1、读取原值
        //     2、原值不存在或超时则进行GETSET竞争，通过检查GETSET结果是否保证旧值过期或空来争夺锁
        //     3、事务开启【避免GETSET等操作冲突，确保操作原子性】：删GETSET结果，设置新值。
        //     4、事务结束后检验新值是否对应预期，对应则认为获取成功
        $check_multi = false;
        // 读取旧值
        $lock = Redis::get($lock_key);
        if(!$lock || $lock == 'nil') {
            // 可以进行加锁计划
            $check_multi = true;
        } else {
            $lock = explode(';', $lock);
            if((float)$lock[0] < microtime(true)) {
                // 过期
                $check_multi = true;
            } else {
                // 未过期
                logger('[4]lock '.$lock_key.' get failed, value: '.$lock_value.', current time: '.microtime(true));
                return false;
            }
        }
        if($lock_value == 'default') {
            $_lock_value = microtime(true) + $ttl + 1;// 过期时间；考虑 Redis 缓存过期机制并非实时
            $shuffle = random_int(1000, 9999);// 这里即使加了随机值，但理论上讲，仍然存在碰撞的可能
            $lock_value = $_lock_value.';'.$shuffle;// 在微秒粒度内，仍然不能确保唯一，即便碰撞几率已经足够小。为了兼顾性能，这里假设值是唯一的
        }
        if($check_multi) {
            $set_competition = Redis::getset($lock_key, $lock_value);
            if($set_competition && $set_competition != 'nil') {
                $set_competition = explode(';', $set_competition);
                if((float)$set_competition[0] < microtime(true)) {
                    // 旧值过期，表明此步已抢占锁资源，执行事务
                    Redis::watch($lock_key);
                    Redis::multi();
                    Redis::del($lock_key);
                    Redis::set($lock_key, $lock_value, 'NX', 'EX', $ttl);
                    Redis::exec();
                    usleep(10);
                    $new_lock = Redis::get($lock_key);
                    if($new_lock == $lock_value) {
                        $this->lockValues[$lock_key] = $lock_value;// 改为数组，以支持多重加锁
                        logger('[2]lock '.$lock_key.' get success, value: '.$lock_value.', current time: '.microtime(true));
                        return $get_value ? $new_lock : true;
                    } else {
                        logger('[2]lock '.$lock_key.' get failed, value: '.$lock_value.', current time: '.microtime(true));
                        return false;
                    }
                } else {
                    logger('[3]lock '.$lock_key.' get failed, value: '.$lock_value.', current time: '.microtime(true));
                    return false;
                }
            } else {
                // 没有旧值，表明此步已抢占锁资源，执行事务
                Redis::watch($lock_key);
                Redis::multi();
                Redis::del($lock_key);
                Redis::set($lock_key, $lock_value, 'NX', 'EX', $ttl);
                Redis::exec();
                usleep(10);
                $new_lock = Redis::get($lock_key);
                if($new_lock == $lock_value) {
                    $this->lockValues[$lock_key] = $lock_value;// 改为数组，以支持多重加锁
                    logger('[1]lock '.$lock_key.' get success, value: '.$lock_value.', current time: '.microtime(true));
                    return $get_value ? $new_lock : true;
                } else {
                    logger('[1]lock '.$lock_key.' get failed, value: '.$lock_value.', current time: '.microtime(true));
                    return false;
                }
            }
        }
    }

    /**
     * 删除锁 / Unlock
     * @author Neptune
     * @param string: $lock_key 锁名
     * @param string: $expected_value 预期锁值；用于检验锁是否是预期要删除的锁。默认为：default，即算法默认设置的锁值。如果要删除自定义锁值的锁，则最好在竞争锁时要求返回锁值，据此解锁，一般在 <异步删锁> 或 <Redis管理调试> 才会用到此值
     * @param bool: $force_unlock 是否强制删除/解锁
     * @param string: $db 数据库
     * @return bool 处理结果
     */
    public function delLock($lock_key, $expected_value = 'default', $force_unlock = false, $db = null) {
        if($lock_key == '') {
            return false;
        }
        $this->setRedisDb($db);

        $expected_value = $expected_value == 'default' ? (isset($this->lockValues[$lock_key]) ? $this->lockValues[$lock_key] : 'default') : $expected_value;
        // 解锁前必须验证锁值是否一致，防止冲突
        $current_lock = Redis::get($lock_key);
        if(!$current_lock) {
            logger('[3]Unlock '.$lock_key.' success, expected lock value is: '.$expected_value);
            return true;// 锁不存在，那就没事可做了
        }
        if($current_lock === $expected_value || $force_unlock) {
            $res = Redis::del($lock_key);
            logger('[1]Unlock '.$lock_key.' '.($res ? 'success' : 'failed').', expected lock value is: '.$expected_value);
            return $res;
        } else {
            logger('[2]Unlock '.$lock_key.' failed, expected lock value is: '.$expected_value.', while current lock value is: '.$current_lock);
            return false;
        }
    }

    /**
     * 获取锁值
     * @author Neptune
     * @param string: $lock_key 锁名
     * @param string: $db 数据库
     * @return bool|string
     */
    public function getLock($lock_key, $db = null) {
        if($lock_key == '') {
            return false;
        }
        $this->setRedisDb($db);
        $_lock = Redis::get($lock_key);
        // 检查是否过期
        $lock = explode(';', $_lock);
        if(count($lock) < 2) {
            return false;
        }
        if((float)$lock[0] >= microtime(true)) {
            return $_lock;// 未过期则返回锁值
        } else {
            return false;
        }
    }

    /**
     * ID生成器
     * @param string $filed ID对应的字段名
     * @return bool|int 生成的ID，超时/错误则返回false
     */
    public function IDGenerator($filed) {
        if(!in_array($filed, ['invite_code'])) {
            return false;
        }
        // 从 1 开始自增，增量 1
        // 为保证每次获取的 ID 单调递增和唯一，此处将直接使用单一节点处理ID自增
        // 由于 key 对应数据不是持续稳定存在，故可能造成查库的情况，需要结合锁进行控制，保证数据正常。
        // 注意：由于Redis吞吐量的限制，可能存在响应延迟和业务失败，此时在前端应做好响应提示，让用户明确问题所在，优化用户体验
        $lock_key = 'IDGenerator-concurrency-control';
        $lock_time = 1; // seconds
        $waiting_time = 15; // micro seconds
        $waiting_remain = 2000; // micro seconds
        while(!$lock = $this->setLock($lock_key, 'default', $lock_time) && $waiting_remain) {
            $waiting_remain -= $waiting_time;
            usleep($waiting_time);
        }
        if(!$lock) {
            return false;
        }
        $tag = '{ID-Sequence-Generator1}';
        $key = $tag.'-'.config('app.name').'-IDGenerator-'.$filed;
        // Redis::del($key); // 调试
        $max_id = Redis::get($key);
        logger('原始ID： '.$max_id);
        if(!$max_id) {
            // 查库
            switch($filed) {
                case 'invite_code':
                    $max_id = max(1, DB::table('users')->max('invite_code'));
                    break;

                default:
                    $this->delLock($lock_key, 'default', true);
                    return false;
            }
        }
        // 自增
        ++$max_id;
        logger('最新ID： '.$max_id);
        Redis::del($key);
        Redis::set($key, $max_id, 'NX', 'EX', 3600 * 24 * 7);
        $this->delLock($lock_key, 'default', true);
        return $max_id;
    }
}