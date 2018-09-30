<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/20
 * Time: 下午3:33
 */
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$seller = pdo_get('sbms_seller', array('user_id' => $member['id'], 'uniacid' => $_W['uniacid']));
if($_W['isposst']){

}
include $this->template('member/seller');