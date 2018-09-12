<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/30
 * Time: 20:20
 */
defined('IN_IA') or exit('Access Denied');

global $_W,$_GPC;
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];

$dt_start = empty($_GPC['dt_start']) ? date('Y-m-d') : $_GPC['dt_start'];
$dt_end = empty($_GPC['dt_end']) ? date('Y-m-d', strtotime("+1 day")) : $_GPC['dt_end'];
$hotalId = intval($_GPC['hid']);    //民宿ID
$days = floor((strtotime($dt_end) - strtotime($dt_start)) / 3600 / 24);

$house = m('common')->getHouseForId($hotalId);

if($_W['isajax']){

}

include $this->template('house/hotal');