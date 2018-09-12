<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/9
 * Time: 20:50
 */
defined('IN_IA') or exit('Access Denied');

class Sbms_Member{

    public function getMember($openid){
        global $_W;
        $tmp = intval($openid);
        if(!empty($tmp)){
            $openid = $tmp;
        }
        return pdo_get('sbms_user', array('openid' => $openid, 'uniacid' => $_W['uniacid']));
    }

    public function getLevel($lid){
        global $_W;
        return pdo_get('sbms_level', array('id' => $lid, 'uniacid' => $_W['uniacid']));
    }

}