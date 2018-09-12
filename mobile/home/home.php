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

$curDateArr = explode('-', $curDate);
$nextDateArr = explode('-', $nextDate);

include $this->template('home/home');