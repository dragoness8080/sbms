<?php
global $_GPC, $_W;
$seller_id=$_COOKIE["storeid"];
$uid=$_COOKIE["uid"];
$GLOBALS['frames'] = $this->getNaveMenu($seller_id, $action='start',$uid);
$cur_store = $this->getStoreById($seller_id);
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$type=isset($_GPC['type'])?$_GPC['type']:'all';
$status=$_GPC['status'];
load()->func('tpl');
$pageindex = max(1, intval($_GPC['page']));
$pagesize=10;
$where=' WHERE  uniacid=:uniacid and is_delete=0 and seller_id=:seller_id ';
if($_GPC['keywords']){
	$where.=" and name LIKE  concat('%', :order_no,'%') ";	
	$data[':order_no']=$_GPC['keywords'];
}	
if($type!='all'){
	$where.= " and status=$status";
}
if(!empty($_GPC['time'])){
   $start=strtotime($_GPC['time']['start']);
   $end=strtotime($_GPC['time']['end']);
  $where.=" and time >={$start} and time<={$end}";

}

$sql="SELECT * FROM ".tablename('sbms_order') .$where." ORDER BY id DESC";
$data[':uniacid']=$_W['uniacid'];
$data[':seller_id']=$seller_id;
$total=pdo_fetchcolumn("SELECT count(*) FROM ".tablename('sbms_order') .$where,$data);	


$select_sql =$sql." LIMIT " .($pageindex - 1) * $pagesize.",".$pagesize;
$list=pdo_fetchall($select_sql,$data);
$pager = pagination($total, $pageindex, $pagesize);
if($operation=='delete'){
	$res=pdo_update('zhjd_order',array('is_delete'=>1),array('id'=>$_GPC['id']));
	if($res){
		message('删除成功',$this->createWebUrl2('dlinorder',array()),'success');
	}else{
		message('删除失败','','error');
	}
}
if($operation=='rz'){
	$res=pdo_update('sbms_order',array('status'=>5),array('id'=>$_GPC['id']));
	if($res){
		$type=pdo_get('sbms_system',array('uniacid'=>$_W['uniacid']),'open_member');
		$order=pdo_get('sbms_order',array('id'=>$_GPC['id']));
		if($type['open_member']==1){
	//获取用户消费金额
			$count=pdo_get('sbms_order', array('user_id'=>$order['user_id'],'status'=>5), array('sum(dis_cost) as count'));	 
			$members=pdo_getall('sbms_level',array('uniacid'=>$_W['uniacid']),array() , '' , 'value DESC');
			$level_id= $this->getLevel($members,$count);
		}
		$score=$this->getScore($_GPC['id']);
		$rst=$this->saveScore($_GPC['id'],$order['user_id'],$score);
		if($rst){
			pdo_update('sbms_user',array('score +='=>$score),array('id'=>$order['user_id']));
		}
		$this->rzMessage($_GPC['id']);
		if($level_id){
	//更改会员等级
			pdo_update('sbms_user',array('level_id'=>$level_id,'type'=>2),array('id'=>$order['user_id']));
	//echo $level_id;
		}
		if($order['type']==3){
			$this->roomNum($_GPC['id']);
		}
		//更改拥金
		pdo_update('sbms_earnings',array('state'=>2),array('order_id'=>$_GPC['id']));
		message('操作成功',$this->createWebUrl2('dlinorder',array()),'success');
	}else{
		message('操作失败','','error');
	}
}
if($operation=='jjrz'){
	$orderInfo=pdo_get('sbms_order',array('id'=>$_GPC['order_id']));
	$this->jjrzMessage($_GPC['order_id']);
	if($orderInfo['type']==1){

	$result=$this->wxrefund($_GPC['order_id']);
    if ($result['result_code'] == 'SUCCESS') {//退款成功
        //更改订单操作
        pdo_update('sbms_order',array('status'=>9,'jj_time'=>time()),array('id'=>$_GPC['order_id']));
        $order=pdo_get('sbms_order',array('id'=>$_GPC['order_id']));
		//修改房间数量
		$dt_start = strtotime(substr($order['arrival_time'],0,10));  
		$dt_end = strtotime(substr($order['departure_time'],0,10));
		while ($dt_start<$dt_end){
			$dateday=$dt_start;
			$res=pdo_get('sbms_roomnum',array('rid'=>$order['room_id'],'dateday'=>$dateday));
			if($res['id']){
				$nums=$res['nums']+$order['num'];
				pdo_update('sbms_roomnum',array('nums'=>$nums),array('rid'=>$order['room_id'],'dateday'=>$dateday));
			}
			$dt_start = strtotime('+1 day',$dt_start);
		}
		pdo_update('sbms_earnings',array('state'=>3),array('order_id'=>$_GPC['order_id']));
		
        message('拒绝入住成功',$this->createWebUrl2('dlinorder',array()),'success');

        }else{
         message($result['err_code_des'],'','error');
        }
    }  
    	//余额支付
    if($orderInfo['type']==2){
    	$result=$this->saveRecharge($_GPC['order_id']);
    	if($result==1){
    		pdo_update('sbms_order',array('status'=>9,'jj_time'=>time()),array('id'=>$_GPC['order_id']));
    		pdo_update('sbms_user',array('balance +='=>$orderInfo['total_cost']),array('id'=>$orderInfo['user_id']));
    		  message('拒绝入住成功',$this->createWebUrl2('dlinorder',array()),'success');
    	}
    } 
        //到店付款
    if($orderInfo['type']==3){   
    	pdo_update('sbms_order',array('status'=>9,'jj_time'=>time()),array('id'=>$_GPC['order_id']));
    	message('拒绝入住成功',$this->createWebUrl2('dlinorder',array()),'success');
    	
    }  
   
}
if($operation=='refund'){
	$orderInfo=pdo_get('sbms_order',array('id'=>$_GPC['order_id']));
	if($orderInfo['type']==1){
	$result=$this->wxrefund($_GPC['order_id']);
    if ($result['result_code'] == 'SUCCESS') {//退款成功
        //更改订单操作
        pdo_update('sbms_order',array('status'=>7),array('id'=>$_GPC['order_id']));   
        $order=pdo_get('sbms_order',array('id'=>$_GPC['order_id']));
		//修改房间数量
		$dt_start = strtotime(substr($order['arrival_time'],0,10));  
		$dt_end = strtotime(substr($order['departure_time'],0,10));
		while ($dt_start<$dt_end){
			$dateday=$dt_start;
			$res=pdo_get('sbms_roomnum',array('rid'=>$order['room_id'],'dateday'=>$dateday));
			if($res['id']){
				$nums=$res['nums']+$order['num'];
				pdo_update('sbms_roomnum',array('nums'=>$nums),array('rid'=>$order['room_id'],'dateday'=>$dateday));
			}
			$dt_start = strtotime('+1 day',$dt_start);
		}
        pdo_update('sbms_earnings',array('state'=>3),array('order_id'=>$_GPC['order_id']));
        message('退款成功',$this->createWebUrl2('dlinorder',array()),'success');
        }else{
         message($result['err_code_des'],'','error');
        }
    }
        	//余额支付
    if($orderInfo['type']==2){
    	$result=$this->saveRecharge($_GPC['order_id']);
    	if($result==1){
    		pdo_update('sbms_order',array('status'=>7),array('id'=>$_GPC['order_id']));
    		pdo_update('sbms_user',array('balance +='=>$orderInfo['total_cost']),array('id'=>$orderInfo['user_id']));
    		  message('退款成功',$this->createWebUrl('order',array()),'success');
    	}
    } 

}
if($operation=='reject'){
	$res=pdo_update('sbms_order',array('status'=>8),array('id'=>$_GPC['order_id']));
	if($res){
			//更改拥金
	pdo_update('sbms_earnings',array('state'=>2),array('order_id'=>$_GPC['order_id']));
		message('拒绝成功',$this->createWebUrl2('dlinorder',array()),'success');
	}else{
		message('拒绝失败','','error');
	}
}

