<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/20
 * Time: 下午4:20
 */
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
if($_W['isajax']){
    $update = array(
        'tel' => $_GPC['tel'],
        'zs_name' => $_GPC['realname'],
        'idcard' => $_GPC['idcard']
    );
    pdo_update('sbms_user', $update, array('openid' => $openid, 'uniacid' => $_W['uniacid']));
    show_json(1);
}

include $this->template('member/detail');