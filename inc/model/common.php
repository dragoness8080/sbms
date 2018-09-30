<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/8/24
 * Time: 下午2:44
 */
defined('IN_IA') or exit('Access Denied');

class Sbms_Common{

    /**
     * 获取民宿信息
     * @param $id
     * @return bool
     */
    public function getHouseForId($id){
        global $_W;

        $house = pdo_get('sbms_seller', array('id' => $id, 'uniacid' => $_W['uniacid']));
        return $house;
    }

    /**
     * 判断是否有房间
     * @param $rid
     * @param $start
     * @param $end
     * @return bool
     */
    public function checkRooms($rid,$start,$end){
        $res = true;
        if(empty($rid)){    return false;}
        if(empty($start)){  return false;}
        $start = strtotime($start);
        $end = strtotime($end);
        if($start > $end){  return false;}
        while ($start < $end){
            $nums = pdo_getcolumn('sbms_roomnum', array('rid' => $rid,'dateday' => $start),'nums');
            if(empty($nums)){
                $res = false;
            }
            $start = strtotime('+1 day', $start);
        }
        return $res;
    }

    /**
     * 获取当前日期的优惠价
     * @param $rid
     * @param $day
     * @return bool|int|mixed
     */
    public function getRoomPrice($rid,$day){
        $day = strtotime($day);
        $price = pdo_getcolumn('sbms_roomprice', array('rid' => $rid, 'dateday' => $day), 'mprice');
        return empty($price) ? 0 : $price;
    }

    /**
     * 获取天数
     * @param $start
     * @param $end
     * @return float
     */
    public function getDays($start,$end){
        $start = strtotime($start);
        $end = strtotime($end);
        $days = floor(($end - $start) / 3600 / 24);
        return $days;
    }

    public function alipay_build($params, $alipay = array(), $type = 0, $openid = '')
    {
        global $_W;
        $tid                   = $params['tid'];
        $set                   = array();
        $set['service']        = 'alipay.wap.create.direct.pay.by.user';
        $set['partner']        = $alipay['partner'];
        $set['_input_charset'] = 'utf-8';
        $set['sign_type']      = 'MD5';
        if (empty($type)) {
            $set['notify_url'] = $_W['siteroot'] . "addons/sbms/payment/alipay/notify.php";
            $set['return_url'] = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sbms&do=order&p=pay&op=return&openid=" . $openid;
        } else {
            $set['notify_url'] = $_W['siteroot'] . "addons/sbms/payment/alipay/notify.php";
            $set['return_url'] = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sbms&do=member&p=recharge&op=return&openid=" . $openid;
        }
        $set['out_trade_no'] = $tid;
        $set['subject']      = $params['title'];
        $set['total_fee']    = $params['fee'];
        $set['seller_id']    = $alipay['account'];
        $set['payment_type'] = 1;
        $set['body']         = $_W['uniacid'] . ':' . $type;
        $prepares            = array();
        foreach ($set as $key => $value) {
            if ($key != 'sign' && $key != 'sign_type') {
                $prepares[] = "{$key}={$value}";
            }
        }
        sort($prepares);
        $string = implode($prepares, '&');
        $string .= $alipay['secret'];
        $set['sign'] = md5($string);
        return array(
            'url' => ALIPAY_GATEWAY . '?' . http_build_query($set, '', '&')
        );
    }

