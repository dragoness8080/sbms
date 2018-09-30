<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/16
 * Time: 14:30
 */
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
if($_W['isajax']){
    if($operation == 'receive'){
        $couponId = intval($_GPC['id']);
        $count = pdo_count('sbms_usercoupons', array('user_id' => $member['id'], 'coupons_id' => $couponId, 'uniacid' => $_W['uniacid']));
        if(empty($count)){
            $insert = array(
                'user_id' => $member['id'],
                'coupons_id' => $couponId,
                'state' => 1,
                'time' => time(),
                'sy_time' => 0,
                'uniacid' => $_W['uniacid']
            );

            pdo_insert('sbms_usercoupons', $insert);
            $id = pdo_insertid();
            if($id){
                $num = pdo_getcolumn('sbms_coupons', array('id' => $couponId, 'uniacid' => $_W['uniacid']), 'number');
                pdo_update('sbms_coupons', array('number' => ($num - 1)), array('id' => $couponId, 'uniacid' => $_W['uniacid']));
            }
        }
    }

    $page = max(1,intval($_GPC['page']));
    $pageSize = 8;
    $pageCount = ($page - 1) * $pageSize;
    $list = pdo_getall('sbms_coupons', array('uniacid' => $_W['uniacid']),'*', 'id', 'time desc');
    $coupons = array();
    foreach ($list as $item){
        $isused = m('common')->checkCouponUsed($item['start_time'],$item['end_time']);
        $item['isused'] = $isused;
        $has = pdo_getcolumn('sbms_usercoupons', array('user_id' => $member['id'], 'coupons_id' => $item['id'], 'uniacid' => $_W['uniacid']), 'id');
        $item['has'] = empty($has) ? 0 : 1; //是否已领取
        if($item['seller_id'] > 0){
            $seller_name = pdo_getcolumn('sbms_seller', array('id' => $item['seller_id'], 'uniacid' => $_W['uniacid']),'name');
            $item['seller_name'] = $seller_name;
        }
        if($item['isused'] > 0 && $item['has'] < 1){    //取消所有过期和以领取的优惠券
            $coupons[] = $item;
        }
    }
    unset($list);
    $list = array_slice($coupons,$pageCount,$pageSize);

    show_json(1,array('pagecount' => $pageCount, 'list' => $list));
}

include $this->template('coupon/list');