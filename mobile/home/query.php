<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/8/24
 * Time: 下午4:43
 */
defined('IN_IA') or exit('Access Denied');

global $_W,$_GPC;

if($_W['isajax']){
    $latitude = $_GPC['latitude']; //纬度
    $longitude = $_GPC['longitude']; //经度
    $speed = $_GPC['speed']; //速度
    $accuracy = $_GPC['accuracy']; //精度

    $_W['latitude'] = $latitude;
    $_W['$longitude'] = $longitude;
    $_W['accuracy'] = $accuracy;

    show_json(1);
}