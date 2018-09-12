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

$levelName = '初始会员';
if($member['type'] == 2){
    $levelArray = m('mmeber')->getLevel($member['level_id']);
    $levelName = $levelArray['name'];
}

$orderAll = m('order')->getOrderCount($openid);
$orderPay = m('order')->getOrderCount($openid, 1);
$orderCheckIn = m('order')->getOrderCount($openid, 5);

if($operation == 'display'){

}

include $this->template('member/center');