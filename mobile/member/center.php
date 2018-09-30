<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/9
 * Time: 16:29
 */
defined('IN_IA') or exit('Access Denied');

global $_W,$_GPC;

$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];

$openid = m('user')->getOpenid();

$member = m('member')->getMember($openid);

if($operation == 'display'){
    $levelName = '初始会员';
    if($member['type'] == 2){
        $levelArray = m('mmeber')->getLevel($member['level_id']);
        $levelName = $levelArray['name'];
    }

    $orderAll = m('order')->getOrderCount($openid);
    $orderPay = m('order')->getOrderCount($openid, 1);
    $orderCheckIn = m('order')->getOrderCount($openid, 2);

    //余额
    $credit2 = m('member')->getCredit($openid,'credit2');
    //积分
    $credit1 = m('member')->getCredit($openid, 'credit1');
    //优惠券
    $coupons = m('member')->coupons($openid);

    include $this->template('member/center');
}elseif ($operation == 'custom'){
    include $this->template('member/custom');
}


