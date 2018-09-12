<?php
/**
 * 志汇酒店营销版模块微站定义
 *
 * @author 武汉志汇科技
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
require_once IA_ROOT . '/addons/sbms/defines.php';
require_once SBMS_PATH_FUNC . 'function.php';
require_once SBMS_PATH_FUNC . 'core.php';

class sbmsModuleSite extends Core {

    public function doMobileHome(){ $this->_exce(__FUNCTION__,'index');}
    public function doMobileHouse(){ $this->_exce(__FUNCTION__, 'list');}
    public function doMobileMember(){ $this->_exce(__FUNCTION__, 'center');}
    public function doMobileOrder(){ $this->_exce(__FUNCTION__, 'list');}


	public function doMobileYjRefund(){
		global $_W, $_GPC;
		$money=$_GPC['money']*100;//退款金额
        $order_id=$_GPC['id'];
		include_once IA_ROOT . '/addons/sbms/cert/WxPay.Api.php';
		load()->model('account');
		load()->func('communication');
		$refund_order =pdo_get('sbms_order',array('id'=>$order_id));  
		$WxPayApi = new WxPayApi();
		$input = new WxPayRefund();
		$path_cert = IA_ROOT . "/addons/sbms/cert/".'apiclient_cert_' .$_W['uniacid'] . '.pem';
		$path_key = IA_ROOT . "/addons/sbms/cert/".'apiclient_key_' . $_W['uniacid'] . '.pem';
		$account_info = $_W['account'];   
		$res=pdo_get('sbms_system',array('uniacid'=>$_W['uniacid']));
		$appid=$res['appid'];
		$key=$res['wxkey'];
		$mchid=$res['mchid']; 
		$out_trade_no=$refund_order['out_trade_no'];
		$fee = $refund_order['total_cost'] * 100;
		$input->SetAppid($appid);
		$input->SetMch_id($mchid);
		$input->SetOp_user_id($mchid);
		$input->SetRefund_fee($money);
		$input->SetTotal_fee($fee);
           // $input->SetTransaction_id($refundid);
		$input->SetOut_refund_no($refund_order['order_no']);
		$input->SetOut_trade_no($out_trade_no);
		$result = $WxPayApi->refund($input, 6, $path_cert, $path_key, $key);
		 if ($result['result_code'] == 'SUCCESS') {//退款成功
        //更改订单操作
        pdo_update('sbms_order',array('ytyj_cost'=>($money/100)),array('id'=>$order_id));           
       return '1';
        }else{
        return '2';
        }
	
	}

	public function doMobileSelectJd(){
		global $_W, $_GPC;
		$sql =" select id,name from ".tablename('sbms_seller')." where uniacid={$_W['uniacid']} and state=2 and  name like '%{$_GPC['keywords']}%'";    
		echo json_encode(pdo_fetchall($sql));
	}


	public function doMobileSelectUser(){
		global $_W, $_GPC;
		$sql =" select id,name from ".tablename('sbms_user')." where uniacid={$_W['uniacid']}  and  openid like '%{$_GPC['keywords']}%'";    
		echo json_encode(pdo_fetchall($sql));
	}


	public function doMobileNewOrder(){
		global $_W, $_GPC;
		$time=time();
		$time2=$time-10;
		if($_GPC['role']=='operator'){
    //查找商家ID;
			$seller=pdo_get('sbms_account',array('weid'=>$_W['uniacid'],'uid'=>$_GPC['uid']));
			$seller_id=$seller['storeid'];
			$sql="SELECT * FROM ".tablename('sbms_order')." WHERE  uniacid=:uniacid and status=2 and seller_id=:seller_id and voice=1 union all SELECT * FROM ".tablename('sbms_order')." WHERE  status=1 and type=3 and seller_id=:seller_id and uniacid=:uniacid and voice=1";
			$res=pdo_fetch($sql,array(':uniacid'=>$_GPC['uniacid'],':seller_id'=>$seller_id));
			if($res){
				echo '1';
			}else{
				echo '2';
			}
		}else {
			$sql="SELECT * FROM ".tablename('sbms_order')." WHERE  status=2 and uniacid=:uniacid and voice=1 union all SELECT * FROM ".tablename('sbms_order')." WHERE  status=1 and type=3 and uniacid=:uniacid and voice=1";
			$res=pdo_fetch($sql,array(':uniacid'=>$_GPC['uniacid']));

			if($res){
				echo '1';
			}else{
				echo '2';
			}
		}

	}

   //删除充值活动
    public function doMobileDelCz(){
		global $_W,$_GPC;
		$res=pdo_delete('sbms_czhd',array('id'=>$_GPC['id']));
		if($res){
			echo '1';
		}else{
			echo '2';
		}
    }
    //添加充值活动
    public function doMobileAddCz(){
    	global $_W,$_GPC;
    	for($i=0;$i<count($_GPC['list']);$i++){
    		$data['full']=$_GPC['list'][$i]['full'];
    		$data['reduction']=$_GPC['list'][$i]['reduction'];
    		$data['uniacid']=$_W['uniacid'];
    		pdo_insert('sbms_czhd',$data);
    	}
    }



	public function doMobileSelectUser2(){
		global $_W, $_GPC;
		$sql =" select id,name from ".tablename('sbms_user')." where uniacid={$_W['uniacid']}  and  openid like '%{$_GPC['keywords']}%' and id not in (select user_id from".tablename('sbms_seller')." where  uniacid={$_W['uniacid']})";    
		echo json_encode(pdo_fetchall($sql));
	}

}