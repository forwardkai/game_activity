<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Library\HxeUtil;

class MeetCityRelation extends Model
{
    protected $connection = 'NEWCMS';
    protected $table = 'meet_city_relation';
    public $timestamps = false;

    //获取推荐城市信息
    public function getCityInfo($city_code, $order){
        $map = [
            'is_deleted' => 0,
            'city_code' => $city_code,
        ];
        $fields = ['id','relation_city','plane_time','train_time'];
        $city_info = $this->where($map)->orderBy('id',$order)->get($fields);
        if($city_info){
            return $city_info->toArray();
        }
        return false;
    }
}

