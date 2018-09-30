<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/12
 * Time: 23:03
 */
defined('IN_IA') or exit('Access Denied');

global $_W,$_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid = m('user')->getOpenid();
$uniacid = $_W['uniacid'];
$orderid = intval($_GPC['orderid']);
$order = pdo_get('sbms_order', array('id' => $orderid, 'uniacid' => $uniacid));
$order['arrival_time'] = empty($order['arrival_time']) ? '' : date('m月d日', strtotime($order['arrival_time']));
$order['departure_time'] = empty($order['departure_time']) ? '' : date('m月d日', strtotime($order['departure_time']));
if ($_W['isajax']){
    if ($operation == 'cancel'){
        if (empty($order)) {
            show_json(0, '订单未找到!');
        }
        if ($order['status'] > 1) {
            show_json(0, '订单已支付，不能取消!');
        }
        pdo_update('sbms_order', array('status' => 3), array('id' => $order['id'], 'uniacid' => $uniacid));
        $order['status'] = 3;
        //$order['arrival_time'] = empty($order['arrival_time']) ? '' : date('m月d日', strtotime($order['arrival_time']));
        //$order['departure_time'] = empty($order['departure_time']) ? '' : date('m月d日', strtotime($order['departure_time']));
        //是否发送信息

        //返回确定房间
        $rid = $order['room_id'];
        $num = $order['num'];
        $start = strtotime($order['arrival_time']);
        $end = strtotime($order['departure_time']);
        while ($start < $end){
            $roomnum = pdo_getcolumn('sbms_roomnum', array('rid' => $rid, 'dateday' => $start), 'nums');
            $update['nums'] = $roomnum + $num;
            pdo_update('sbms_roomnum', $update, array('rid' => $rid, 'dateday' => $start));
            $start = strtotime('+1 day', $start);
        }

        if($order['coupons_id'] > 0){   //优惠券
            pdo_update('sbms_usercoupons', array('state' => 1), array('user_id' => $order['user_id'], 'coupons_id' => $order['coupons_id'], 'uniacid' => $_W['uniacid']));
        }
        if($order['hb_id'] > 0){    //积分红包

        }
        show_json(1, array('order' => $order));
    }elseif ($operation == 'delete'){
        if (empty($order)) {
            show_json(0, '订单未找到!');
        }
        if ($order['status'] != 3) {
            show_json(0, '订单没有取消，不能删除!');
        }
        pdo_update('sbms_order', array('is_delete' => 1), array('id' => $order['id'], 'uniacid' => $uniacid));
        show_json(1);
    }elseif ($operation == 'refund'){
        $orderid = intval($_GPC['orderid']);
        $order = pdo_get('sbms_order', array('id' => $orderid, 'uniacid' => $uniacid));
        if (empty($order)) {
            show_json(0, '订单未找到!');
        }
        if ($order['status'] == 1) {
            show_json(0, '订单未支付，不能退款!');
        }else{
            if(in_array($order['status'], array(3,4,5,8))){
                show_json(0, '此订单不允许退款!');
            }elseif(in_array($order['status'], array(6,7))){
                show_json(0, '此订单正在退款或已退款!');
            }else{
                pdo_update('sbms_order', array('status' => 6), array('id' => $orderid, 'uniacid' => $uniacid));
                $order['status'] = 6;
                show_json(1, array('order' => $order));
            }
        }
    }elseif($operation == 'assess'){
        if (empty($order)) {
            show_json(0, '订单未找到!');
        }
        if ($order['status'] != 5) {
            show_json(0, '酒店未入住，不能评价!');
        }
        $member = m('member')->getMember($openid);
        $level = $_GPC['level'];
        $content = $_GPC['content'];
        $images = $_GPC['images'];
        $assess = pdo_get('sbms_assess', array('seller_id' => $order['seller_id'], 'user_id' => $member['id'], 'uniacid' => $uniacid));
        if(!empty($assess)){
            show_json(0,'订单已评价!');
        }
        $comment = array('seller_id' => $order['seller_id'], 'score' => $level, 'content' => $content, 'img' => $images, 'time' => time(), 'user_id' => $member['id'], 'uniacid' => $uniacid);
        pdo_insert('sbms_assess', $comment);
        $assessId = pdo_insertid();
        pdo_update('sbms_order', array('status' => 4), array('id' => $orderid, 'uniacid' => $uniacid));
        $score = array('user_id' => $member['id'], 'order_id' => $order['id'], 'assess_id' => $assessId, 'score' => $level, 'note' => '评价所得', 'time' => time(), 'uniacid' => $uniacid);
        pdo_insert('sbms_score', $score);   //积分明细表
        show_json(1);
    }
}