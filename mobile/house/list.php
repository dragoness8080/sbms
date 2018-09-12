<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/8/23
 * Time: 上午9:38
 */
defined('IN_IA') or exit('Access Denied');

global $_W,$_GPC;

$page = max(1,intval($_GPC['page']));
$pageSize = 6;
$pageCount = ($page - 1) * $pageSize;

$orderBy = $_GPC['orderby'];
if(empty($orderBy)){    $orderBy = 'recommend desc';}

$conditions['uniacid'] = $_W['uniacid'];
$conditions['state'] = 2;

$keyWord = $_GPC['keyword'];
if(!empty($keyWord)){
    $conditions['name like'] = '%' . $keyWord . '%';
}

$dt_start = empty($_GPC['dt_start']) ? date('Y-m-d', time()) : $_GPC['dt_start'];    //入住时间
$dt_end = empty($_GPC['dt_end']) ? date('Y-m-d', strtotime('+1 day')) : $_GPC['dt_end'];  //离开时间
$startArr = explode('-', $dt_start);
$startArr = intval($startArr[1]) . "月" . intval($startArr[2]) . "日";

$startTimes = strtotime($dt_start);
$endTimes = strtotime($dt_end);
$days = floor(($endTimes - $startTimes) / 3600 / 24);

//var_dump($orderBy); die();

if($_W['isajax']){
    $list = pdo_getall('sbms_seller', $conditions, array('id','name','star','address','zd_money','ewm_logo'),'id', $orderBy);
    if($startTimes < $endTimes){
        foreach ($list as $key => $item){
            $list[$key]['ewm_logo'] = tomedia($item['ewm_logo']);
            $hasRoomsCount = 0; //拥有的空房间数量
            $rooms = pdo_getall('sbms_room', array('seller_id'=>$key,'uniacid'=>$_W['uniacid']), array('id','total_num'),'id');
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

include $this->template('house/list');