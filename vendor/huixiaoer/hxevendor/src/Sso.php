<?php
/**
 * sso登录用的
 * @authors bin.liang
 * @date    2018-02-23 09:46:10
 */
namespace HxeVendor; 
class Sso
{
    public static function Say()
    {
        return 'Hello World!';
    }

    public static function getEnv(){
    	$array = [];
    	if (function_exists('env')) {
		    $array['connect_url'] = env('BOSS_CENTER_CALLBACK');
		    $array['login_url'] = env('BOSS_CENTER_USER_LOGIN');
		    $array['userinfo_url'] = env('BOSS_CENTER_USER_INFO');
		    $array['app_key'] = env('APP_KEY_LOGIN');
		} else {
		    $array['connect_url'] = Env::get('BOSS_CENTER_CALLBACK');
		    $array['login_url'] = Env::get('BOSS_CENTER_USER_LOGIN');
		    $array['userinfo_url'] = Env::get('BOSS_CENTER_USER_INFO');
		    $array['app_key'] = Env::get('APP_KEY_LOGIN');
		}
		return $array;
    }

    public static function getAuthUrl(){
    	$array = self::getEnv();
    	if(!$array['connect_url'] || !$array['app_key']) return '';
    	header('Location:'.$array['connect_url'].'?appkey='.$array['app_key']);
    	exit;
    }

    public static function getUserInfo($ticket){
    	$array = self::getEnv();
        $result = self::curl($array['userinfo_url'],['ticket'=>$ticket,'appkey'=>$array['app_key']],'post');
        $result = json_decode($result, true);
        return $result;
    }

    public static function curl($url, $fields = array(), $method = 'post',$debug = false) {
        $curl = curl_init ();
        curl_setopt ( $curl, CURLOPT_URL, $url );
        curl_setopt ( $curl, CURLOPT_HTTPHEADER, array (
                'Expect:'
        ) );
        curl_setopt ( $curl, CURLOPT_TIMEOUT, 60 );
        curl_setopt ( $curl, CURLOPT_MAXREDIRS, 6 );
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt ( $curl, CURLOPT_COOKIEJAR, "/tmp/curl_cookie_file" );
        curl_setopt ( $curl, CURLOPT_COOKIEFILE, "/tmp/curl_cookie_file" );
        if (strtolower ( $method ) == 'post') {
            curl_setopt ( $curl, CURLOPT_POST, true );
            curl_setopt ( $curl, CURLOPT_POSTFIELDS, http_build_query ( $fields ) );
        }
        curl_setopt ( $curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)' );
        $result = curl_exec ( $curl );
        if($debug){
            echo "=====post data======\r\n";
            var_dump($fields);
            echo '=====info====='."\r\n";
            print_r( curl_getinfo($curl) );
            echo '=====$response====='."\r\n";
            print_r( $result );
            echo '=====error====='."\r\n";
            echo  curl_error($curl) ;
        }
        return $result;
    }
}
