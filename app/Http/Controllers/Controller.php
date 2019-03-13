<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Libs\IpSearch;
use App\Models\Member;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Libs\HxeMns;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;

use Upyun\Upyun;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $client_ip = '';

    protected $city_code = '';


    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    function get_client_ip($type = 0, $adv = true) {
        $type = $type ? 1 : 0;
        $ip   = '';
        if ($this->client_ip) return $this->client_ip[ $type ];
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) unset($arr[ $pos ]);
                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long            = sprintf("%u", ip2long($ip));
        $ip              = $long ? [$ip, $long] : ['0.0.0.0', 0];
        $this->client_ip = $ip;

        return $ip[ $type ];
    }

    /**获取用户访问的机型
     * @return string
     */
    protected function get_user_os() {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $user_os = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/win/i', $user_os)) {
                $user_os = 'windows';
            } else {
                if (preg_match('/linux/i', $user_os)) {
                    $user_os = 'linux';
                } else {
                    if (preg_match('/unix/i', $user_os)) {
                        $user_os = 'unix';
                    } else {
                        if (preg_match('/mac/i', $user_os)) {
                            $user_os = 'Mac';
                        } else {
                            $user_os = 'other';
                        }
                    }
                }
            }

            return $user_os;
        }
    }

    /**
     * 快速完成一个Curl请求
     *
     * @param string $url
     * @param array  $fields
     *            POST字段，传入数组
     * @param string $method
     *            = 'post'
     * @param bool   $debug
     * @return string 结果
     */
    public static function curl($url, $fields = [], $method = 'post', $debug = false) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Expect:'
        ]);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 6);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_COOKIEJAR, "/tmp/curl_cookie_file");
        curl_setopt($curl, CURLOPT_COOKIEFILE, "/tmp/curl_cookie_file");
        if (strtolower($method) == 'post') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields));
        }
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');

        $result = curl_exec($curl);
        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($fields);
            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($curl));
            echo '=====$response=====' . "\r\n";
            print_r($result);
            echo '=====error=====' . "\r\n";
            echo curl_error($curl);
        }

        return $result;
    }

    //函数实现快速排序
    function quickSort(&$arr, $leftIndex, $rightIndex) {
        $index = $this->partition($arr, $leftIndex, $rightIndex);
        if ($leftIndex < $index - 1) {
            $this->quickSort($arr, $leftIndex, $index - 1);
        }
        if ($index < $rightIndex) {
            $this->quickSort($arr, $index, $rightIndex);
        }

        return $arr;
    }

    //返回快速排序左下标
    function partition(&$arr, $leftIndex, $rightIndex) {
        $pivot = $arr[ ($leftIndex + $rightIndex) / 2 ];

        while ($leftIndex <= $rightIndex) {
            while ($arr[ $leftIndex ] < $pivot)
                $leftIndex++;
            while ($arr[ $rightIndex ] > $pivot)
                $rightIndex--;
            if ($leftIndex <= $rightIndex) {
                $tmp                = $arr[ $leftIndex ];
                $arr[ $leftIndex ]  = $arr[ $rightIndex ];
                $arr[ $rightIndex ] = $tmp;
                $leftIndex++;
                $rightIndex--;
            }
        }

        return $leftIndex;
    }

    /**
     * [sortStr 对,号隔开的字符串进行排序重组]
     * @param [string] $[str] [<需要重组的字符串 2,7,5>]
     * @return [str] [重组后的字符串 2,5,7]
     */
    public function sortStr($str) {
        if (!strpos($str, ',')) return $str;
        $arr = explode(',', $str);
        sort($arr);

        return implode(',', $arr);
    }

    /**
     * replace  过滤特殊字符
     */
    public function replace($str) {
        $pattern[0] = '&';
        $pattern[1] = '<';
        $pattern[2] = ">";
        $pattern[3] = '\n';
        $pattern[4] = '"';
        $pattern[5] = "'";
        $pattern[6] = "%";
        $pattern[7] = '(';
        $pattern[8] = ')';
        $pattern[9] = '+';
        //$pattern[10] = '/-/';
        $replacement[0] = '/&amp;/';
        $replacement[1] = '/&lt;/';
        $replacement[2] = '/&gt;/';
        $replacement[3] = '/<br>/';
        $replacement[4] = '/&quot;/';
        $replacement[5] = '/&#39;/';
        $replacement[6] = '/&#37;/';
        $replacement[7] = '/&#40;/';
        $replacement[8] = '/&#41;/';
        $replacement[9] = '/&#43;/';

        //$replacement[10] = '&#45;';
        return preg_replace($replacement, $pattern, $str);
    }
}

