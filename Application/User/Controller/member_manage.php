<?php

namespace Application\User\Controller;
use  Application\User\Controller\Frontend;
class Member_manage extends Frontend {
	var $user_id = null;
	var $rec_model = null;
	var $user_model = null;
	function __construct() {
		$this->Member_manage ();
	}
	function Member_manage() {
		parent::__construct ();
		$this->rec_model = & m ( 'memberrec' );
		$this->user_model = & m ( 'member' );
		$this->user_id = $this->visitor->get ( 'user_id' );
		$setting = & af ( 'settings' );
		$this->assign ( 'default_user_img', site_url () . '/' . $setting->getOne ( 'default_user_img' ) );
	}
	function index() {
		/* 用户列表 */
		$this->_get_members ();
		
		/* 当前位置 */
		$this->_curlocal ( LANG::get ( 'member_center' ), 'index.php?app=member', LANG::get ( 'member_manage' ), 'index.php?app=member_manage', LANG::get ( 'member_list' ) );
		
		/* 当前用户中心菜单 */
		$type = (isset ( $_GET ['type'] ) && $_GET ['type'] != '') ? trim ( $_GET ['type'] ) : 'all_members';
		$this->_curitem ( 'member_manage' );
		$this->_curmenu ( $type );
		$this->_config_seo ( 'title', Lang::get ( 'member_center' ) . ' - ' . Lang::get ( 'member_manage' ) );
		$this->import_resource ( array (
				'script' => array (
						array (
								'path' => 'dialog/dialog.js',
								'attr' => 'id="dialog_js"' 
						),
						array (
								'path' => 'jquery.ui/jquery.ui.js',
								'attr' => '' 
						),
						array (
								'path' => 'jquery.ui/i18n/' . i18n_code () . '.js',
								'attr' => '' 
						),
						array (
								'path' => 'jquery.plugins/jquery.validate.js',
								'attr' => '' 
						) 
				),
				'style' => 'jquery.ui/templates/ui-lightness/jquery.ui.css' 
		) );
		/* 显示订单列表 */
		$this->display ( 'member_manage.index.html' );
	}
	
