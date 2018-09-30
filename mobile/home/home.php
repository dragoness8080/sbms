<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/8/21
 * Time: 下午2:13
 */
defined('IN_IA') or exit("Access Denied");

global $_W,$_GPC;

$curDate = date('Y-m-d', time());
$nextDate = date('Y-m-d', strtotime('+1 day'));

$days = (strtotime($nextDate) - strtotime($curDate)) / 24 / 3600;

$curDateArr = explode('-', $curDate);
$nextDateArr = explode('-', $nextDate);

$adv = pdo_get('sbms_ad', array('uniacid' => $_W['uniacid'], 'status' => 1, 'type' => 1));


include $this->template('home/home');