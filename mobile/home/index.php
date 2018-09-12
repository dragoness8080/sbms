<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/8/20
 * Time: 上午10:19
 */
defined('IN_IA') or exit("Access Denied");

global $_W,$_GPC;

$openid = $_W['openid'];

$member = pdo_get('sbms_user', array('uniacid'=>$_W['uniacid'], 'openid'=>$openid));
if(empty($member)){
    $fans = pdo_get('mc_mapping_fans', array('uniacid'=>$_W['uniacid'],'openid'=>$openid));
    if(!empty($fans)){
        $user = pdo_get('mc_members', array('uniacid'=>$_W['uniacid'],'uid'=>$fans['uid']), array('mobile','realname','nickname','avatar'));
        $member = array(
            'name' => $user['nickname'],
            'uniacid' => $_W['uniacid'],
            'openid' => $openid,
            'join_time' => time(),
            'img' => $user['avatar'],
            'tel' => $user['mobile'],
            'zs_name' => $user['realname']
        );
        pdo_insert('sbms_user', $member);
    }
}


include $this->template('home/index');