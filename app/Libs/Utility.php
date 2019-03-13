<?php
namespace App\Libs;

class Utility {

    //替换查询关键词中得特殊字符
    public static function htmldecode($str) {
        if(empty($str)) return '';
        if($str=="") return $str;
        $str=str_replace("&",'',$str);
        $str=str_replace(">","",$str);
        $str=str_replace("<","",$str);
        $str=str_replace("&","",$str);
        $str=str_replace("'",'',$str);
        $str=str_replace("<br />",'',$str);
        $str=str_replace("''","'",$str);
        $str=str_replace("-","",$str);
        $str=str_replace("/"," ",$str);
        $str=str_replace("\\"," ",$str);
        $str=str_replace("select"," ",$str);
        $str=str_replace("join"," ",$str);
        $str=str_replace("union"," ",$str);
        $str=str_replace("where"," ",$str);
        $str=str_replace("insert"," ",$str);
        $str=str_replace("delete"," ",$str);
        $str=str_replace("update"," ",$str);
        $str=str_replace("like"," ",$str);
        $str=str_replace("drop"," ",$str);
        $str=str_replace("create"," ",$str);
        $str=str_replace("modify"," ",$str);
        $str=str_replace("rename"," ",$str);
        $str=str_replace("alter"," ",$str);
        $str=str_replace("cas"," ",$str);
        return $str;
    }

    //简单的CURL
    public static function getDataFromUrl($url){
        $ch = curl_init();
        // set URL and other appropriate options
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; SV1; .NET CLR 1.1.4322)',
            CURLOPT_TIMEOUT => 60); // 1 minute timeout (should be enough)

        curl_setopt_array($ch, $options);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }


    /**
     * 对提供的数据进行urlsafe的base64编码。
     *
     * @param string $data 待编码的数据，一般为字符串
     *
     * @return string 编码后的字符串
     * @link http://developer.qiniu.com/docs/v6/api/overview/appendix.html#urlsafe-base64
     */
    public static function base64_urlSafeEncode($data)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($data));
    }

    /**
     * 对提供的urlsafe的base64编码的数据进行解码
     *
     * @param string $data 待解码的数据，一般为字符串
     *
     * @return string 解码后的字符串
     */
    public static function base64_urlSafeDecode($str)
    {
        $find = array('-', '_');
        $replace = array('+', '/');
        return base64_decode(str_replace($find, $replace, $str));
    }
}
