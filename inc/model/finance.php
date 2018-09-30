<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/13
 * Time: 下午2:48
 */
defined('IN_IA') or exit('Access Denied');

class Sbms_Finance{

    public function isWeixinPay($out_trade_no)
    {
        global $_W, $_GPC;
        $setting = uni_setting($_W['uniacid'], array('payment'));
        if (!is_array($setting['payment'])) {
            return error(1, '没有设定支付参数');
        }
        $wechat = $setting['payment']['wechat'];
        $sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid limit 1';
        $row = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
        $url = 'https://api.mch.weixin.qq.com/pay/orderquery';
        $pars = array();
        $pars['appid'] = $row['key'];
        $pars['mch_id'] = $wechat['mchid'];
        $pars['nonce_str'] = random(8);
        $pars['out_trade_no'] = $out_trade_no;
        ksort($pars, SORT_STRING);
        $string1 = '';
        foreach ($pars as $k => $v) {
            $string1 .= "{$k}={$v}&";
        }
        $string1 .= "key=" . $wechat['apikey'];
        $pars['sign'] = strtoupper(md5($string1));
        $xml = array2xml($pars);
        load()->func('communication');
        $resp = ihttp_post($url, $xml);
        if (is_error($resp)) {
            return error(-2, $resp['message']);
        }
        if (empty($resp['content'])) {
            return error(-2, '网络错误');
        } else {
            $arr = json_decode(json_encode((array)simplexml_load_string($resp['content'])), true);
            $xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
            $dom = new \DOMDocument();
            if ($dom->loadXML($xml)) {
                $xpath = new \DOMXPath($dom);
                $code = $xpath->evaluate('string(//xml/return_code)');
                $ret = $xpath->evaluate('string(//xml/result_code)');
                $trade_state = $xpath->evaluate('string(//xml/trade_state)');
                if (strtolower($code) == 'success' && strtolower($ret) == 'success' && strtolower($trade_state) == 'success') {
                    return true;
                } else {
                    if ($xpath->evaluate('string(//xml/return_msg)') == $xpath->evaluate('string(//xml/err_code_des)')) {
                        $error = $xpath->evaluate('string(//xml/return_msg)');
                    } else {
                        $error = $xpath->evaluate('string(//xml/return_msg)') . "<br/>" . $xpath->evaluate('string(//xml/err_code_des)');
                    }
                    return error(-2, $error);
                }
            } else {
                return error(-1, '未知错误');
            }
        }
    }

    public function isAlipayNotify($gpc){
        global $_W;
        $notify_id = trim($gpc['notify_id']);
        $notify_sign = trim($gpc['sign']);
        if (empty($notify_id) || empty($notify_sign)) {
            return false;
        }
        $setting = uni_setting($_W['uniacid'], array('payment'));
        if (!is_array($setting['payment'])) {
            return false;
        }
        $alipay = $setting['payment']['alipay'];
        $params = array();
        foreach ($gpc as $key => $value) {
            if (in_array($key, array('sign', 'sign_type', 'i', 'm', 'openid', 'c', 'do', 'p', 'op')) || empty($value)) {
                continue;
            }
            $params[$key] = $value;
        }
        ksort($params, SORT_STRING);
        $string1 = '';
        foreach ($params as $k => $v) {
            $string1 .= "{$k}={$v}&";
        }
        $string1 = rtrim($string1, '&') . $alipay['secret'];
        $sign = strtolower(md5($string1));
        if ($notify_sign != $sign) {
            return false;
        }
        $url = "https://mapi.alipay.com/gateway.do?service=notify_verify&partner={$alipay['partner']}&notify_id={$notify_id}";
        $resp = @file_get_contents($url);
        return preg_match('/true$/i', $resp);
    }
}