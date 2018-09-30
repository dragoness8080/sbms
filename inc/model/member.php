<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/9
 * Time: 20:50
 */
defined('IN_IA') or exit('Access Denied');

class Sbms_Member{

    public function getMember($openid){
        global $_W;
        $member = array();
        $tmp = intval($openid);
        if(!empty($tmp)){
            $member = pdo_get('sbms_user', array('id' => $tmp, 'uniacid' => $_W['uniacid']));
        }else{
            $member = pdo_get('sbms_user', array('openid' => $openid, 'uniacid' => $_W['uniacid']));
        }
        return $member;
    }

    public function getLevel($lid){
        global $_W;
        return pdo_get('sbms_level', array('id' => $lid, 'uniacid' => $_W['uniacid']));
    }

    public function getCredit($openid, $credit = "credit2"){
        global $_W;
        load()->model('mc');
        $uid = mc_openid2uid($openid);
        if(!empty($uid)){
            $credits = pdo_getcolumn('mc_members', array('uid' => $uid, 'uniacid' => $_W['uniacid']), $credit);
        }else{
            $credits = 0;
        }
        return $credits;
    }

    public function setCredit($openid, $credit = "credit2", $credits = 0, $log = array()){
        global $_W;
        load()->model('mc');
        $uid = mc_openid2uid($openid);
        if(!empty($uid)){
            $val = pdo_getcolumn('mc_members', array('uid' => $uid, 'uniacid' => $_W['uniacid']), $credit);
            $newCredit = $val + $credits;
            if($newCredit < 0){ $newCredit = 0;}
            pdo_update('mc_members', array($credit => $newCredit), array('uid' => $uid, 'uniacid' => $_W['uniacid']));
            if(!empty($log) || !is_array($log)){
                $log = array($uid,'未纪录');
            }
            $data = array(
                'uid' => $uid,
                'credittype' => $credit,
                'uniacid' => $_W['uniacid'],
                'num' => $credits,
                'createtime' => TIMESTAMP,
                'operator' => intval($log[0]),
                'remark' => $log[1]
            );
            pdo_insert('mc_credits_record', $data);
        }

    }

    /**
     * 拥有可用的优惠券数量
     * @param $openid
     * @return bool
     */
    public function coupons($openid){
        global $_W;
        $cur = strtotime(date('Y-m-d', time()));
        $uid = pdo_getcolumn('sbms_user', array('openid' => $openid, 'uniacid' => $_W['uniacid']),'id');
        $sql = "select count(u.id) as num from " . tablename('sbms_usercoupons') . " u left join " . tablename('sbms_coupons') . " c on u.coupons_id=c.id where "
            . " u.user_id=:uid and u.uniacid=:uniacid and u.state=1 and unix_timestamp(c.start_time)<=:time and unix_timestamp(c.end_time)>=:time";
        $condition = array(
            ':uid' => $uid,
            ':uniacid' => $_W['uniacid'],
            ':time' => $cur
        );
        $count = pdo_fetchcolumn($sql,$condition);
        return $count;
    }

    public function checkMember($openid = ''){
        global $_W;
        if (strexists($_SERVER['REQUEST_URI'], '/web/')){
            return;
        }
        if (empty($openid)){
            $openid = m('user')->getOpenid();
        }
        if (empty($openid)){ return;}
        $member   = m('member')->getMember($openid);
        $userinfo = m('user')->getInfo();
        $followed = m('user')->followed($openid);
        $uid      = 0;
        $mc       = array();
        load()->model('mc');
        if ($followed){
            $uid = mc_openid2uid($openid);
            $mc = mc_fetch($uid, array('realname','mobile','avatar'));
        }
        if (empty($member)){
            $member = array(
                'uniacid' => $_W['uniacid'],
                'name' => !empty($mc['nickname']) ? $mc['nickname'] : $userinfo['nickname'],
                'openid' => $openid,
                'zs_name' => !empty($mc['realname']) ? $mc['realname'] : '',
                'join_time' => time(),
                'tel' => !empty($mc['mobile']) ? $mc['mobile'] : '',
                'type' => 1,
                'level_id' => 0,
                'score' => 0,
                'img' => !empty($mc['avatar']) ? $mc['avatar'] : $userinfo['avatar']
            );
            $openidmember = pdo_count('sbms_user', array('openid' => $openid, 'uniacid' => $_W['uniacid']));
            if(empty($openidmember)){
                pdo_insert('sbms_user', $member);
            }
        }else{
            $upgrade = array();
            if ($userinfo['nickname'] != $member['name'] && !empty($userinfo['nickname'])){
                $upgrade['name'] =  removeEmoji($userinfo['nickname']);
            }
            if ($userinfo['avatar'] != $member['img'] && !empty($userinfo['avatar'])){
                $upgrade['img'] = $userinfo['avatar'];
            }
            if (!empty($upgrade)){
                pdo_update('sbms_user', $upgrade, array('id' => $member['id']));
            }
        }
    }
}