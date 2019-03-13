<?php
/**
 * 空 服务接口
 * @authors keming.zhang
 * @date    2018-03-16
 */
namespace App\Libs;

use Illuminate\Support\Facades\Log;

class HxeKongApi {

    protected $config = [];

    private static $_instance;

    private function __construct($api_key = ''){
        try {
            $config = config('kong.'.$api_key);

            //kong配置验证
            if(empty($config)|| empty($config['username'])|| empty($config ['password'])|| empty($config['url']))
                Log::warning('kong下不存在'.$api_key."的配置\r\n");

            $this->config = $config;
        } catch (\Exception $e) {
            Log::warning('kong下的'.$api_key.'配置出错:'.$e->getMessage()."\r\n");

        }
    }

    private function __clone() {}

    public static function getInstance($api_key){

        if (!isset(self::$_instance[$api_key]))
            self::$_instance[$api_key] = new self($api_key);

        return self::$_instance[$api_key];
    }


    /**
     * curl请求空服务数据
     * @Author   2018-09-13
     * @DateTime 2018-02-23
     * @param    string     $url    [请求地址]
     * @param    array      $fields [请求参数]
     * @param    array      $info   [用户信息]
     * @param    string     $method [请求方式]
     */
    public function kongCurl($uri, $fields = array(), $method = "post"){
        //kong的账号密码配置
        $info['username'] = $this->config['username'];
        $info['pwd'] = $this->config['password'];
        $info['uri'] = $uri;
        $url = rtrim($this->config['url'],'/').$uri;

        $curl = curl_init();
        array_push($fields,['app_name'=>env('APP_NAME','default_app_name')]);
        if ('get' === $method && is_array($fields)) {
            $fieldStr = http_build_query($fields);
            $url .= '?'.$fieldStr;
        }

        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_TIMEOUT, 60 );
        if(!empty($info['username']) && !empty($info['pwd']) && !empty($info['uri'])){
            $uri = $info['uri'];
            $username = $info["username"];
            $pwd = $info["pwd"];
            $time = gmdate('D, d M Y H:i:s \G\M\T', time());
            $hmac = base64_encode(hash_hmac("sha256","date: ".$time."\n".strtoupper($method)." ".$uri." HTTP/1.1",$pwd,true));
            $pass = 'hmac username="'.$username.'", algorithm="hmac-sha256", headers="date request-line", signature="'.$hmac.'"';
            $headers = [
                'date:'.$time,
                'Authorization:'.$pass
            ];
            curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
        }
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        if(strtolower ( $method ) == "post"){
            curl_setopt( $curl, CURLOPT_POST, true);
            curl_setopt( $curl, CURLOPT_POSTFIELDS, http_build_query ( $fields ));
        } else if(strtolower ( $method ) == "put") {
            curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'put');
            curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt( $curl, CURLOPT_POSTFIELDS, http_build_query ( $fields ));
        }else if(strtolower ( $method ) == "delete") {
            curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'delete');
        }
        curl_setopt( $curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;MSIE 6.0; Windows NT 5.1)" );
        $result = curl_exec( $curl );
        return $result;
    }
}