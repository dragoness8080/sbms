<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/8/20
 * Time: 上午10:19
 */
defined('IN_IA') or exit("Access Denied");

global $_W,$_GPC;

$openid = $_W['openid'];

include $this->template('home/index');