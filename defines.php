<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/8/24
 * Time: 下午2:29
 */
defined('IN_IA') or exit('Access Denied');

!defined('SBMS_PATH') && define('SBMS_PATH', IA_ROOT . '/addons/sbms/');
!defined('SBMS_PATH_INC') && define('SBMS_PATH_INC', SBMS_PATH . 'inc/');
!defined('SBMS_URL') && define('SBMS_URL', $_W['siteroot'] . 'addons/sbms/');
!defined('SBMS_PATH_FUNC') && define('SBMS_PATH_FUNC', SBMS_PATH_INC . 'func/');
!defined('SBMS_STATIC') && define('SBMS_STATIC', SBMS_PATH . 'static/');