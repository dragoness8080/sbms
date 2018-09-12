<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/12
 * Time: 下午2:59
 */
defined('IN_IA') or exit('Access Denied');

class Sbms_Seller{

    /**
     * 获取民宿信息
     * @param $id
     * @return bool
     */
    public function getSeller($id){
        global $_W;
        return pdo_get('sbms_seller', array('id' => $id, 'uniacid' => $_W['uniacid']));
    }

    public function getRoom($id){
        global $_W;
        return pdo_get('sbms_room', array('id' => $id, 'uniacid' => $_W['uniacid']));
    }

    public function getRoomNum($id, $dateday){
        return pdo_get('sbms_roomnum', array('rid' => $id, 'dateday' => $dateday));
    }
}