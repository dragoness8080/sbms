<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/17
 * Time: 下午3:27
 */
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);

$uniacid = $_W['uniacid'];
$uid = $member['id'];
if($_W['isajax']){
    $page = max(1, intval($_GPC['page']));
    $pageSize = 8;
    $pageCount = ($page - 1) * $pageSize;

    $where = "u.user_id=:uid and u.uniacid=:uniacid";
    $condition[':uid'] = $uid;
    $condition[':uniacid'] = $uniacid;
    $state = intval($_GPC['state']);

    $cur = strtotime(date('Y-m-d', time()));
    if($state == 0){    //有效
        $where .= " and u.state=1 and unix_timestamp(c.end_time)>=:time";
        $condition[':time'] = $cur;
    }elseif($state == 1){   //已使用
        $where .= " and u.state=2";
    }elseif($state == 2){   //已过期
        $where .= " and unix_timestamp(c.end_time)<=:time";
        $condition[':time'] = $cur;
    }

    $sql = "select * from " . tablename('sbms_usercoupons') . " u left join " . tablename('sbms_coupons') . " c on u.coupons_id=c.id where "
        . $where . " order by u.time desc limit " . $pageCount . "," . $pageSize;

    $list = pdo_fetchall($sql,$condition);
    foreach ($list as &$item){

    }
    unset($item);
    show_json(1,array('pagecount' => $pageSize, 'list' => $list));
}

include $this->template('member/coupon');