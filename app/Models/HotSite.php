<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Libs\HxeKongApi;
use App\Libs\HxeRedis;
use App\Libs\AES;
use App\Libs\Utility;
use Illuminate\Support\Facades\Log;

class HotSite extends Model
{

    /**
     * 此文件只做参考使用,参考完成立即删除
     * @param int $flag
     * @return array|bool|mixed
     */
    protected $connection = 'NEWCMS';
    protected $table = 'hot_site';

    //获取推荐场地信息
    public function getSiteInfo($city_code, $order, $flag = 0) {
        $redis_key = config('rediskey.refuseInfo', env('REDIS_PREFIX') . ':demo_city_site') . $city_code;
        $data      = HxeRedis::get($redis_key);
        if ($data !== null) return $data;
        $map       = [
            'status'     => 1,
            'is_deleted' => 0,
            'city_code'  => $city_code,
            'flag'       => $flag,
        ];
        $fields    = ['id', 'city_code', 'site_id', 'hotel_banner_title', 'flag', 'status'];
        $site_info = $this->where($map)->orderBy('order_id', $order)->limit(10)->get($fields);
        if ($site_info) {
            $output_data = $site_info->toArray();
            HxeRedis::set($redis_key, $output_data);

            return $output_data;
        }

        return false;
    }
    //kong接口demo
    public function refuseInfo($types = 1){
        $input_data  = ['types' => $types];
        $kongApi     = HxeKongApi::getInstance('case');
        $output_data = $kongApi->kongCurl('/case_base_data', $input_data, 'get');
        $output_data = json_decode($output_data, true);
        if (!isset($output_data['code']) || $output_data['code'] != 2000 || !isset($output_data['data'])) {
            Log::warning("方案不满意原因获取失败input_data:" . json_encode($input_data) . "output_data:" . json_encode($output_data) . date('Y-m-d H:i', time()) . "\r\n");
            return [];
        }
        return $output_data;
    }

    public static function aesDemo($number){
        return Utility::base64_urlSafeEncode(AES::encode(env('AES_KEY'), $number));
    }
}

