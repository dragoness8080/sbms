<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/6
 * Time: 19:02
 */

defined('IN_IA') or exit('Access Denied');

global $_W,$_GPC;

$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];

$dt_start = $_GPC['dt_start'];
$dt_end = $_GPC['dt_end'];

$model = m('common');

$days = $model->getDays($dt_start,$dt_end);
$id = intval($_GPC['id']);      //民宿ID

$hotal = pdo_get('sbms_seller', array('id' => $id, 'uniacid' => $_W['uniacid']));
$policy = $hotal['policy'];
preg_match_all("/<img.*?src=[\'| \"](.*?(?:[\.gif|\.jpg]?))[\'|\"].*?[\/]?>/", $policy, $policyImgs);
if (isset($policyImgs[1])){
    foreach ($policyImgs[1] as $img){
        $im       = array(
            "old" => $img,
            "new" => tomedia($img)
        );
        $images[] = $im;
    }
    if (isset($images)) {
        foreach ($images as $img) {
            $policy = str_replace($img['old'], $img['new'], $policy);
        }
        unset($images);
    }
    $hotal['policy'] = $policy;
}
$introduction = $hotal['introduction'];
preg_match_all("/<img.*?src=[\'| \"](.*?(?:[\.gif|\.jpg]?))[\'|\"].*?[\/]?>/", $introduction, $introductionImgs);
if (isset($introductionImgs[1])){
    foreach ($introductionImgs[1] as $img){
        $im       = array(
            "old" => $img,
            "new" => tomedia($img)
        );
        $images[] = $im;
    }
    if (isset($images)) {
        foreach ($images as $img) {
            $introduction = str_replace($img['old'], $img['new'], $introduction);
        }
        unset($images);
    }
    $hotal['introduction'] =$introduction;
}

$tp = empty($_GPC['tp']) ? 'list' : $_GPC['tp'];    //类型

$page = max(1, intval($_GPC['page']));
$pageSize = 6;
$pageCount = ($page - 1) * $pageSize;

if($operation == 'display'){
    if($_W['isajax']){
        if($tp == 'list'){  //房型列表
            $start = strtotime($dt_start);
            $list = pdo_getall('sbms_room', array('seller_id' => $id, 'state' => 1), array('id','name','price','total_num','logo','people','size'), 'id', array('price desc'));
            foreach ($list as $key => $item){
                $list[$key]['logo'] = tomedia($item['logo']);
                $bool = $model->checkRooms($item['id'],$dt_start,$dt_end);
                if($bool == false){
                    unset($list[$key]);
                }else{
                    $price = $model->getRoomPrice($item['id'], $dt_start);
                    if($price > 0){
                        $list[$key]['price'] = $price;
                    }
                    $num = pdo_getcolumn('sbms_roomnum', array('rid' => $item['id'], 'dateday' => $start), 'nums');
                    $list[$key]['num'] = $num;
                }
            }

            $rooms = array_slice($list, $pageCount, $pageSize);
            show_json(1, array('pageCount' => $pageCount, 'list' => $rooms));
        }elseif($tp == 'introduce'){    //民宿详情
            show_json(1);
        }elseif ($tp == 'evaluate'){
            $list = pdo_getall('sbms_assess', array('seller_id' => $id, 'uniacid' => $_W['uniacid']), '*', 'id', array('id desc'), array($pageCount,$pageSize));
            foreach ($list as &$item){
                $member = m('member')->getMember($item['user_id']);
                $item['img'] = empty($item['img']) ? '' : tomedia($item['img']);
                $item['thumb'] = $member['img'];
                $item['realname'] = $member['name'];
                $item['time'] = date('Y-m-d H:i:s', $item['time']);
                $item['reply_time'] = empty($item['reply_time']) ? 0 : date('Y-m-d H:i:s', $item['reply_time']);
            }
            unset($item);
            show_json(1, array('list' => $list, 'pageCount' => $pageCount));
        }
    }
}

include $this->template('house/detail');