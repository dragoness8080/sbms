<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/11
 * Time: 21:53
 */
defined('IN_IA') or exit('Access Denied');

global $_W,$_GPC;

$openid = m('user')->getOpenid();

$dt_start = $_GPC['dt_start'];
$dt_end = $_GPC['dt_end'];

$days = m('common')->getDays($dt_start,$dt_end);
$rid = intval($_GPC['id']); //房间ID
$member = m('member')->getMember($openid);
$rooms = pdo_get('sbms_room', array('id' => $rid, 'uniacid' => $_W['uniacid']));
$hotal = pdo_get('sbms_seller', array('id' => $rooms['seller_id'], 'uniacid' => $_W['uniacid']));

$order = array(
    'hotal_name' => $hotal['name'],
    'hotal_address' => $hotal['address'],
    'room_bed' => $rooms['size'],
    'room_name' => $rooms['name'],
    'room_num' => 1,
    'room_price' => $rooms['price'],
    'realname' => $member['zs_name'],
    'mobile' => $member['tel'],
    'idcard' => '',
    'time' => date('H:i'),
    'daymoney' => array(),
    'dayallmoney' => 0,
    'discountmoney' => 0,
    'levelmoney' => 0,
    'paymoney' => 0,
    'days' => $days,
    'levelrate' => 0
);

$start = strtotime($dt_start);
$end = strtotime($dt_end);
$dayAllMoney = 0;
do{
    $daymoney[] = array(
        'title' => date('y年m月d日', $start) . '房费',
        'money' => $rooms['price']
    );
    $dayAllMoney += $rooms['price'];
    $start += 24 * 3600;    //增加一天

}while($start < $end);

$order['daymoney'] = $daymoney;
$order['dayallmoney'] = $dayAllMoney;

if($member['type'] == 2){
    $level = m('member')->getLevel($member['level_id']);
    $rate = $level['discount'];
    $levelMoney = $dayAllMoney * $rate / 100 * -1;
    $order['levelmoney'] = $levelMoney;
    $order['levelrate'] = $rate;
}

$order['paymoney'] = $dayAllMoney + $order['discountmoney'] + $order['levelmoney'];

include $this->template('house/check');