    public function wechat_build($params, $wechat, $type = 0){
        global $_W;
        load()->func('communication');
        if (empty($wechat['version']) && !empty($wechat['signkey'])) {
            $wechat['version'] = 1;
        }
        $wOpt = array();
        if ($wechat['version'] == 1) {
            $wOpt['appId']               = $wechat['appid'];
            $wOpt['timeStamp']           = TIMESTAMP . "";
            $wOpt['nonceStr']            = random(8) . "";
            $package                     = array();
            $package['bank_type']        = 'WX';
            $package['body']             = urlencode($params['title']);
            $package['attach']           = $_W['uniacid'] . ':' . $type;
            $package['partner']          = $wechat['partner'];
            $package['device_info']      = "sbms";
            $package['out_trade_no']     = $params['tid'];
            $package['total_fee']        = $params['fee'] * 100;
            $package['fee_type']         = '1';
            $package['notify_url']       = $_W['siteroot'] . "addons/sbms/payment/wechat/return.php";
            $package['spbill_create_ip'] = CLIENT_IP;
            $package['input_charset']    = 'UTF-8';
            ksort($package);
            $string1 = '';
            foreach ($package as $key => $v) {
                if (empty($v)) {
                    continue;
                }
                $string1 .= "{$key}={$v}&";
            }
            $string1 .= "key={$wechat['key']}";
            $sign    = strtoupper(md5($string1));
            $string2 = '';
            foreach ($package as $key => $v) {
                $v = urlencode($v);
                $string2 .= "{$key}={$v}&";
            }
            $string2 .= "sign={$sign}";
            $wOpt['package'] = $string2;
            $string          = '';
            $keys            = array(
                'appId',
                'timeStamp',
                'nonceStr',
                'package',
                'appKey'
            );
            sort($keys);
            foreach ($keys as $key) {
                $v = $wOpt[$key];
                if ($key == 'appKey') {
                    $v = $wechat['signkey'];
                }
                $key = strtolower($key);
                $string .= "{$key}={$v}&";
            }
            $string           = rtrim($string, '&');
            $wOpt['signType'] = 'SHA1';
            $wOpt['paySign']  = sha1($string);
            return $wOpt;
        } else {
            $package                     = array();
            $package['appid']            = $wechat['appid'];
            $package['mch_id']           = $wechat['mchid'];
            $package['nonce_str']        = random(8) . "";
            $package['body']             = $params['title'];
            $package['device_info']      = "sbms";
            $package['attach']           = $_W['uniacid'] . ':' . $type;
            $package['out_trade_no']     = $params['tid'];
            $package['total_fee']        = $params['fee'] * 100;
            $package['spbill_create_ip'] = CLIENT_IP;
            $package['notify_url']       = $_W['siteroot'] . "addons/sbms/payment/wechat/return.php";
            $package['trade_type']       = 'JSAPI';
            $package['openid']           = $_W['fans']['from_user'];
            ksort($package, SORT_STRING);
            $string1 = '';
            foreach ($package as $key => $v) {
                if (empty($v)) {
                    continue;
                }
                $string1 .= "{$key}={$v}&";
            }
            $string1 .= "key={$wechat['signkey']}";
            $package['sign'] = strtoupper(md5($string1));
            $dat             = array2xml($package);
            $response        = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);
            if (is_error($response)) {
                return $response;
            }
            $xml = @simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
            if (strval($xml->return_code) == 'FAIL') {
                return error(-1, strval($xml->return_msg));
            }
            if (strval($xml->result_code) == 'FAIL') {
                return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
            }
            $prepayid          = $xml->prepay_id;
            $wOpt['appId']     = $wechat['appid'];
            $wOpt['timeStamp'] = TIMESTAMP . "";
            $wOpt['nonceStr']  = random(8) . "";
            $wOpt['package']   = 'prepay_id=' . $prepayid;
            $wOpt['signType']  = 'MD5';
            ksort($wOpt, SORT_STRING);
            $string = '';
            foreach ($wOpt as $key => $v) {
                $string .= "{$key}={$v}&";
            }
            $string .= "key={$wechat['signkey']}";
            $wOpt['paySign'] = strtoupper(md5($string));
            return $wOpt;
        }
    }

    /**
     * 判断优惠券是否可用
     * @param $start
     * @param $end
     * @return int
     */
    public function checkCouponUsed($start,$end){
        $now = strtotime(date('Y-m-d', time()));
        $end = strtotime($end);
        $start = strtotime($start);
        if($now > $end){
            $isused = 0;
        }elseif ($now == $end){
            $isused = 1;
        }elseif ($now < $end && $now >= $start){
            $isused = 2;
        }elseif ($now < $start){
            $isused = 3;
        }
        return $isused;
    }

    public function getSystem(){
        global $_W;
        return pdo_get('sbms_system', array('uniacid' => $_W['uniacid']));
    }

    public function getAccount()
    {
        global $_W;
        load()->model('account');
        if (!empty($_W['acid'])) {
            return WeAccount::create($_W['acid']);
        } else {
            $acid = pdo_fetchcolumn("SELECT acid FROM " . tablename('account_wechats') . " WHERE `uniacid`=:uniacid LIMIT 1", array(
                ':uniacid' => $_W['uniacid']
            ));
            return WeAccount::create($acid);
        }
        return false;
    }

    public function distance($latitude,$longitude){
        global $_W;
        $earth = 6371229.0; //弧长乘地球半径
        $lat = $_COOKIE['latitude'];
        $lon = $_COOKIE['longitude'];
        $x = ($longitude - $lon) * M_PI * $earth * cos((($latitude + $lat) / 2) * M_PI / 180) / 180;
        $y = ($latitude - $lat) * M_PI * $earth / 180;
        $s = sqrt($x * $x + $y * $y);
        return round($s,2);
    }

    public function checkClose(){
        if (strexists($_SERVER['REQUEST_URI'], '/web/')) {
            return;
        }

        $shop = $this->getSystem();
        if (!empty($shop['close'])){
            if (!empty($shop['closeurl'])) {
                header('location: ' . $shop['closeurl']);
                exit;
            }
            die("<!DOCTYPE html>
					<html>
						<head>
							<meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'>
							<title>抱歉，民宿暂时关闭</title><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'><link rel='stylesheet' type='text/css' href='https://res.wx.qq.com/connect/zh_CN/htmledition/style/wap_err1a9853.css'>
						</head>
						<body>
						<style type='text/css'>
						body { background:#fbfbf2; color:#333;}
						img { display:block; width:100%;}
						.header {
						width:100%; padding:10px 0;text-align:center;font-weight:bold;}
						</style>
						<div class='page_msg'>
						
						<div class='inner'><span class='msg_icon_wrp'><i class='icon80_smile'></i></span>{$shop['closedetail']}</div></div>
						</body>
					</html>");
        }
    }
}