<?php
//门店列表
	global $_W,$_GPC;
		$id=trim($_GPC['pid']);
		$data['dateday']=strtotime(trim($_GPC['dateday']));
		$data['mprice']=trim($_GPC['price']);
		$data['rid']=trim($_GPC['rid']);
		$res=pdo_get('sbms_roomprice',array('rid'=>$data['rid'],'dateday'=>$data['dateday']));
		if(!$res['id']){
			pdo_insert('sbms_roomprice',$data);
		}else{
			pdo_update('sbms_roomprice',array('mprice'=>$data['mprice']),array('id'=>$res['id']));
		}