	/**
	 * 获取订单列表
	 *
	 * @author LorenLei
	 * @return void
	 */
	function _get_members() {
		$page = $this->_get_page ();
		$model_order = & m ( 'member' );
		! $_GET ['type'] && $_GET ['type'] = 'all_members';
		$conditions = '';
		
		$conditions .= $this->_get_query_conditions ( array (
				array ( // 按认证状态搜索
						'field' => 'rec_stage',
						'name' => 'type',
						'handler' => 'member_status_translator_reverse' 
				),
				array ( // 按买家名称搜索
						'field' => 'realname',
						'equal' => 'LIKE' 
				),
				array ( // 按买家昵称搜索
						'field' => 'user_name',
						'equal' => 'LIKE' 
				),
				array ( // 按买家手机号码搜索
						'field' => 'phone_mob',
						'equal' => 'LIKE'
				)
		) );
		$store_id = $this->visitor->get('manage_store');
		if(!$store_id){
			$this->show_warning('会员所属加盟商ID为空！');
		}
		$members = $this->user_model->findAll ( array (
				'join' => 'has_rec,manage_mall',
				'conditions' => "member.belong_store={$store_id} {$conditions}",
				'limit' => $page ['limit'],
				
				'count'=>true,
				'order' => 'reg_time DESC',
				'fields' => 'this.*,memberrec.*,userpriv.*' ,
				
		) );
		$count = count ( $members );
		$_COOKIE ['now'] = $count;
		/*
		 * define('UPLOADED_NO_BASE_INFO', 0);//没有上传基本身份信息 define('UPLOADED_BASE_INFO', 1);//上传了身份基本信息 define('USER_NO_PASS_CREDIT', 20);//没有通过信用认证 define('USER_PASS_CREDIT', 21);//通过信用认证 define('UPLOADED_NO_IMG_INFO', 30);//没有上传图片信息 define('UPLOADED_IMG_INFO', 31);//上传了图片信息 define('USER_NO_PASS', 40);//没有审核通过,被拒绝 define('USER_HAS_VERIFIED', 41);//审核通过
		 */
		$page ['item_count'] = $this->user_model->getCount ();
		      
		$this->_format_page ( $page );
		$this->assign ( 'types', array (
				'all' => Lang::get ( 'all_members' ),
				'uploaded_no_base_info' => Lang::get ( 'uploaded_no_base_info' ),
				'user_no_pass_credit' => Lang::get ( 'user_no_pass_credit' ),
				'user_pass_credit' => Lang::get ( 'user_pass_credit' ),
				'uploaded_img_info' => Lang::get ( 'uploaded_img_info' ),
				'user_has_verified' => Lang::get ( 'user_has_verified' ),
				'be_rejected' => Lang::get ( 'be_rejected' ) 
		) );
		$this->assign ( 'type', $_GET ['type'] );
		$this->format_member ( $members );
		      
		$this->assign ( 'members', $members );
		
		$this->assign ( 'page_info', $page );
	}
	function format_member(&$members) {
		foreach ( $members as $key => $m ) {
			
			if (! $m ['phone_mob'] && ! $m ['tel']) {
				$members [$key] ['phone_mob'] = "未知";
			}
			switch ($m ['user_role']) {
				case 1 :
					$members [$key] ['user_role'] = "学生";
					break;
				case 2 :
					$members [$key] ['user_role'] = "社会人士";
					break;
				default :
					$members [$key] ['user_role'] = "未知";
					break;
			}
			$members [$key] ['rec_stage'] = Lang::get ( member_status_translator ( $m ['rec_stage'] ) );
		}
	}
	function view() {
		if (! IS_POST) {
			/* 当前位置 */
			$this->_curlocal ( LANG::get ( 'member_center' ), 'index.php?app=member', LANG::get ( 'member_manage' ), 'index.php?app=member_manage', '查看用户' );
			
			/* 当前用户中心菜单 */
			$this->_curitem ( 'member_manage' );
			$user_id = $_GET ['user_id'];
			if (! $user_id) {
				$this->show_warning ( "没有此用户" );
				return;
			}
			$members = $this->user_model->get ( array (
					'join' => 'has_rec',
					'conditions' => array (
							'user_id',
							$user_id 
					),
					'order' => 'reg_time DESC',
					'fields' => 'this.*,memberrec.*' 
			) );
			if (! $members) {
				$this->show_warning ( "没有此用户" );
				return;
			}
			$lm = & m('userrec_log');
			$log = $lm->findAll('user_id='.intval($user_id));
			$this->assign ( "user", $members );			
			$this->assign ( "order_logs",$log );
			if ($members ['user_role'] == 1) {
				$this->display ( "member_manage.view.student.html" );
			} else {
				$this->display ( "member_manage.view.social.html" );
			}
		} else {
			
			if ($_POST ['is_pass']) {
				
				$msg = '恭喜您的个人资料通过了审核，您已经成为认证用户。';
				$ident_stage = USER_HAS_VERIFIED;
				$has_ident = 1;
				$extra = array ();
			} else {
				
				$ident_stage = 0;
				$has_ident = 0;
				$reason = $_POST ['reason'] ? "  失败原因:".$_POST ['cancel_reason']." 客服建议:" . $_POST ['reason'] : " 失败原因:" .$_POST ['cancel_reason'];
				$msg = "很遗憾，您的个人资料没有被审核通过,请您重新认证  " . $reason;
				$extra = array (
						'reason' => $reason 
				);
			}
			
			$rec_model = & m ( 'memberrec' );
			$rec_model->shenhe ( $_POST ['user_id'], $ident_stage, $has_ident );
			$rec = $rec_model->get_rec ( intval ( $_POST ['user_id'] ) );
			$userrec_log = & m ( 'userrec_log' );
			$userrec_log->add ( array (
					'user_id' => $_POST ['user_id'],
					'operator' => addslashes ( $this->visitor->get ( 'user_name' ) ),
					'oringin_status' => Lang::get(member_status_translator ( $rec ['rec_stage'] )),
					'changed_status' => Lang::get(member_status_translator ( $ident_stage )),
					'remark' => $_POST ['remark'],
					'reason' => $reason,
					'log_time' => gmtime () 
			) );
			// 通知客户
			$model_member = & m ( 'member' );
			$user_info = $model_member->get ( $rec ['user_id'] );			
			$this->send_email(array ( 
					'user_msgs' => array ( 
							'user_id' => $rec ['user_id'] , 
							'tpl' => 'user_comm_notify',
							 'info'=>array('subject'=>"用户认证结果",'content'=>$msg), 							
							'send_msg' => 1, 
							'tel_msg'=>$msg,
					 ) 
				)
			);		
			$this->show_message ( '提交成功！', 'enter_member_center', SITE_URL . '/index.php?app=member_manage' );
		}
	}
	/**
	*@author	LorenLei
	*@usage   冻结用户
	*@param
	*@return   
	*/
	function dongjie() {
		if (!IS_POST) {
			$user_id = $_GET ['user_id'];
			if (! $user_id) {
				$this->show_warning ( "没有此用户" );
				return;
			}
			$rec_model = & m ( 'memberrec' );
			$rec = $rec_model->get_rec(intval($user_id));
			$this->assign("user_id",intval($user_id));			
			$this->assign("rec",$rec);
			$this->display('member_manage.dongjie.html');
		}else{
			$rec_model = & m ( 'memberrec' );
			if($_POST['is_pass']){ 	
				$ident_stage = USER_BEEN_DONGJIE;				
			}else{
				$ident_stage = UPLOADED_NO_BASE_INFO;	
				
			}
			$rec_model->edit('user_id='.intval($_POST['user_id']),array (
					'rec_stage' =>$ident_stage
			));
			$rec = $rec_model->get_rec ( intval ( $_POST ['user_id'] ) );
			if($rec_model->has_error()){
				$this->pop_warning($this->get_errors_str($rec_model->get_error()),'dongjie_user',SITE_URL . '/index.php?app=member_manage');
				return ;
			}
			$userrec_log = & m ( 'userrec_log' );
			$userrec_log->add ( array (
					'user_id' => $_POST ['user_id'],
					'operator' => addslashes ( $this->visitor->get ( 'user_name' ) ),
					'oringin_status' => Lang::get(member_status_translator ( $rec ['rec_stage'] )),
					'changed_status' => Lang::get(member_status_translator ( $ident_stage )),
					'remark' => $_POST ['remark'],
					'log_time' => gmtime ()
			) );
			$this->pop_warning("ok",'dongjie_user',SITE_URL . '/index.php?app=member_manage');
			
			return ;
		}
	}
	
	/* 三级菜单 */
	function _get_member_submenu() {
		$array = array (
				array (
						'name' => 'all_members',
						'url' => 'index.php?app=member_manage&amp;type=all_members'
				),
				array (
						'name' => 'uploaded_img_info', // 待审核
						'url' => 'index.php?app=member_manage&amp;type=uploaded_img_info'
				),
				array (
						'name' => 'user_has_verified',//已经审核完毕
						'url' => 'index.php?app=member_manage&amp;type=user_has_verified'
				),
				array (
						'name' => 'user_been_dongjie',//已经审核完毕
						'url' => 'index.php?app=member_manage&amp;type=user_been_dongjie'
				),
				array (
						'name' => 'user_no_pass_credit',//已经审核完毕
						'url' => 'index.php?app=member_manage&amp;type=user_no_pass_credit'
				)
				
		);
		return $array;
	}
}

?>
