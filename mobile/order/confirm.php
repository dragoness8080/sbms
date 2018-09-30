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
    $allmoney = 0;
    $levelmoney = $_GPC['levelmoney'];
    $payment = $_GPC['payment'];
    $num = $_GPC['num'];
    $couponid = intval($_GPC['couponid']);

    $member = m('member')->getMember($openid);
    $room = m('seller')->getRoom($rid);
    $seller = m('seller')->getSeller($room['seller_id']);

    $sn = m('order')->getOrderSn('MS');
    $out_trade_no = m('order')->getOutTradeNo('MSZF');

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

        $price = pdo_getcolumn('sbms_roomprice', array('rid' => $rid, 'dateday' => $start), 'mprice');
        empty($price) && $price = $room['price'];
        $allmoney += $price * $num; //所有订的房间的价格总和

        $start = strtotime('+1 day',$start);
    }

    $deposit = $room['yj_cost'] * $num; //押金
    $couponMoney = 0;
    if($couponid > 0){  //优惠券
        $couponMoney = pdo_getcolumn('sbms_coupons', array('id' => $couponid,'uniacid' => $_W['uniacid']),'cost');
    }

    $ordermoney = $allmoney + $deposit - $couponMoney - $levelmoney;

    $order['openid'] = $openid;
    $order['user_id'] = $member['id'];
    $order['seller_id'] = $room['seller_id'];
    $order['room_id'] = $rid;
    $order['order_no'] = $sn;
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
    $order['total_cost'] = $allmoney + $deposit;
    $order['num'] = $num;
    $order['bed_type'] = $room['size'];
    $order['uniacid'] = $_W['uniacid'];
    $order['days'] = m('common')->getDays($dt_start,$dt_end);
    $order['dis_cost'] = 0; //折扣价格
    $order['yhq_cost'] = $couponMoney; //优惠券
    $order['hb_id'] = 0; //积分红包ID
    $order['hb_cost'] = 0; //积分红包金额
    $order['yyzk_cost'] = 0;
    $order['yj_cost'] = $deposit;
    $order['room_logo'] = $room['logo'];
    $order['out_trade_no'] = $out_trade_no;
    $order['from_id'] = 0;  //来源？
    $order['qr_fromid'] = 0; //关注二维码来源?
    $order['coupons_id'] = $couponid; //优惠券ID
    $order['type'] = $payment; //支付类型

    if(!empty($msg)){
        show_json(0,$msg);
    }else{
        pdo_insert('sbms_order', $order);
        $orderId = pdo_insertid();
        //添加支付日志
        $paylog = array(
            'uniacid' => $_W['uniacid'],
            'openid'  => $member['id'],
            'module'  => 'sbms',
            'tid'     => $out_trade_no,
            'fee'     => $ordermoney,
            'status'  => 0
        );
        pdo_insert("core_paylog", $paylog);
        if($couponid > 0){  //优惠券
            pdo_update('sbms_usercoupons', array(
                'state' => 2,
                'sy_time' => time()
            ), array('user_id' => $member['id'], 'coupons_id' => $couponid, 'uniacid' => $_W['uniacid']));
        }
        show_json(1, array('orderid' => $orderId, 'ordersn' => $order['order_no'], 'payment' => $payment));
    }
}