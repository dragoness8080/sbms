<?php
global $_GPC, $_W;
$GLOBALS['frames'] = $this->getMainMenu2();
$seller_id=$_COOKIE["storeid"];
$cur_store = $this->getStoreById($seller_id);
$item = pdo_get('sbms_notice',array('seller_id'=>$seller_id,'uniacid'=>$_W['uniacid']));
if(checksubmit('submit')){		
	$data['uniacid']=$_W['uniacid'];
	$data['js_tel']=$_GPC['js_tel'];
	$data['tpl_id']=$_GPC['tpl_id'];
	$data['seller_id']=$seller_id;
	$data['appkey']=$_GPC['appkey'];
	if($_GPC['id']==''){
		$res=pdo_insert('sbms_notice',$data);
		if($res){
			message('添加成功',$this->createWebUrl('notice',array()),'success');
		}else{
			message('添加失败','','error');
		}
	}else{
		$res = pdo_update('sbms_notice', $data, array('id' => $_GPC['id']));
		if($res){
			message('编辑成功',$this->createWebUrl('notice',array()),'success');
		}else{
			message('编辑失败','','error');
		}
	}
}
include $this->template('web/notice');