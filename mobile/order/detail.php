<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/12
 * Time: 下午4:32
 */
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;

$orderId = intval($_GPC['orderid']);

$order = m('order')->getOrderInfo($orderId);

include $this->tempalte('order/detail');