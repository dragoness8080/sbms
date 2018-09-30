<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/31
 * Time: 22:32
 */
defined('IN_IA') or exit('Access Denied');

global $_W,$_GPC;
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];

$page = intval($_GPC['page']);
$pageSize = 6;
$pageCount = $page * $pageCount;

$orderBy = $_GPC['orderby'];
if(empty($orderBy)){    $orderBy = 'id desc';}

$conditions = "uniacid={$_W['uniacid']} AND state=2 AND special_offer=1";


$keyWord = $_GPC['keyword'];
if(!empty($keyWord)){
    $conditions .= " AND name LIKE '%{$keyWord}%'";
}

$sellerIds = $_GPC['sids'];
$roomTypes = $_GPC['rnams'];
$service = $_GPC['service'];

$province = $_GPC['province'];
$city = $_GPC['city'];
$area = $_GPC['area'];

if(!empty($sellerIds)){
    $conditions .= " AND id IN({$sellerIds})";
    $sellerIds = explode(',', $sellerIds);
}

if(!empty($roomTypes)){
    $roomTypes = explode(',', $roomTypes);
}

if(!empty($service)){
    $service = explode(',', $service);
    foreach ($service as $val){
        $conditions .= " AND {$val}=1";
        $services[$val] = 1;
    }
}

if(!empty($province)){
    $conditions .= " AND province='" . $province . "'";
}
if(!empty($city)){
    $conditions .= " AND city='" . $city . "'";
}
if(!empty($area)){
    $conditions .= " AND area='" . $area . "'";
}

//获取所有民宿列表
$homestay = pdo_getall('sbms_seller', array('uniacid' => $_W['uniacid'], 'state' => 2), array('id','name'));
$housetype = pdo_fetchall("SELECT DISTINCT name FROM " . tablename('sbms_room') . " WHERE uniacid=" . $_W['uniacid']);


$dt_start = empty($_GPC['dt_start']) ? date('Y-m-d', time()) : $_GPC['dt_start'];    //入住时间
$dt_end = empty($_GPC['dt_end']) ? date('Y-m-d', strtotime('+1 day')) : $_GPC['dt_end'];  //离开时间
$startArr = explode('-', $dt_start);
$startArr = intval($startArr[1]) . "月" . intval($startArr[2]) . "日";

$startTimes = strtotime($dt_start);
$endTimes = strtotime($dt_end);
$days = floor(($endTimes - $startTimes) / 3600 / 24);

if($_W['isajax']){
    $list = pdo_fetchall("SELECT id,name,star,address,zd_money,ewm_logo,province,city,area FROM " . tablename('sbms_seller') . " WHERE " . $conditions . " ORDER BY " . $orderBy);
    if($startTimes < $endTimes){
        foreach ($list as $key => $item){
            $list[$key]['ewm_logo'] = tomedia($item['ewm_logo']);
            //分数
            $info = pdo_fetch("SELECT SUM(score) AS score,COUNT(id) AS count FROM " . tablename('sbms_assess') . " WHERE seller_id={$item['id']} AND uniacid={$_W['uniacid']}");
            $score = empty($info['count']) ? 0 : round($info['score'] / $info['count'],2);
            $list[$key]['score'] = $score;

            $hasRoomsCount = 0; //拥有的空房间数量
            $roomWhere = " uniacid={$_W['uniacid']} AND seller_id={$item['id']}";
            if(!empty($roomTypes)){
                $roomWhere .= " AND name IN(";
                foreach ($roomTypes as $val){
                    $roomWhere .= "'" . $val . "',";
                }
                $roomWhere = substr($roomWhere,0,strlen($roomWhere) - 1);
                $roomWhere .= ")";
            }

            $rooms = pdo_fetchall("SELECT id,total_num FROM " . tablename('sbms_room') . " WHERE " . $roomWhere);
            foreach ($rooms as $id => $room){
                $used = pdo_getcolumn('sbms_roomnum', array('dateday >=' => $startTimes,'dateday <=' => $endTimes), 'count(id) as count');
                $hasRoomsCount += intval($room['total_num'] - intval($used));
            }
            if(empty($hasRoomsCount)){ unset($list[$key]);}
        }
    }
    $house = array_slice($list, $pageCount, 6);
    show_json(1,array('pageCount' => $pageSize,'list' => $house));
}

include $this->template('house/discount');