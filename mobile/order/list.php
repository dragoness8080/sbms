<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/13
 * Time: 22:04
 */
defined('IN_IA') or exit('ccess Denied');
global $_W,$_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid = m('user')->getOpenid();
$uniacid = $_W['uniacid'];
$status = intval($_GPC['status']);
if($_W['isajax']){
    if($operation == 'display'){
        $page = max(1,intval($_GPC['page']));
        $pageSize = 5;
        $conditions = " openid=:openid and uniacid=:uniacid and is_delete=0";
        $params = array(':openid' => $openid, ':uniacid' => $uniacid);
        if($status == 0){
            $conditions .= " and status in(1,2,3,4,5,6,7,8,10)";    //10订单确认后，等待入住
        }else{
            $conditions .= " and status=:status";
            $params[':status'] = $status;
        }
        $list = pdo_fetchall("select * from " . tablename('sbms_order') . " where " . $conditions . " order by time desc limit " . ($page - 1) * $pageSize . "," . $pageSize, $params);
        $total = m('order')->getOrderCount($openid,$status);
        foreach ($list as &$item){
            $item['arrival_time'] = empty($item['arrival_time']) ? '' : date('m月d日', strtotime($item['arrival_time']));
            $item['departure_time'] = empty($item['departure_time']) ? '' : date('m月d日', strtotime($item['departure_time']));
            $item['statusStr'] = m('order')->getStatusStr($item['status']);
            $img = pdo_getcolumn('sbms_room', array('id' => $item['room_id']),'logo');
            $item['img'] = tomedia($img);
        }
        unset($item);
        show_json(1, array(
            'total' => $total,
            'list'  => empty($list) ? array() : $list,
            'pagesize'  => $pageSize
        ));
    }
}

include $this->template('order/list');