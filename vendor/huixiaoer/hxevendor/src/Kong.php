<?php
/**
 * 空服务接口
 * @authors bin.liang
 * @date    2018-02-23 09:46:10
 */
namespace HxeVendor; 

class Kong {
    protected $config = [];

    //选择链接空的账户
    public function __construct($key = '') {
        try {
            $config = config('kong');
            if (!$key) $key = $config['default'];
            $this->config = $config[$key];
        } catch (\Exception $e) {
            throw new \Exception('获取kong服务链接异常:' . $e->getMessage(), 1);
        }
    }

    /**
     * 执行请求操作
     * @Author   bin.liang
     * @DateTime 2018-01-27
     * @param    string     $uri    [请求地址]
     * @param    string     $method [请求方式]
     * @param    array      $fields [请求参数]
     */
    public function exec($uri, $method, $fields = []) {
        //用户信息
		$info['username'] = $this->config['username'];
		$info['pwd'] = $this->config['password'];
		$info['uri'] = $uri;
        //请求数据
		$url = $this->config['url'].$uri;
		return $this->kongCurl($url,$fields,$info,$method);
    }

    /**
     * curl请求空服务数据
     * @Author   bin.liang
     * @DateTime 2018-02-23
     * @param    string     $url    [请求地址]
     * @param    array      $fields [请求参数]
     * @param    array      $info   [用户信息]
     * @param    string     $method [请求方式]
     */
	public function kongCurl($url, $fields = array(), $info = [], $method = "post"){
        $curl = curl_init();

        if ('get' === $method && $fields && is_array($fields)) {
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
        curl_setopt( $curl, CURLOPT_COOKIEJAR, "/tmp/curl_cookie_file" );
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