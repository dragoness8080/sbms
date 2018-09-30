<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/18
 * Time: 上午9:09
 */
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$uniacid = $_W['uniacid'];
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
if($_W['isajax']){
    if($operation == 'coupons'){
        $sellerId = intval($_GPC['hotal_id']);
        $time = strtotime(date('Y-m-d',time()));
        $sql = "select c.id,c.seller_id,c.name,c.start_time,c.end_time,c.conditions,c.cost from " . tablename('sbms_usercoupons') . " u left join " . tablename('sbms_coupons') . " c on u.coupons_id=c.id where "
            . "u.user_id=:uid and u.uniacid=:uniacid and u.state=1 and unix_timestamp(c.end_time)>=:time and unix_timestamp(c.start_time)<=:time and (c.seller_id = 0 or c.seller_id=:sid) order by c.end_time asc";
        $condition[':uid'] = $member['id'];
        $condition[':uniacid'] = $uniacid;
        $condition[':time'] = $time;
        $condition[':sid'] = $sellerId;
        $list = pdo_fetchall($sql,$condition);
        foreach ($list as &$item){
            //$item['start_time'] = date('Y-m-d', $item['start_time']);
            //$item['end_time'] = date('Y-m-d', $item['end_time']);
            if($item['seller_id'] > 0){
                $item['seller_name'] = pdo_getcolumn('sbms_seller', array('id' => $item['seller_id'], 'uniacid' => $uniacid), 'seller_name');
            }
        }
        unset($item);
        show_json(1,array('list' => $list));
    }
}
