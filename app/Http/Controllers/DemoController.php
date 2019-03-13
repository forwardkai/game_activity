<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteInfo;
use App\Models\MeetCityRelation;
use App\Libs\HxeMns;
use Illuminate\Support\Facades\Log;

/**
 * 此文件只做参考使用,参考完成立即删除
 * @param int $flag
 * @return array|bool|mixed
 */
class DemoController extends Controller
{
    //return code码 成功 接受参数验证 成功
    public function demo(Request $request) {
//        phpinfo();exit;
        $string = filter($request->get('city_code', 'd'), 's');

        return 1000;//测试return code码，可用
    }

    //Model  +  Mysql + Redis 测试 - 正常 可用
    public function MysqlDemo(Request $request) {
        $city_code = filter($request->get('city_code', '11000'), 'd');
        $order         = "asc";
        $meetCityModel = new MeetCityRelation;
        $demoModel     = new SiteInfo;
        // 获取推荐城市
        $meet_citys = $meetCityModel->getCityInfo($city_code, $order);
        if ($meet_citys) {
            foreach ($meet_citys as $k => $v) {
                $sites = $demoModel->getSiteInfo($v['relation_city'], 0);//Redis 测试 正常

                return $sites;
            }
        }

        return 5000;
    }

    //Kong接口 测试 正常
    public function kongDemo() {
        Log::info('success'); //日志测试 正常
        $demoModel = new SiteInfo;
        // 获取推荐城市
        $sites = $demoModel->refuseInfo();

        return $sites;
    }

    //模版 测试
    public function viewDemo() {
//        $aes_data = SiteInfoController.php::aesDemo(18899922133); //加解密 - 需要开启相应php扩展
        return view('demo.view_demo');
    }

    //上传图片
    public function imgDemo(Request $request) {
        $img     = $_FILES['img_src'];
        if (!empty($img['name'])) {
            if ($img['size'] > 5 * 1024 * 1024) {
                $data = [
                    'code'    => 1,
                    'message' => '图片不能大于5M',
                    'data'    => '',
                ];

                return $data;
            }
            $ext = strtolower(substr($img['name'], strrpos($img['name'], '.') + 1));
            if (!in_array($ext, ['jpg', 'png', 'jpeg'])) {
                $data = [
                    'code'    => 1,
                    'message' => '不符合图片的格式要求,请上传JPG/PNG/JPEG格式图片',
                    'data'    => '',
                ];

                return $data;
            }
            $str           = md5(date('YmdHis') . mt_rand(100000, 999999));
            $new_file_name = $str . '.' . $ext;
            /* 本地上传 */
            $upload_path = env('IMG_DIR');
            if (!file_exists($upload_path)) mkdir($upload_path, 0777, true);
            $org_file_name = $upload_path . '/' . $new_file_name;
            if (!move_uploaded_file($img['tmp_name'], $org_file_name)) {
                $ret = [
                    'code'    => 1,
                    'message' => '图片上传失败,请从新上传未成功的图片',
                    'data'    => '',
                ];

                return $ret;
            }
            $img_src = $upload_path . '/' . $new_file_name; // 本地图片地址+图片名

            return $img_src;
        }
        return 1000;
    }

    /**
     * 推送信息到新的消息中心
     * @param $common_code int  接收人
     * @param $template_code  消息模板code
     * @param $content str|arr 消息内容
     * @param $type  类型 1：短信 2：模板消息 3：邮箱 4：语音信息
     * @param $service_code  发送载体  短信时 1：国都 2：微网通
     * @param $sms_sign  短信签名
     */
    protected function pushMessage($common_code, $template_code, $content, $type=1, $service_code=4, $sms_sign='会小二') {

        $data['template_code'] = $template_code;
        $data['content'] = $content;
        $data['common_code'] = $common_code;
        $data['business_code'] = '1000000'; //例如:会小二app ' 10000002'
        $data['service_type'] = $type;
        $data['service_code'] = $service_code;
        $data['sms_sign'] = $sms_sign;
        $kdata = $data;
        ksort($kdata);
        $str = md5(json_encode($kdata));
        $data['times'] = time();
        $data['code'] = HxeMns::generateString(8,'char&num');
        $data['signature'] = md5($str. env('MSG_SERVICE_API_KEY') . $data['times'] . $data['code']);

        //组装消息体并发送
        $message = json_encode($data);
        $ret = HxeMns::publishMessage($message);
        return $ret;
    }

}