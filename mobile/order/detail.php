<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/12
 * Time: 下午4:32
 */
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;

$orderId = intval($_GPC['orderid']);
$order = m('order')->getOrderInfo($orderId);
if(!empty($order)){
    $order['arrival_time'] = empty($order['arrival_time']) ? '' : date('m月d日', strtotime($order['arrival_time']));
    $order['departure_time'] = empty($order['departure_time']) ? '' : date('m月d日', strtotime($order['departure_time']));
}
if($_W['isajax']){
    if(empty($order)){
        show_json(0);
    }else{
        show_json(1,array('order' => $order));
    }
}
include $this->template('order/detail');