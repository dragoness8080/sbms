<?php
global $_GPC, $_W;
// $action = 'ad';
// $title = $this->actions_titles[$action];
$GLOBALS['frames'] = $this->getMainMenu();
$item=pdo_get('sbms_system',array('uniacid'=>$_W['uniacid']));
    if(checksubmit('submit')){
           $data['tid1']=trim($_GPC['tid1']);
           $data['rz_tid']=trim($_GPC['rz_tid']);
            $data['jjrz_tid']=trim($_GPC['jjrz_tid']);
            $data['tid3']=trim($_GPC['tid3']);
            $data['tid4']=trim($_GPC['tid4']);
            $data['uniacid']=trim($_W['uniacid']);
            if($_GPC['id']==''){                
                $res=pdo_insert('sbms_system',$data);
                if($res){
                    message('添加成功',$this->createWebUrl('shares',array()),'success');
                }else{
                    message('添加失败','','error');
                }
            }else{
                $res = pdo_update('sbms_system', $data, array('id' => $_GPC['id']));
                if($res){
                    message('编辑成功',$this->createWebUrl('news',array()),'success');
                }else{
                    message('编辑失败','','error');
                }
            }
        }
    include $this->template('web/news');