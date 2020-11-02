<?php
/**
 * 项目定制的 Snowflake，用于生成唯一ID
 */

namespace App\Libraries\Snowflake;
use Kra8\Snowflake\Snowflake as SnowflakeBase;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Snowflake extends SnowflakeBase {
    const TIMESTAMP_LEFT_SHIFT      = 22;

    const DATACENTER_ID_LEFT_SHIFT  = 17;

    const WORKER_ID_LEFT_SHIFT      = 12;

    const MAX41BITS = 2199023255551;

    const MAX5BITS = 31;

    const MAX12BITS = 4095;

    private $epoch;

    private $lastTimestamp;

    private $datacenterId;

    private $sequence;

    private $workerId;

    private $project;

    public function __construct()
    {
        parent::__construct();
        $this->project = config('app.name');// to prevent conflict with other project
    }

    /**
     * Generate the 64bit unique id.
     *
     * @return integer
     *
     * @throw Exception
     */
    public function next()
    {
        /*
        $timestamp = $this->timestamp();

        if ($timestamp < $this->lastTimestamp) {
            $errorLog = "Couldn't generation snowflake id, os time is backwards. [last timestamp:{$this->lastTimestamp}]";
            Log::error($errorLog);
            throw new Exception($errorLog);
        }

        if ($timestamp === $this->lastTimestamp) {
            $this->sequence = $this->sequence + 1;
            if ($this->sequence > self::MAX12BITS) {
                usleep(1);
                $timestamp      = $this->timestamp();
                $this->sequence = 0;
            }
        } else {
            $this->sequence = 0;
        }

        $this->lastTimestamp = $timestamp;
        */
        list($this->sequence, $timestamp) = $this->getSequence();

        $_id = ((($timestamp - $this->epoch) & self::MAX41BITS) << self::TIMESTAMP_LEFT_SHIFT)
            | (($this->datacenterId & self::MAX5BITS) << self::DATACENTER_ID_LEFT_SHIFT)
            | (($this->workerId & self::MAX5BITS) << self::WORKER_ID_LEFT_SHIFT)
            | ($this->sequence & self::MAX12BITS);

        return $_id > 0 ? $_id : (~$_id);
    }

    /**
     * Get the sequence number which is managed by Redis,
     * With the help of redis, the sequence number will be unique among all the concurrent accsesses
     * @notice Redis must be available and it can be able to handle high concurrency
     * @author Neptune
     * @return array [sequence, timestamp]
     */
    protected function getSequence() {
        $timestamp = $this->timestamp();
        if ($timestamp < $this->lastTimestamp) {
            $errorLog = "Couldn't generation snowflake id, os time is backwards. [last timestamp:{$this->lastTimestamp}]";
            Log::error($errorLog);
            throw new Exception($errorLog);
        }
        $tag = '{ID-Sequence-Generator}';// Do not change this Hash Tag unless the Redis hash tag is marked by other characators
        $key = $tag.$this->project.'-'.$timestamp.'-'.$this->datacenterId.'-'.$this->workerId;
        $ttl = 2;
        $sequence = max(Redis::incr($key) - 1, 0);
        Redis::expire($key, $ttl);
        if($sequence > self::MAX12BITS) {
            usleep(1);
            return $this->getSequence();
        } else {
            $this->lastTimestamp = $timestamp;
            return [$sequence, $timestamp];
        }
    }
}