<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/10
 * Time: 22:14
 */
defined('IN_IA') or exit('Access Denied');

class Sbms_Order{

    /**
     * 获取订单数量
     * @param $openid
     * @param int $status
     * @return bool|mixed
     */
    public function getOrderCount($openid,$status = -1){
        global $_W;
        $condition['qr_fromid'] = $openid;
        $condition['uniacid'] = $_W['uniacid'];
        if($status > -1) {
            if($status == 5){
                $condition['status'] = array('in', '1,2,3,4');
            }else{
                $condition['status'] = $status;
            }

        }
        return pdo_getcolumn('sbms_order', $condition, 'count(id)');
    }

    /**
     * 获取订单编号
     * @param string $prefix
     * @return string
     */
    public function getOrderSn($prefix = ''){
        $sn = date('YmdHis') . rand(0,9999);
        if(!empty($prefix)){
            $sn = $prefix . $sn;
        }
        return $sn;
    }

    public function getOrderInfo($id){
        global $_W;
        return pdo_get('sbms_order', array('id' => $id, 'uniacid' => $_W['uniacid']));
    }
}