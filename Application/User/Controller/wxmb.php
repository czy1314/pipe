<?php

/* 微公众平台关键词自动回复管理控制器 */
class WxmbApp extends MemberBaseApp {
	var $wxfile_mod;
	var $template_id = 0;
	function __construct() {
		$this->WxmbApp ();
	}
	function WxmbApp() {
		parent::__construct ();
		ignore_user_abort(true);
		set_time_limit(0);
		$wxconfig_mod = & m ( 'wxconfig' );
		$wxconfig = $wxconfig_mod->get_info_user ( $this->visitor->get ( 'user_id' ) );
		if (empty ( $wxconfig )) {
			$this->show_message ( Lang::get ( 'no_ininterface' ), '', 'index.php?app=my_wxconfig' );
			exit ();
		}
		if (trim ( $wxconfig ['appid'] ) && ($wxconfig ['appsecret']) && $wxconfig ['mbxxid']) {
			$this->appid = $wxconfig ['appid'];
			$this->secret = $wxconfig ['appsecret'];
			$this->template_id = $wxconfig ['mbxxid'];
		} else {
			$this->show_warning ( '发送消息前，请完成接口配置！' );
		}
	}
	
	function index() {
		
		if(!IS_POST)
		{
			/* 当前位置 */
			$this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('my_wxconfig'), 'index.php?app=my_wxconfig', LANG::get('my_wxconfig'));
			/* 当前用户中心菜单 */
			$this->_curitem('to_send');
			$this->_config_seo('title', Lang::get('member_center') . ' - 发送模板消息');
			$this->display('wxmb.send.form.html');
			
		}
		else
		{
			if($_POST['first'] && $_POST['keyword1'] && $_POST['keyword2'] && $_POST['remark']){
				/* 填充数据 */
				$data = array
				(
						'first'=>array
						(
								'value'=>urlencode($_POST['first']),
								'color'=>"#743A3A"
						),
						'keyword1'=>array
						(
								'value'=>urlencode($_POST['keyword1']),
								'color'=>'#FF5027'
						),
						'keyword2'=>array
						(
								'value'=>urlencode($_POST['keyword2']),
								'color'=>'#7CBAE5'
						),
			
						'remark'=>array(
								'value'=>urlencode($_POST['remark']),
								'color'=>'#44B549'
						)
			
				);
					
			
				/* 发送数据 */
				$result = $this->send_wxxx($_POST['url'], $data);
				ob_start();
				if(!$result)
				{
					echo "<script>alert('发送失败!刷新,再试一试')";
				}
				else
				{
					echo "<script>alert('成功发送！');history.back();</script>";
				}
					
				flush();
				return;
			
			}else{
				$this->show_warning('数据不完整！');
			}
			return ;
				
		}	
		
	}
	
	
	/**
	 *
	 * @author LorenLei
	 *         @usage		发送模板消息
	 * @param        	
	 *
	 * @return
	 *
	 */
	function send_wxxx($url, $data) {
		import ( 'wxmb' );
		$start_time = time();
		$this->order = new OrderPush ( $this->appid, $this->secrect );		
		$next_openid = isset ( $_GET ['next_openid'] ) ? $_GET ['next_openid'] : '';
		$next_openid = trim ( $next_openid );
		$openid_file = ROOT_PATH . 'data/openids/' . $this->visitor->get ( 'user_id' ) . 'openids.php';
		// 缓存24个小时
		if (is_file ( $openid_file )) {
			include_once $openid_file;
			if (isset ( $token ['time'] ) && (time () - $token ['time'] - 3600 * 24) < 0) {
				$user_openids = $token ['data'];
				// array_slice使用前提---保留全部数据
				if ($next_openid) {
					if ($key = array_search ( $next_openid, $user_openids )) {
						$user_openids = array_slice ( $user_openids, $key );
					}
				}
			}
		} else {
			
			$user_openids = $this->order->get_user_openids ( $next_openid );
			if (! $user_openids) {
				$this->show_warning ( '获取用户openid失败，刷新重新发送' );
				exit;
			}
			file_put_contents ( $openid_file, '<?php return $token =' . var_export ( array (
					'time' => time (),
					'data' => $user_openids 
			), true ) . ';?>' );
		}
		$success = 0;
		$fail = 0;
		$result = 1;		
		if ($user_openids) {			
			foreach ( $user_openids as $ks => $value ) {
				$result = $this->order->doSend ( $value, $this->template_id, $url, $data );
				if ($result) {
					$success ++;
				} else {
					$fail ++;
				}
				
			}
		}
		$log_model = & m('send_log');
		$log_model->add(array(
			'start_time'=>get,
			'success'=>$success,
			'fail'=>$fail,	
			'end_time'=>time(),	
			'user_id'=>$this->visitor->get('user_id')	
		));
		return array (
				'success' => $success,
				'fail' => $fail 
		);
	}
}

?>