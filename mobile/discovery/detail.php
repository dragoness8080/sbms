<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/22
 * Time: 上午10:33
 */
defined('IN_IA') or exit("Access Denied");
global $_W,$_GPC;
$openid = m('user')->getOpenid();
$id = intval($_GPC['id']);
$discovery = pdo_get('sbms_discovery', array('id' => $id, 'uniacid' => $_W['uniacid']));
$count = pdo_count('sbms_discovery_hand', array('openid' => $openid, 'uniacid' => $_W['uniacid'], 'types' => 'browse', 'cid' => $id));
if(empty($count)){
    $update['browse'] = $discovery['browse'] + 1;
    pdo_update('sbms_discovery', $update, array('id' => $id, 'uniacid' => $_W['uniacid']));
    pdo_insert('sbms_discovery_hand', array('uniacid' => $_W['uniacid'], 'cid' => $id, 'openid' => $openid, 'createtime' => time(), 'types' => 'browse'));
}

include $this->template('discovery/detail');