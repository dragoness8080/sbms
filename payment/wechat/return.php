<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/15
 * Time: 下午3:24
 */
define('IN_MOBILE', true);
require '../../../../framework/bootstrap.inc.php';
global $_W, $_GPC;
$input = file_get_contents('php://input');
$isxml = true;
if (!empty($input) && empty($_GET['out_trade_no'])){
    $obj = isimplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
    $res = $data = json_decode(json_encode($obj), true);
    if (empty($data)) {
        exit('fail');
    }
    if ($data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS') {
        exit('fail');
    }
    $get = $data;
}else{
    $get = $_GET;
}
$strs          = explode(':', $get['attach']);
$_W['uniacid'] = $_W['weid'] = intval($strs[0]);
$type          = intval($strs[1]);
$total_fee = $get['total_fee'] / 100;
if ($type == 0) {
    $paylog = "\r\n-------------------------------------------------\r\n";
    $paylog .= "orderno: " . $get['out_trade_no'] . "\r\n";
    $paylog .= "paytype: wechat\r\n";
    $paylog .= "data: " . json_encode($_POST) . "\r\n";
    m('common')->paylog($paylog);
}
$setting = uni_setting($_W['uniacid'], array(
    'payment'
));
if (is_array($setting['payment'])){
    $wechat = $setting['payment']['wechat'];
    if (!empty($wechat)){
        ksort($get);
        $string1 = '';
        foreach ($get as $k => $v) {
            if ($v != '' && $k != 'sign') {
                $string1 .= "{$k}={$v}&";
            }
        }
        $wechat['signkey'] = ($wechat['version'] == 1) ? $wechat['key'] : $wechat['signkey'];
        if ($sign == $get['sign']){
            if (empty($type)){
                $tid = $get['out_trade_no'];
                $log = pdo_get('core_paylog', array('module' => 'sbms', 'tid' => $tid));
                if (!empty($log) && $log['status'] == '0' && $log['fee'] == $total_fee){
                    $site = WeUtility::createModuleSite($log['module']);
                    if (!is_error($site)){
                        $method = 'payResult';
                        if (method_exists($site, $method)){
                            $ret            = array();
                            $ret['weid']    = $log['weid'];
                            $ret['uniacid'] = $log['uniacid'];
                            $ret['result']  = 'success';
                            $ret['type']    = $log['type'];
                            $ret['from']    = 'return';
                            $ret['tid']     = $log['tid'];
                            $ret['user']    = $log['openid'];
                            $ret['fee']     = $log['fee'];
                            $ret['tag']     = $log['tag'];
                            $result         = $site->$method($ret);
                            if (is_array($result) && $result['result'] == 'success'){
                                $log['tag']                   = iunserializer($log['tag']);
                                $log['tag']['transaction_id'] = $get['transaction_id'];
                                $record                       = array();
                                $record['status']             = '1';
                                $record['tag']                = iserializer($log['tag']);
                                pdo_update('core_paylog', $record, array(
                                    'plid' => $log['plid']
                                ));
                                exit('success');
                            }
                        }
                    }
                }
            }elseif ($type == 1){

            }
        }
    }
}