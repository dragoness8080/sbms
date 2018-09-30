<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/18
 * Time: 下午2:52
 */
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$openid = m('user')->getOpenid();
$uniacid = $_W['uniacid'];
$orderid = intval($_GPC['orderid']);
if($_W['ispoat']){
    $assess = array(
        'seller_id' => $_GPC['seller_id'],
        'score' => $_GPC['score'],
        'content' => $_GPC['content'],
        'img' => $_GPC['img'],
        'time' => time(),
        'user_id' => $_GPC['user_id'],
        'uniacid' => $uniacid
    );
    pdo_insert('sbms_assess', $assess);
}

$member = m('member')->getMember($openid);
$order = pdo_get('sbms_order', array('id' => $orderid, 'uniacid' => $uniacid));
if(!empty($order)){
    $seller = pdo_get('sbms_seller', array('id' => $order['seller_id'], 'uniacid' => $uniacid));
    $room = pdo_get('sbms_room', array('id' => $order['room_id'], 'uniacid' => $uniacid));
}

include $this->template('order/assess');