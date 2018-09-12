<?php
/**
 * 志汇酒店营销版模块定义
 *
 * @author 武汉志汇科技
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class SbmsModule extends WeModule {

	public function welcomeDisplay()

    {   
    	global $_GPC, $_W;

        $url = $this->createWebUrl('ptdata');
        if ($_W['role'] == 'operator') {
        	$url = $this->createWebUrl('hotel');
        }

        Header("Location: " . $url);

    }

}