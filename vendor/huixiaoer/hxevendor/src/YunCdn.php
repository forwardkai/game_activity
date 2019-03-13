<?php
namespace HxeVendor;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Upyun\Upyun;
use Upyun\Config;

class YunCdn
{
    protected $config = [];

    public static function uploadQiniu($file_path,$new_file_name,$bucket){
        $config = [];
        if (function_exists('env')) {
            $config['QINIU_USER'] = env('QINIU_USER');
            $config['QINIU_SECRET'] = env('QINIU_SECRET');
            $config['QINIU_BUCKET'] = !empty($bucket) ? $bucket : env('QINIU_BUCKET');
        } else {
            $config['QINIU_BUCKET'] = !empty($bucket) ? $bucket : Env::get('QINIU_BUCKET');
            $config['QINIU_USER'] = Env::get('QINIU_USER');
            $config['QINIU_SECRET'] = Env::get('QINIU_SECRET');
        }
        $auth = new Auth($config['QINIU_USER'], $config['QINIU_SECRET']);
        $token = $auth->uploadToken($config['QINIU_BUCKET']);
        $uploadMgr = new UploadManager();
        list($ret, $error) = $uploadMgr->putFile($token, $new_file_name, $file_path);
        if (!empty($ret['hash'])) {
            return $ret['hash'];
        } else {
            return false;
        }
    }

    public static function uploadUpyun($file_path,$new_file_name,$bucket){
        $config = [];
        if (function_exists('env')) {
            $config['UPYUN_USER'] = env('UPYUN_USER');
            $config['UPYUN_SECRET'] = env('UPYUN_SECRET');
            $config['UPYUN_BUCKET'] = !empty($bucket) ? $bucket : env('UPYUN_BUCKET');
        } else {
            $config['UPYUN_USER'] = Env::get('UPYUN_USER');
            $config['UPYUN_SECRET'] = Env::get('UPYUN_SECRET');
            $config['UPYUN_BUCKET'] = !empty($bucket) ? $bucket : Env::get('UPYUN_BUCKET');
        }
        $bucketConfig = new Config($config['UPYUN_BUCKET'], $config['UPYUN_USER'], $config['UPYUN_SECRET']);
        $upyun = new Upyun($bucketConfig);
        $file = fopen($file_path,'r');
        $ret = $upyun->write('/'.$new_file_name, $file);
        if (!empty($ret) && isset($ret['x-upyun-height'])) {
            return true;
        }else{
            return false;
        }

    }

    public static function upload($file_path,$new_file_name,$bucket){
        $qiniu_bucket = isset($bucket['qiniu']) ? $bucket['qiniu'] : '';
        $upyun_bucket = isset($bucket['upyun']) ? $bucket['upyun'] : '';
        $ret1 = self::uploadQiniu($file_path,$new_file_name,$qiniu_bucket);
        $ret2 = self::uploadUpyun($file_path,$new_file_name,$upyun_bucket);
        if($ret1!== false && $ret2 !== false)
            return true;
        return false;
    }
}