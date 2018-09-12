<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/8/24
 * Time: 下午2:44
 */
defined('IN_IA') or exit('Access Denied');

class Sbms_Common{

    /**
     * 获取民宿信息
     * @param $id
     * @return bool
     */
    public function getHouseForId($id){
        global $_W;

        $house = pdo_get('sbms_seller', array('id' => $id, 'uniacid' => $_W['uniacid']));
        return $house;
    }

    /**
     * 获取房间数
     * @param array $room
     * @param $start
     * @param $end
     * @return bool
     */
    public function getFreeRooms($room = array(),$start,$end){
        if(empty($room) || !is_array($room)){
            return false;
        }

        if(empty($start) || empty($end)){
            return false;
        }

        $start = strtotime($start);
        $end = strtotime($end);

        $rid = $room['id'];
        $rooms = pdo_getcolumn('sbms_roomnum', array('rid' => $rid,'dateday' => $start),'nums');
        if($rooms > 0){
            //$count = pdo_getcolumn('sbms_order', array('room_id' => $rid, 'status' => 1, 'departure_time >=' => $end, 'arrival_time =>' => $start), 'sum(num) as count');
        }

        return empty($rooms) ? false : true;
    }

    /**
     * 获取天数
     * @param $start
     * @param $end
     * @return float
     */
    public function getDays($start,$end){
        $start = strtotime($start);
        $end = strtotime($end);
        $days = floor(($end - $start) / 3600 / 24);
        return $days;
    }
}