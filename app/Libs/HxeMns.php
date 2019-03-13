<?php
namespace App\Libs;
/**
 * 阿里主题消息发送
 * @authors bin.liang
 * @date    2018-02-26
 */
use AliyunMNS\Http\HttpClient;
use AliyunMNS\Topic;
use AliyunMNS\Requests\PublishMessageRequest;
class HxeMns {

	private static $client;

	/**
	 * 设置链接属性
	 */
	private static function _setConnected(){
		$config = config('queue.connections.topic');
		self::$client = new HttpClient($config['endpoint'],$config['key'],$config['secret']);
	}

	/**
	 * 发送信息到ali主题列表
	 * @Author   bin.liang
	 * @DateTime 2018-02-26
	 * @param    str|arr    $message   [纤细信息]
	 * @param    string     $topicName [主题名称]
	 */
	public static function publishMessage($message, $topicName = '') {
		self::_setConnected();
		if (!$topicName) $topicName = config('queue.connections.topic.topicName');
		$topic = new Topic(self::$client, $topicName);
		$request = new PublishMessageRequest($message);
		//记录发送信息
		file_put_contents(storage_path('logs').'/mns_info.log', date("Y-m-d H:i:s")."  ".$message , FILE_APPEND);

		try {
			$topic->publishMessage($request);
		} catch (MnsException $e) {
			//记录错误信息
			file_put_contents(storage_path('logs').'/mns_err.log', date("Y-m-d H:i:s")."  publishMessage failed: " . $e->getMessage() , FILE_APPEND);
			return false;
		}
		return true;
	}

    /**
     * 生成随机字符串
     *
     * @param int $len
     *        	生成长度
     * @param string $type
     *        	生成类型，包括num(0-9)、char(a-z)、charnum(0-9+a-z)、all(0-9+a-z+符号)。默认为num
     * @param string $model
     *        	自定义生成字符的范围
     * @return string
     */
    public static function generateString($len, $type = 'num', $model = '') {
        $models = array (
            'num' => '0123456789',
            'char' => 'abcdefghijklmnopqrstuvwxyz',
            'all' => 'abcdefghijklmnopqrstuvwxyz0123456789_=%&*<>|',
            'char&num' => 'abcdefghijklmnopqrstuvwxyz0123456789'
        );
        if (! $model)
            $model = $models [$type];
        if (! $model || ! $len)
            return false;

        $ret = '';
        for($i = 0; $i < $len; $i ++) {
            $n = mt_rand ( 0, strlen ( $model ) - 1 );
            $ret .= $model {$n};
        }
        return $ret;
    }
}