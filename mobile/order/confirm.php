<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/12
 * Time: 下午2:35
 */
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$openid = m('user')->getOpenid();

if($_W['isajax']){

    $rid = intval($_GPC['rid']);
    $dt_start = $_GPC['dt_start'];
    $dt_end = $_GPC['dt_end'];
    $realname = $_GPC['realname'];
    $tel = $_GPC['tel'];
    $idcard = $_GPC['idcard'];
    $time = $_GPC['time'];
    $allmoney = $_GPC['allmoney'];
    $discount = $_GPC['discount'];
    $levelmoney = $_GPC['levelmoney'];
    $ordermoney = $_GPC['ordermoney'];
    $payment = $_GPC['payment'];
    $num = $_GPC['num'];

    $member = m('member')->getMember($openid);
    $room = m('seller')->getRoom($rid);
    $seller = m('seller')->getSeller($room['seller_id']);

    $order['user_id'] = $member['id'];
    $order['seller_id'] = $room['seller_id'];
    $order['room_id'] = $rid;
    $order['order_no'] = m('order')->getOrderSn('ms');
    $order['status'] = 1;
    $order['time'] = time();
    $order['price'] = $ordermoney;
    $order['seller_name'] = $seller['name'];
    $order['seller_address'] = $seller['address'];
    $order['coordinates'] = $seller['coordinates'];
    $order['arrival_time'] = $dt_start;
    $order['departure_time'] = $dt_end;
    $order['dd_time'] = $time;
    $order['tel'] = $tel;
    $order['name'] = $realname;
    $order['room_type'] = $room['name'];
    $order['total_cost'] = $allmoney;
    $order['num'] = $num;
    $order['bed_type'] = $room['size'];
    $order['uniacid'] = $_W['uniacid'];
    $order['days'] = m('common')->getDays($dt_start,$dt_end);
    $order['dis_cost'] = $allmoney; //折扣价格
    $order['yhq_cost'] = $allmoney; //优惠价格
    $order['hb_cost'] = 0;
    $order['yyzk_cost'] = 0;
    $order['yj_cost'] = 0;
    $order['room_logo'] = $room['img'];
    $order['out_trade_no'] = $order['order_no'];
    $order['from_id'] = 0;  //来源？
    $order['qr_fromid'] = 0; //关注二维码来源?
    $order['coupons_id'] = 0; //优惠券ID
    $order['type'] = $payment; //支付类型

    $start = strtotime($dt_start);
    $end = strtotime($dt_end);
    $msg = '';
    while ($start < $end){
        $dateday = $start;
        $res = m('seller')->getRoomNum($rid,$dateday);
        $surplus = $res['nums'];
        if(!$res['id']){
            $surplus = $room['total_num'];
        }
        if($num - $surplus > 0){
            if($surplus ==  0){
                $msg .= date('m月d日',$start) . " 已经没有房间了!";
            }else{
                $msg .= date('m月d日',$dateday) . '只剩下' . $surplus . '间房';
            }
        }
        $start = strtotime('+1 day',$start);
    }
    if(!empty($msg)){
        show_json(0,$msg);
    }else{
        pdo_insert('sbms_order', $order);
        $orderId = pdo_insertid();
        show_json(1, array('orderid' => $orderId, 'ordersn' => $order['order_no'], 'payment' => $payment));
    }
}