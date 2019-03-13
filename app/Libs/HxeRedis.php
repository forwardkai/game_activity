<?php
namespace App\Libs;

use Illuminate\Support\Facades\Redis;

class HxeRedis{

    public static function get($key){
        $value = Redis::get($key);
        return json_decode($value,true);
    }

    public static function set($key,$value,$expire=7200){
        $value = json_encode($value);
        Redis::set($key,$value);
        self::setExpire($key,$expire);
    }

    public static function hmset($key,$value,$expire){
        foreach ($value as $k=>&$v){
            $v = json_encode($v);
        }
        Redis::hmset($key,$value);
        self::setExpire($key,$expire);
    }

    public static function hmget($key,$fields){
        $result = Redis::hMGet($key,$fields);
        foreach ($result as $k=>&$v){
            $v = json_decode($v,true);
        }
        return $result;
    }

    public static function hgetall($key){
        $result = Redis::hGetAll($key);
        foreach ($result as $k=>&$v){
            $v = json_decode($v,true);
        }
        return $result;
    }

    public static function del($key){
        Redis::delete($key);
    }

    public static function setLock($lockKey,$lock_time){
        return Redis::set($lockKey, 1, 'ex', $lock_time,'nx');
    }
    public static function setExpire($key,$expire){
        if(empty($expire))
            Redis::setTimeout($key,env('REDIS_EXPIRE',3600));
        else
            Redis::setTimeout($key, $expire);
    }
}