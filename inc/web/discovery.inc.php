<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/9/21
 * Time: 上午10:27
 */
global $_W,$_GPC;
$GLOBALS['frames'] = $this->getMainMenu();
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];

if($operation == 'display'){
    $pageindex = max(1, intval($_GPC['page']));
    $pagesize = 10;
    $sql = "select d.*,u.name from " . tablename('sbms_discovery') . " d left join " . tablename('sbms_user') . " u on d.openid=u.openid where d.uniacid=:uniacid order by d.createtime desc limit " . ($pageindex - 1) * $pagesize . "," . $pagesize;
    $list = pdo_fetchall($sql,array(':uniacid'=>$_W['uniacid']));
    if(!empty($list)){
        foreach ($list as &$item){
            if(empty($item['name'])){   $item['name'] = '素邦民宿社区';}
        }
        unset($item);
    }

    $count = pdo_count('sbms_discovery', array('uniacid' => $_W['uniacid']));

    $pager = pagination($count,$pageindex,$pagesize);

    include $this->template('web/discovery');
}elseif ($operation == 'adddiscovery'){
    $id = intval($_GPC['id']);
    $info = pdo_get('sbms_discovery', array('id' => $id, 'uniacid' => $_W['uniacid']));
    if($_W['ispost']){
        $data['title'] = $_GPC['title'];
        $data['logo'] = $_GPC['logo'];
        $data['content'] = $_GPC['content'];
        if(empty($info)){
            $data['uniacid'] = $_W['uniacid'];
            $data['openid'] = '0';
            $data['createtime'] = time();
            pdo_insert('sbms_discovery', $data);
        }else{
            pdo_update('sbms_discovery', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
        }
        message('编辑／添加发现成功', $this->createWebUrl('discovery'), 'success');
    }

    include $this->template('web/adddiscovery');
}elseif ($operation == 'delete'){
    $id = intval($_GPC['id']);
    pdo_delete('sbms_discovery', array('id' => $id, 'uniacid' => $_W['uniacid']));
    message('删除成功', $this->createWebUrl('discovery'), 'success');
}