if($operation=='close'){
	$res=pdo_update('sbms_order',array('voice'=>2),array('id'=>$_GPC['order_id']));
	if($res){
		message('操作成功',$this->createWebUrl2('dlinorder',array()),'success');
	}else{
		message('操作失败','','error');
	}
}
if($operation=='query'){
	$res=pdo_update('sbms_order',array('status'=>10),array('id'=>$_GPC['order_id']));
	if($res){
		$this->queryOrderMessage($_GPC['order_id']);
		message('确认成功',$this->createWebUrl2('dlinorder',array()),'success');
	}else{
		message('确认失败','','error');
	}
}
if($operation=='jjorder'){
	$orderInfo=pdo_get('sbms_order',array('id'=>$_GPC['order_id']));
	$this->rejectOrderMessage($_GPC['order_id']);
	if($orderInfo['type']==1){
	$result=$this->wxrefund($_GPC['order_id']);
    if ($result['result_code'] == 'SUCCESS') {//退款成功
        //更改订单操作
        pdo_update('sbms_order',array('status'=>9,'jj_time'=>time()),array('id'=>$_GPC['order_id']));
        $order=pdo_get('sbms_order',array('id'=>$_GPC['order_id']));
		//修改房间数量
		$dt_start = strtotime(substr($order['arrival_time'],0,10));  
		$dt_end = strtotime(substr($order['departure_time'],0,10));
		while ($dt_start<$dt_end){
			$dateday=$dt_start;
			$res=pdo_get('sbms_roomnum',array('rid'=>$order['room_id'],'dateday'=>$dateday));
			if($res['id']){
				$nums=$res['nums']+$order['num'];
				pdo_update('sbms_roomnum',array('nums'=>$nums),array('rid'=>$order['room_id'],'dateday'=>$dateday));
			}
			$dt_start = strtotime('+1 day',$dt_start);
		}
		pdo_update('sbms_earnings',array('state'=>3),array('order_id'=>$_GPC['order_id']));	
        message('拒绝成功',$this->createWebUrl2('dlinorder',array()),'success');
        }else{
         message($result['err_code_des'],'','error');
        }
    }  
    	//余额支付
    if($orderInfo['type']==2){
    	$result=$this->saveRecharge($_GPC['order_id']);
    	if($result==1){
    		pdo_update('sbms_order',array('status'=>9,'jj_time'=>time()),array('id'=>$_GPC['order_id']));
    		pdo_update('sbms_user',array('balance +='=>$orderInfo['total_cost']),array('id'=>$orderInfo['user_id']));
    		  message('拒绝成功',$this->createWebUrl2('dlinorder',array()),'success');
    	}
    }    
    //到店付款
    if($orderInfo['type']==3){   
    	pdo_update('sbms_order',array('status'=>9,'jj_time'=>time()),array('id'=>$_GPC['order_id']));
    	message('拒绝成功',$this->createWebUrl2('dlinorder',array()),'success');
    	
    }  
}
include $this->template('web/dlinorder');