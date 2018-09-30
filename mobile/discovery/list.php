<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/21
 * Time: 下午3:17
 */
defined('IN_IA') or exit("Access Denied");
global $_W,$_GPC;
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
$openid = m('user')->getOpenid();
if($_W['isajax']){
    if($operation == 'display'){
        $page = max(1,intval($_GPC['page']));
        $pageSize = 6;
        $list = pdo_getall('sbms_discovery', array('uniacid' => $_W['uniacid']), array('id','title','openid','browse','likes','logo'), 'id', 'createtime desc', array($page,$pageSize));
        if(!empty($list)){
            foreach ($list as &$item){
                if(empty($item['openid'])){
                    $name = '素邦民宿社区';
                    $thumb = tomedia($_W['system']['link_logo']);
                }else{
                    $member = pdo_get('sbms_user', array('openid' => $item['openid'], 'uniacid' => $_W['uniacid']), array('name','img'));
                    $name = $member['name'];
                    $thumb = $member['img'];
                }
                $item['name'] = $name;
                $item['log'] = tomedia($item['logo']);
                $item['thumb'] = $thumb;
            }
            unset($item);
        }

        show_json(1, array('pagesize' => $pageSize, 'list' => $list));
    }elseif($operation == 'likes'){
        $id = intval($_GPC['id']);
        $discovery = pdo_get('sbms_discovery', array('id' => $id, 'uniacid' => $_W['uniacid']));
        if(empty($discovery)){
            show_json(0, '未找到发现');
        }else{
            $log = pdo_get('sbms_discovery_hand', array('openid' => $openid, 'uniacid' => $_W['uniacid'], 'types' => 'likes', 'cid' => $id));
            if(empty($log)){
                $update['likes'] = $discovery['likes'] + 1;
                pdo_insert('sbms_discovery_hand', array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'createtime' => time(), 'types' => 'likes','cid' => $id));
            }else{
                $update['likes'] = $discovery['likes'] - 1;
                pdo_delete('sbms_discovery_hand', array('openid' => $openid, 'uniacid' => $_W['uniacid'], 'types' => 'likes', 'cid' => $id));
            }

            pdo_update('sbms_discovery', $update, array('id' => $id, 'uniacid' => $_W['uniacid']));
            show_json(1,$update['likes']);
        }
    }
}

include $this->template('discovery/list');