<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/13
 * Time: 下午2:56
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid    = m('user')->getOpenid();
if (empty($openid)){ $openid = $_GPC['openid'];}
$member  = m('member')->getMember($openid);
$uniacid = $_W['uniacid'];
$orderid = intval($_GPC['orderid']);
if ($_W['isajax']){
    if (!empty($orderid)){
        $order = pdo_get('sbms_order', array('id' => $orderid, 'uniacid' => $uniacid, 'user_id' => $member['id']));
        if (empty($order)){ show_json(0, '订单未找到!');}
        $log = pdo_get('core_paylog', array('uniacid' => $uniacid, 'module' => 'sbms', 'tid' => $order['out_trade_no']));
        if (!empty($log) && $log['status'] != '0'){
            show_json(0, '订单已支付, 无需重复支付!');
        }
        $param_title     = $order['seller_name'] . "订单: " . $order['order_no'];
        $alipay          = array(
            'success' => false
        );
        $params          = array();
        $params['tid']   = $log['tid'];
        $params['user']  = $openid;
        $params['fee']   = $order['price'];
        $params['title'] = $param_title;
        load()->func('communication');
        load()->model('payment');
        $setting = uni_setting($_W['uniacid'], array(
            'payment'
        ));
        if (is_array($setting['payment'])) {
            $options = $setting['payment']['alipay'];
            $alipay  = m('common')->alipay_build($params, $options, 0, $openid);
            if (!empty($alipay['url'])) {
                $alipay['success'] = true;
            }
        }
    }
}
include $this->template('order/pay_alipay');