<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/13
 * Time: 上午9:43
 */
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$operation = !empty($_GPC["op"]) ? $_GPC["op"] : "display";
$openid = m('user')->getOpenid();
if(empty($openid)){ $openid = $_GPC['openid'];}
$member = m('member')->getMember($openid);
$uniacid = $_W['uniacid'];
$orderid = intval($_GPC['orderid']);
if($operation == 'display' && $_W['isajax']){

}elseif ($operation == 'pay' && $_W['isajax']){
    $order = pdo_get('sbms_order', array('id' => $orderid, 'user_id' => $member['id'], 'uniacid' => $uniacid));
    if(empty($order)){  show_json(0,'未找到订单!');}
    $type = $_GPC['type'];  //支付方式
    if(!in_array($type, array('wechat','alipay'))){
        show_json(0, "未找到支付方式");
    }
    $log = pdo_get('core_paylog', array('uniacid' => $uniacid, 'module' => 'sbms', 'tid' => $order['out_trade_no']));
    if(empty($log)){
        show_json(0, "支付出错,请重试!");
    }
    if($type == 'wechat'){
        if(!is_weixin()){
            show_json(0, "非微信环境!");
        }
        $wechat        = array(
            "success" => false
        );
        $params        = array();
        $params["tid"] = $log["tid"];
        $params["user"]  = $openid;
        $params["fee"]   = $order["price"];
        $params["title"] = $order['name'] . "订单";
        //调用统一的支付接口
        load()->model("payment");
        $setting = uni_setting($_W["uniacid"], array(
            "payment"
        ));
        if (is_array($setting["payment"])) {
            $options           = $setting["payment"]["wechat"];
            $options["appid"]  = $_W["account"]["key"];
            $options["secret"] = $_W["account"]["secret"];
            $wechat            = m("common")->wechat_build($params, $options, 0);
            $wechat["success"] = false;
            if (!is_error($wechat)) {
                $wechat["success"] = true;
            } else {
                show_json(0, $wechat["message"]);
            }
        }
        if (!$wechat["success"]) {
            show_json(0, "微信支付参数错误!");
        }
        pdo_update('sbms_order', array('type' => 2), array('id' => $orderid, 'uniacid' =>$uniacid));
        show_json(1, array(
            "wechat" => $wechat
        ));
    }elseif($type == 'alipay'){
        pdo_update('sbms_order', array('type' => 1), array('id' => $orderid, 'uniacid' => $uniacid));
        show_json(1);
    }
}elseif ($operation == 'complete' && $_W['ispost']){
    $order = pdo_get('sbms_order', array('id' => $orderid, 'uniacid' => $uniacid, 'user_id' => $member['id']));
    if(empty($order)){  show_json(0,'未找到订单!');}
    $type = $_GPC["type"];
    if(!in_array($type, array('wechat','alipay','credit','cash'))){
        show_json(0, "未找到支付方式");
    }
    $log = pdo_get('core_paylog', array('uniacid' => $uniacid, 'module' => 'sbms', 'tid' => $order['out_trade_no']));
    if(empty($log)){
        show_json(0, "支付出错,请重试!");
    }
    if($type == 'cash'){
        $ret            = array();
        $ret["result"]  = "success";
        $ret["type"]    = "cash";
        $ret["from"]    = "return";
        $ret["tid"]     = $log["tid"];
        $ret["user"]    = $order["openid"];
        $ret["fee"]     = $order["price"];
        $ret["uniacid"] = $_W["uniacid"];
        $pay_result     = $this->payResult($ret);
        show_json(1, $pay_result);
    }

    $ps          = array();
    $ps["tid"]   = $log["tid"];
    $ps["user"]  = $openid;
    $ps["fee"]   = $log["fee"];
    if ($type == "credit"){
        $credits = m('member')->getCredit($openid, "credit2");
        if (($credits <=0) || ($ps['fee']<=0)) {
            show_json(0, "支付金额错误或余额为0");
        }
        if ($credits < $ps["fee"]) {
            show_json(0, "余额不足,请充值");
        }
        $fee    = floatval($ps["fee"]);
        $result = m('member')->setCredit($openid, 'credit2', -$fee, array($_W['member']['uid'],'民宿支付消费：' . $fee));
        if(is_error($result)){
            show_json(0, $result["message"]);
        }
        $record           = array();
        $record["status"] = "1";
        $record["type"]   = "cash";
        pdo_update('core_paylog', $record, array("plid" => $log["plid"]));
        pdo_update('sbms_order', array('type' => 3), array('id' => $order['id'], 'uniacid' => $uniacid));
        $ret            = array();
        $ret["result"]  = "success";
        $ret["type"]    = $log["type"];
        $ret["from"]    = "return";
        $ret["tid"]     = $log["tid"];
        $ret["user"]    = $log["openid"];
        $ret["fee"]     = $log["fee"];
        $ret["uniacid"] = $log["uniacid"];
        $pay_result     = $this->payResult($ret);
        show_json(1, $pay_result);
    }elseif ($type == 'wechat'){
        $out_trade_no = $order["out_trade_no"];
        $payquery = m("finance")->isWeixinPay($out_trade_no);
        if (!is_error($payquery)){
            $record           = array();
            $record["status"] = "1";
            $record["type"]   = "wechat";
            pdo_update('core_paylog',$record,array("plid" => $log["plid"]));
            $ret            = array();
            $ret["result"]  = "success";
            $ret["type"]    = "wechat";
            $ret["from"]    = "return";
            $ret["tid"]     = $log["tid"];
            $ret["user"]    = $log["openid"];
            $ret["fee"]     = $log["fee"];
            $ret["weid"]    = $log["weid"];
            $ret["uniacid"] = $log["uniacid"];
            $pay_result     = $this->payResult($ret);
            show_json(1, $pay_result);
        }
        show_json(0, "支付出错,请重试!");
    }

}elseif ($operation == 'notify'){   //alipay
    $tid = $_GPC["out_trade_no"];
    if (!m("finance")->isAlipayNotify($_GET)) {
        die("支付出现错误，请重试!");
    }
    $log = m('order')->getPayLog($tid);
    if(empty($log)){    die("支付出现错误，请重试!");}
}elseif ($operation == 'return'){
    $tid = $_GPC["out_trade_no"];
    if (!m("finance")->isAlipayNotify($_GET)) {
        die("支付出现错误，请重试!");
    }
    $log = m('order')->getPayLog($tid);
    if(empty($log)){    die("支付出现错误，请重试!");}
    if($log['status'] != 1){
        $record           = array();
        $record["status"] = "1";
        $record["type"]   = "alipay";
        pdo_update('core_paylog', $record, array("plid" => $log["plid"]));
        $ret            = array();
        $ret["result"]  = "success";
        $ret["type"]    = "alipay";
        $ret["from"]    = "return";
        $ret["tid"]     = $log["tid"];
        $ret["user"]    = $log["openid"];
        $ret["fee"]     = $log["fee"];
        $ret["uniacid"] = $log["uniacid"];
        $this->payResult($ret);
        $url     = $this->createMobileUrl("order/detail", array("id" => $orderid));
        die("<script>top.window.location.href='{$url}'</script>");
    }
}