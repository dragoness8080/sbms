<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/19
 * Time: 下午4:10
 */
defined('IN_IA') or exit('Access Denied');

class Sbms_Notice{

    public function sendOrderMessage($orderId = '0', $refund = false){
        global $_W;
        if(empty($orderId)){ return;}
        $order = pdo_get('sbms_order', array('id' => $orderId, 'uniacid' => $_W['uniacid']));
        if(empty($order)){  return;}
        $orderUrl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sbms&do=order&p=detail&orderid=' . $orderId;
        if(strexists($orderUrl, '/addons/sbms/')){
            $orderUrl = str_replace('/addons/sbms/', '/', $orderUrl);
        }
        if(strexists($orderUrl, '/mobile/')){
            $orderUrl = str_replace('/mobile/', '/', $orderUrl);
        }
        $openId = $order['openid'];
        $member = pdo_get('sbms_user', array('id' => $order['user_id'], 'uniacid' => $_W['uniacid']));
        $set = m('common')->getSystem();
        if($order['status'] == 2){  //订房通知
            $tid = $set['tid1'];
            $createTime = date('Y-m-d H:i:s', $order['time']);
            $inTime = date('Y-m-d', strtotime($order['arrival_time']));
            $outTime = date('Y-m-d', strtotime($order['departure_time']));
            $message = array('first' => array('value' => '订房通知', 'color' => '#173177'), 'keyword1' => array('value' => $order['seller_name'], 'color' => '#173177'),
                'keyword2' => array('value' => $createTime, 'color' => '#173177'), 'keyword3' => array('value' => $order['price'], 'color' => '#173177'),
                'keyword4' => array('value' => $order['seller_address'], 'color' => '#173177'), 'keyword5' => array('value' => $order['order_no'], 'color' => '#173177'),
                'keyword5' => array('value' => $inTime, 'color' => '#173177'), 'keyword6' => array('value' => $outTime, 'color' => '#173177'),
                'keyword7' => array('value' => $member['name'], 'color' => '#173177'), 'keyword8' => array('value' => $order['room_type'], 'color' => '#173177'));
            if(!empty($tid)){
                m('message')->sendTplNotice($openId,$tid,$message,$orderUrl);
            }else{
                m('message')->sendCustomNotice($openId,$message,$orderUrl);
            }
        }elseif($order['status'] == 5){ //入住通知
            $tid = $set['rz_tid'];
            $seller = pdo_get('sbms_seller', array('id' => $order['seller_id'], 'uniacid' => $_W['uniacid']));
            $address = $seller['province'] . $seller['city'] . $seller['area'] . $seller['address'];
            $inTime = date('Y-m-d', strtotime($order['arrival_time']));
            $message = array('first' => array('value' => '入住通知', 'color' => '#173177'), 'keyword1' => array('value' => $order['seller_name'], 'color' => '#173177'),
                'keyword2' => array('value' => $order['room_type'], 'color' => '#173177'), 'keyword3' => array('value' => $order['name'], 'color' => '#173177'),
                'keyword4' => array('value' => $inTime, 'color' => '#173177'), 'keyword5' => array('value' => $seller['tel'], 'color' => '#173177'), 'keyword6' => array('value' => $address, 'color' => '#173177'));
            if(!empty($tid)){
                m('message')->sendTplNotice($openId,$tid,$message,$orderUrl);
            }else{
                m('message')->sendCustomNotice($openId,$message,$orderUrl);
            }
        }elseif ($order['status'] == 9){ //拒绝入住
            $tid = $set['jjrz_tid'];
            $time = date('Y-m-d H:i:s', $order['jj_time']);
            $message = array('first' => array('value' => '拒绝入住通知', 'color' => '#173177'), 'keyword1' => array('value' => '', 'color' => '#173177'), 'keyword2' => array('value' => $time, 'color' => '#173177'),
                'keyword3' => array('value' => $order['price'], 'color' => '#173177'), 'keyword4' => array('value' => $order['seller_name'], 'color' => '#173177'),
                'keywrod5' => array('value' => $set['tel'], 'color' => '#173177'), 'keyword6' => array('value' => $order['order_no'], 'color' => '#173177'));
            if(!empty($tid)){
                m('message')->sendTplNotice($openId,$tid,$message,$orderUrl);
            }else{
                m('message')->sendCustomNotice($openId,$message,$orderUrl);
            }
        }
    }
}