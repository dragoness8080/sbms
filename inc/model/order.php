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
        //$condition['qr_fromid'] = $openid;
        $condition['openid'] = $openid;
        $condition['uniacid'] = $_W['uniacid'];
        $condition['is_delete'] = 0;
        if($status > -1) {
            if($status == 5){
                $condition['status'] = 2;
            }else{
                $condition['status'] = $status;
            }
        }
        return pdo_count('sbms_order', $condition);
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
        $count = pdo_getcolumn('sbms_order', array('order_no' => $sn), 'count(id) as count');
        if($count){
            $sn = $this->getOrderSn($prefix);
        }
        return $sn;
    }

    /**
     * 获取支付单号
     * @param string $prefix
     * @return string
     */
    public function getOutTradeNo($prefix = ''){
        $sn = date('YmdHis') . rand(0,9999);
        if(!empty($prefix)){    $sn = $prefix . $sn;}
        $count = pdo_getcolumn('sbms_order', array('out_trade_no' => $sn), 'count(id) as count');
        if($count){ $sn = $this->getOutTradeNo($prefix);}
        return $sn;
    }

    public function getOrderInfo($id){
        global $_W;
        return pdo_get('sbms_order', array('id' => $id, 'uniacid' => $_W['uniacid']));
    }

    public function getPayLog($out_trade_no){
        global $_W;
        return pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'module' => 'sbms', 'tid' => $out_trade_no));
    }

    public function payResult($params){
        global $_W;
        $out_trade_no = $params['tid'];
        $order = pdo_get('sbms_order', array('out_trade_no' => $out_trade_no, 'uniacid' => $_W['uniacid']));
        $orderid = $order['id'];
        if ($params['from'] == 'return'){
            //到店付款
            if($params['type'] == 'cash'){
                return array(
                    'result' => 'success',
                    'order' => $order
                );
            }else if ($params['type'] == 'cheatfd'){
                return array(
                    'result' => 'success',
                    'order' => $order
                );
            }else{
                if ($order['status'] == 1){
                    pdo_update('sbms_order', array('status' => 2),array('id' => $orderid, 'uniacid' => $_W['uniacid']));
                    $this->setRooms($order['id']);
                    m('notice')->sendOrderMessage($orderid);
                }
                return array(
                    'result' => 'success',
                    'order' => $order
                );
            }
        }
    }

    public function getStatusStr($status){
        $str = '';
        if($status == 1){
            $str = '待支付';
        }elseif ($status == 2){
            $str = '已支付';
        }elseif ($status == 3){
            $str = '已取消';
        }elseif ($status == 4){
            $str = '已完成';
        }elseif ($status == 5){
            $str = '已入住';
        }elseif ($status == 6){
            $str = '申请退款';
        }elseif ($status == 7){
            $str = '已退款';
        }elseif ($status == 8){
            $str = '已拒绝';
        }elseif($status == 10){
            $str = '待入住';
        }
        return $str;
    }

    public function setRooms($orderid){
        global $_W;
        $uniacid = $_W['uniacid'];
        $order = pdo_get('sbms_order', array('id' => $orderid, 'uniacid' => $uniacid));
        $rid = $order['room_id'];
        $start = strtotime($order['arrival_time']);
        $end = strtotime($order['departure_time']);
        $num = $order['num'];
        while ($start < $end){
            $roomnum = pdo_getcolumn('sbms_roomnum', array('rid' => $rid, 'dateday' => $start), 'nums');
            $updata['nums'] = $roomnum - $num;
            pdo_update('sbms_roomnum', $updata,array('rid' => $rid, 'dateday' => $start));
            $start = strtotime('+1 day',$start);
        }
    }
}