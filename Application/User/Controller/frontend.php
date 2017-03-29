<?php
/**
 *    前台控制器基础类
 *
 *    @author    LiuLei
 *    @usage    none
 */
namespace  Application\User\Controller;
use \Common\Controller\BaseVisitor;
use \Common\Controller\ZmApp;
use \Common\lib\Init_FrontendApp;
use \Framework\Core\util\Conf;
use \Framework\Core\util\Lang;
class Frontend extends ZmApp {
	var $s;
	function __construct() {
		$this->FrontendApp ();
	}
	function FrontendApp() {
		////Lang::load ( lang_file ( 'common' ) );
		////Lang::load ( lang_file ( APP ) );
		parent::__construct ();
	}
	function _run_action() {
		
		$this->init_cache();
		parent::_run_action ();
	}
	
	function _get_waptemplate_name() {
		$template_name = Conf::get ( 'waptemplate_name' );
		if (! $template_name) {
			$template_name = 'default';
		}
		return $template_name;
	}
	function _get_wapstyle_name() {
		$style_name = Conf::get ( 'wapstyle_name' );
		if (! $style_name) {
			$style_name = 'default';
		}
		return $style_name;
	}
	function _config_view() {
		parent::_config_view ();
		
		if (Boot_WAP == 1) {
				
			$template_name = $this->_get_waptemplate_name ();
			$style_name = $this->_get_wapstyle_name ();
			$this->_view->template_dir = ROOT_PATH . "/templates/wapmall/{$template_name}";
			$this->_view->compile_dir = ROOT_PATH . "/temp/compiled/wapmall/{$template_name}";
			$this->_view->res_base = SITE_URL . "/templates/wapmall/{$template_name}/styles/{$style_name}";
		} else {
			$template_name = $this->_get_template_name ();
			$style_name = $this->_get_style_name ();
			$this->_view->template_dir = ROOT_PATH . "/templates/mall/{$template_name}";
			$this->_view->compile_dir = ROOT_PATH . "/temp/compiled/mall/{$template_name}";
			$this->_view->res_base = SITE_URL . "/templates/mall/{$template_name}/styles/{$style_name}";
		}
		      
		$this->_config_seo ( array (
				'title' => Conf::get ( 'site_title' ),
				'description' => Conf::get ( 'site_description' ),
				'keywords' => Conf::get ( 'site_keywords' ) 
		) );
	}
	function adb() {
	}
	function add_lost($url) {
		$url = str_replace ( '/', '-xg-', trim ( $url ) );
		$url = str_replace ( '?', '-wh-', $url );
		$url = str_replace ( '=', '~dy~', $url );
		$url = str_replace ( '&', '~ad~', $url );
		$uuid = 'uuid:' . crypt ( $url, SALT );
		$url = $url . $uuid;
		return $url;
	}
	/* 保证url是系统产生的 */
	function strip_lost($url) {
		$start = strpos ( $url, 'uuid:' );
		$uuid_him = substr ( $url, $start + 5 );
		$url = substr ( $url, 0, $start );
		$uuid_us = crypt ( $url, SALT );
		if ($uuid_him != $uuid_us) {
			$this->show_warning ( '请按常规方式操作！' );
			exit ();
		}
		$url = str_replace ( '-xg-', '/', $url );
		$url = str_replace ( '-wh-', '?', $url );
		$url = str_replace ( '~dy~', '=', $url );
		$url = str_replace ( '~ad~', '&', $url );
		return trim ( $url );
	}
	function display($tpl) {
		
		/* 新消息 */
		$this->assign ( 'new_message', isset ( $this->visitor ) ? $this->_get_new_message () : '' );		
		$init = new Init_FrontendApp();	
		$this->assign ( 'navs', $this->_get_navs () ); // 自定义导航
		$this->assign ( 'acc_help', ACC_HELP ); // 帮助中心分类code
		$this->assign ( 'site_title', Conf::get ( 'site_title' ) );
		$this->assign ( 'site_logo', Conf::get ( 'site_logo' ) );
		$this->assign ( 'statistics_code', Conf::get ( 'statistics_code' ) ); // 统计代码
		$current_url = explode ( '/', $_SERVER ['REQUEST_URI'] );
		$count = count ( $current_url );
		$this->assign ( 'current_url', $count > 1 ? $current_url [$count - 1] : $_SERVER ['REQUEST_URI'] ); // 用于设置导航状态(以后可能会有问题)
		
	   // 广告图，城市选择器
		$this->show_widgt ( 1 );
		// 表单隐藏域
		$this->auto_hidden ();		
		$user = $this->visitor->get();
		$user_mod =& m('member');
		$user = $user_mod->get_info($user['user_id']);
		$user['portrait'] = portrait($user['user_id'], $user['portrait'], 'middle');
		$this->assign('user', $user);
				       
		parent::display ( $tpl );
		
	}
	/**
	 * 导入jq.ui.js，dialog.js jq.ui.css jq.validate.js
	 */
	function import_resource_jqui() {
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
	}
	/* 热门搜素 tyioocom */
	function _get_hot_keywords() {
		$keywords = explode ( ',', conf::get ( 'hot_search' ) );
		return $keywords;
	}
	function curl_get_contents($url) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 1 );
		curl_setopt ( $ch, CURLOPT_USERAGENT, _USERAGENT_ );
		curl_setopt ( $ch, CURLOPT_REFERER, _REFERER_ );
		@curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
		$r = curl_exec ( $ch );
		curl_close ( $ch );
		return $r;
	}
	function goheader($oauth_url) {
		header ( 'Expires: 0' );
		header ( 'Last-Modified: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
		header ( 'Cache-Control: no-store, no-cahe, must-revalidate' );
		header ( 'Cache-Control: post-chedk=0, pre-check=0', false );
		header ( 'Pragma: no-cache' );
		header ( "HTTP/1.1 301 Moved Permanently" );
		header ( "Location: $oauth_url" );
		exit ();
	}
	function getcode_by_redirect() {
		// return;
		if (! $this->visitor->has_login) {
			$s = &cache_server ();
			if ($s->get ( session_id () . '_code' )) {
				$_GET ['code'] = $s->get ( session_id () . '_code' );
				$this->_wxautologin ();
				return;
			}
			$back_url = SITE_URL . '/index.php?app=member&act=login&ret_url=' . $_GET ['ret_url'];
			$redirect_uri = urlencode ( $back_url );
			$state = 'wechat';
			$scope = 'snsapi_base';
			$wxconfig = $this->init_wxconfig ();
			$oauth_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $wxconfig ['appid'] . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=' . $scope . '&state=' . $state . '#wechat_redirect';
			header ( 'Expires: 0' );
			header ( 'Last-Modified: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
			header ( 'Cache-Control: no-store, no-cahe, must-revalidate' );
			header ( 'Cache-Control: post-chedk=0, pre-check=0', false );
			header ( 'Pragma: no-cache' );
			header ( "HTTP/1.1 301 Moved Permanently" );
			header ( "Location: $oauth_url" );
		}
		
		return;
	}
	function get_user_openid($code) {
		$code = ! empty ( $_GET ['code'] ) ? $_GET ['code'] : '';
		$wxconfig = $this->init_wxconfig ();
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $wxconfig ['appid'] . '&secret=' . $wxconfig ['appsecret'] . '&code=' . $code . '&grant_type=authorization_code';
		$ret_oa_json = $this->curl_get_contents ( $url );
		$ret_oa = json_decode ( $ret_oa_json, true );
		if (! $ret_oa || (isset ( $ret_oa ['errcode'] ) && $ret_oa ['errcode'] > 0)) {
			$this->show_warning ( '获取openid失败！请刷新重试!' . $ret_oa );
			return;
		}
		$user_openid = $ret_oa ['openid'];
		$_SESSION ['user_openid'] = $user_openid;
	}
	function _wxautologin() {
		if (WX_WAP != 1) {
			return;
		}
		$ret_url = get_ret_url ( 'app=member' );
		if ($this->visitor->has_login) {
			redirect ( '', $ret_url );
			return;
		}
		
		if (isset ( $_GET ['code'] )) {
			$s = &cache_server ();
			$s->set ( session_id () . '_code', $_GET ['code'], 60 * 4 );
			if ($_SESSION ['user_openid']) {
				
				$user_openid = $_SESSION ['user_openid'];
			} else {
				$user_openid = $this->get_user_openid ( $_GET ['code'] );
			}
			
			if (! empty ( $user_openid )) {
				$_SESSION ['user_openid'] = $user_openid;
				$wxrelation_mod = & m ( 'wxrelation' );
				$mod_user = & m ( 'member' );
				
				// 如果存在记录就自动登录
				$table = " {$wxrelation_mod->table} wx INNER JOIN {$mod_user->table} m ON m.user_id = wx.user_id where wx.user_openid = '" . trim ( $user_openid ) . "'";
				$sql = "SELECT m.user_id FROM {$table} ORDER BY  wx.creat_time ASC LIMIT 1";
				$user_id = $wxrelation_mod->getOne ( $sql );
				
				if ($user_id) {
					
					$this->_do_login ( $user_id );
				} else {
					
					$user_id = $this->reg_by_wx ( array (
							'user_openid' => $user_openid 
					) );
					
					if (! $user_id) {
						$this->show_warning ( '通过微信注册失败！' );
						return;
					}
					$this->_do_login ( $user_id );
				}
				
				// header ( 'Location:' . $this->strip_lost ( $ret_url ) );
				$this->show_message ( '获取用户信息成功！' );
			} else {
				$this->show_warning ( '获取用户openid失败，请刷新重试。' );
			}
		} else {
			
			$this->getcode_by_redirect ();
		}
	}
	/**
	 *
	 * @author liulei
	 *         @usage 通过token获取用户信息
	 * @param        	
	 *
	 *
	 *
	 *
	 * @return
	 *
	 *
	 *
	 *
	 */
	var $sended_count = 0;
	var $limt_send = 2;
	function get_user_info($user_openid) {
		if ($_SESSION ['user_wxinfo']) {
			return $_SESSION ['user_wxinfo'];
		}
		
		$wxconfig = $this->init_wxconfig ();
		$url_token = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $wxconfig ['appid'] . '&secret=' . $wxconfig ['appsecret'];
		$ret_token_json = $this->curl_get_contents ( $url_token );
		$ret_token = json_decode ( $ret_token_json );
		$ret_token->access_token;
		
		$sns_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $ret_token->access_token . "&openid=" . $user_openid;
		$ret_sns_json = $this->curl_get_contents ( $sns_url );
		$ret_sns = json_decode ( $ret_sns_json );
		
		if (! $ret_sns || (isset ( $ret_sns->errcode ) && $ret_sns->errcode > 0)) {
			
			if ($this->sended_count < $this->limt_send) {
				$this->get_user_info ( $user_openid );
			}
			// 超过n次，则认为发送失败
			if ($this->sended_count == $this->limt_send) {
				$this->show_warning ( '经过三次努力，获取用户信息失败！请刷新重试!' );
				return false;
			}
		}
		$_SESSION ['user_wxinfo'] = $ret_sns;
		return $ret_sns;
	}
	/**
	 * 通过微信注册
	 *
	 * @author liulei
	 *         @usage
	 * @param $user_openid belong_store
	 *        	user_id
	 * @return $user_id
	 */
	function reg_by_wx($data) {
		$ret_sns = $this->get_user_info ( $data ['user_openid'] );
		
		$nickname = $ret_sns->nickname;
		$openid = $ret_sns->openid;
		$sex = $ret_sns->sex;
		$headimgurl = $ret_sns->headimgurl;
		$city = $ret_sns->city;
		$province = $ret_sns->province;
		
		$belong_store = isset ( $_SESSION ['current_store'] ['belong_store'] ) ? $_SESSION ['current_store'] ['store_id'] : '';
		$select_city = isset ( $_SESSION ['current_store'] ['city'] ) ? $_SESSION ['current_store'] ['city'] : '';
		$ms = & ms (); // 连接用户中心
		               // 第一次注册，记录用户所属城市加盟商
		$user_id = $ms->user->register ( '', '', '', array (
				'nickname' => $nickname,
				'portrait' => $headimgurl,
				'gender' => $sex,
				'select_city' => $select_city,
				'belong_store' => $belong_store,
				// 推荐人id，或者称为虚拟商家id
				'vstore_id' => $_SESSION ['vstore_id'] 
		) );
		
		if (! $user_id) {
			$arr = $ms->user->get_error ();
			if (DEBUG_MODE) {
				$this->show_warning ( var_export ( $ms->user->get_error (), true ) );
				return;
			}
			$this->show_warning ( 'step1通过微信注册失败!' );
			return;
		}
		
		$data = array (
				'user_openid' => $ret_sns->openid,
				'user_id' => $user_id,
				'nickname' => $ret_sns->nickname,
				'creat_time' => gmtime (),
				'city' => $ret_sns->city,
				'province' => $ret_sns->province 
		);
		
		$wxrelation_mod = & m ( 'wxrelation' );
		if (! $wxrelation_mod->add ( $data )) {
			
			if (DEBUG_MODE) {
				$this->show_warning ( var_export ( $ms->user->get_error (), true ) );
				return;
			}
			$this->show_warning ( 'step2通过微信注册失败!' );
			return;
		}
		
		return $user_id;
	}
	function is_accesss($url) {
		$ch = curl_init ( $url );
		curl_exec ( $ch );
		$str = curl_getinfo ( $ch );
		curl_close ( $ch );
		if ($str ['http_code'] == 200) {
			return true;
		} else {
			return false;
		}
	}
	function _update_user($user_id) {
		$user_openid = $_SESSION ['user_openid'];
		$store_openid = $_SESSION ['store_openid'];
		$wx_store_id = $_SESSION ['wx_store_id'];
		$data = array (
				'user_id' => $user_id 
		);
		if (! empty ( $user_openid ) && ! empty ( $store_openid ) && ! empty ( $wx_store_id )) {
			
			$wxrelation_mod = & m ( 'wxrelation' );
			
			$result = $wxrelation_mod->edit ( "user_openid='" . $user_openid . "' and store_openid='" . $store_openid . "'", $data );
			
			return $result;
		} else {
			return false;
		}
	}
	function login() {	       
		
		if (! IS_POST) {
			
			/* 防止登陆成功后跳转到登陆、退出的页面 */
			$ret_url = strtolower ( $ret_url );
			if (str_replace ( array (
					'act=login',
					'act=logout' 
			), '', $ret_url ) != $ret_url) {
				$ret_url = SITE_URL . '/index.php';
			}
			
			if (Conf::get ( 'captcha_status.login' )) {
				$this->assign ( 'captcha', 1 );
			}
			
			$this->import_resource ( array (
					'script' => 'jquery.plugins/jquery.validate.js' 
			) );
			
			$this->assign ( 'ret_url', rawurlencode ( $ret_url ) );
			$this->_curlocal ( LANG::get ( 'user_login' ) );
			$this->_config_seo ( 'title', Lang::get ( 'user_login' ) . ' - ' . Conf::get ( 'site_title' ) );
			
			$this->display ( 'login.html' );
		
		} else {
			if (Conf::get ( 'captcha_status.login' ) && base64_decode ( $_SESSION ['captcha'] ) != strtolower ( $_POST ['captcha'] )) {
				$this->show_warning ( 'captcha_failed' );
				
				return;
			}
			
			$user_name = trim ( $_POST ['user_name'] );
			$password = $_POST ['password'];
			
			$ms = & ms ();
			$user_id = $ms->user->auth ( $user_name, $password );
			if (! $user_id) {
				/* 未通过验证，提示错误信息 */
				$this->show_warning ( $ms->user->get_error () );
				return;
			} else {
				/* 通过验证，执行登陆操作 */
				
				$this->_do_login ( $user_id );
				
				/* 同步登陆外部系统 */
				$synlogin = $ms->user->synlogin ( $user_id );
			}
			
			$this->show_message ( Lang::get ( 'login_successed' ) . $synlogin, 'back_before_login', rawurldecode ( $_POST ['ret_url'] ), 'enter_member_center', 'index.php?app=member' );
		}
		
	}
	function pop_warning($msg, $dialog_id = '', $url = '') {
		if ($msg == 'ok') {
			if (empty ( $dialog_id )) {
				$dialog_id = APP . '_' . ACT;
			}
			if (! empty ( $url )) {
				echo "<script type='text/javascript'>window.parent.location.href='" . $url . "';</script>";
			}
			echo "<script type='text/javascript'>window.parent.js_success('" . $dialog_id . "');</script>";
		} else {
			header ( "Content-Type:text/html;charset=" . CHARSET );
			$msg = is_array ( $msg ) ? $msg : array (
					array (
							'msg' => $msg 
					) 
			);
			$errors = '';
			foreach ( $msg as $k => $v ) {
				$error = $v [obj] ? Lang::get ( $v [msg] ) . " [" . Lang::get ( $v [obj] ) . "]" : Lang::get ( $v [msg] );
				$errors .= $errors ? "<br />" . $error : $error;
			}
			echo "<script type='text/javascript'>window.parent.js_fail('" . $errors . "');</script>";
		}
	}
	function pop_warning_($msg, $ext = array()) {
	
		if ($ext) {
			$data = array_merge ( array (
					'done' => 0,
					'msg' => $msg 
			), $ext );
			echo json_encode ( $data );
		} else {
			echo json_encode ( array (
					'done' => 0,
					'msg' => $msg 
			) );
		}
		exit ();
		
	}
	function pop_message_($msg, $ext = array()) {
		
		if ($ext) {
			$data = array_merge ( array (
					'done' => 1,
					'msg' => $msg 
			), $ext );
			echo json_encode ( $data );
		} else {
			echo json_encode ( array (
					'done' => 1,
					'msg' => $msg 
			) );
		}
		exit ();
	}
	/**
	 * 获得hidden input 字符串
	 *
	 * @return string
	 */
	function get_hidden_str() {
		if (! $_SESSION ['hidden_value']) {
			$this->show_warning ( "hidden_value不存在!" );
			return '';
		}
		return "<input type='hidden' name='hidden_value' value='{$_SESSION['hidden_value']}' />";
	}
	function auto_hidden() {
		if (IS_POST && isset ( $_POST ['hidden_value_'] )) {
			
			$hidden_value_ = $_SESSION ['hidden_value_'];
			$p_hidden_value_ = $_POST ['hidden_value_'];
			
			if (trim ( $p_hidden_value_ ) != $hidden_value_) {
				
				echo "<script>alert('不能重复提交！');</script>";
				// exit;
				unset ( $_SESSION ['hidden_value_'] );
			}
			unset ( $_SESSION ['hidden_value_'] );
		}
	}
	/**
	 *
	 * @author liulei
	 *         @usage 分配hidden value
	 * @param        	
	 *
	 *
	 *
	 *
	 * @return
	 *
	 *
	 *
	 *
	 */
	function assign_hidden_value() {
		$_SESSION ['hidden_value_'] = create_hidden_value ();
		
		$this->assign ( "hidden_value_", "<input name='hidden_value_' type='hidden' value='" . $_SESSION ['hidden_value_'] . "'/>" );
	}
	function logout() {
		$this->visitor->logout ();
		
		/* 跳转到登录页，执行同步退出操作 */
		
		header ( "Location: index.php?app=member&act=login&synlogout=1" );
		
		return;
	}
	
	/* 执行登录动作 */
	function _do_login($user_id) {
		$mod_user = & m ( 'member' );
		
		$user_info = $mod_user->get ( array (
				'conditions' => "user_id = '{$user_id}'",
				'fields' => 'user_id, user_name, reg_time, last_login, last_ip' 
		) );
		
		
		/* 分派身份 */
		$this->visitor->assign ( $user_info );
		/* 更新用户登录信息 */
		$mod_user->edit ( "user_id = '{$user_id}'", "last_login = '" . gmtime () . "', last_ip = '" . real_ip () . "', logins = logins + 1" );
		
	}
	
	/* 取得导航 */
	function _get_navs() {
		$cache_server = & cache_server ();
		$key = 'common.navigation';
		$data = $cache_server->get ( $key );
		if ($data === false) {
			$data = array (
					'header' => array (),
					'middle' => array (),
					'footer' => array () 
			);
			$nav_mod = & m ( 'navigation' );
			$rows = $nav_mod->find ( array (
					'order' => 'type, sort_order' 
			) );
			foreach ( $rows as $row ) {
				$data [$row ['type']] [] = $row;
			}
			$cache_server->set ( $key, $data, 86400 );
		}
		
		return $data;
	}
	
	/**
	 * 获取JS语言项
	 *
	 * @author LiuLei
	 * @param
	 *        	none
	 * @return void
	 */
	function jslang() {
		$lang = Lang::fetch ( lang_file ( 'jslang' ) );
		parent::jslang ( $lang );
	}
	
	/**
	 * 视图回调函数[显示小挂件]
	 *
	 * @author LiuLei
	 * @param array $options        	
	 * @return void
	 */
	function display_widgets($options) {
		$area = isset ( $options ['area'] ) ? $options ['area'] : '';
		$page = isset ( $options ['page'] ) ? $options ['page'] : '';
		if (! $area || ! $page) {
			return;
		}
		include_once (APP_PATH . 'Common/Util/widget.base.php');
		
		/* 获取该页面的挂件配置信息 */
		$widgets = get_widget_config ( $this->_get_template_name (), $page );
		
		/* 如果没有该区域 */
		if (! isset ( $widgets ['config'] [$area] )) {
			return;
		}
		
		/* 将该区域内的挂件依次显示出来 */
		foreach ( $widgets ['config'] [$area] as $widget_id ) {
			$widget_info = $widgets ['widgets'] [$widget_id];
			$wn = $widget_info ['name'];
			$options = $widget_info ['options'];
			
			$widget = & widget ( $widget_id, $wn, $options );
			$widget->display ();
		}
	}
	
	/**
	 * 获取当前使用的模板名称
	 *
	 * @author LiuLei
	 * @return string
	 */
	function _get_template_name() {
		return 'default';
	}
	
	/**
	 * 获取当前使用的风格名称
	 *
	 * @author LiuLei
	 * @return string
	 */
	function _get_style_name() {
		return 'default';
	}
	
	/**
	 * 当前位置
	 *
	 * @author LiuLei
	 * @param
	 *        	none
	 * @return void
	 */
	function _curlocal($arr) {
		$curlocal = array (
				array (
						'text' => Lang::get ( 'index' ),
						'url' => SITE_URL . '/index.php' 
				) 
		);
		if (is_array ( $arr )) {
			$curlocal = array_merge ( $curlocal, $arr );
		} else {
			$args = func_get_args ();
			if (! empty ( $args )) {
				$len = count ( $args );
				for($i = 0; $i < $len; $i += 2) {
					$curlocal [] = array (
							'text' => $args [$i],
							'url' => $args [$i + 1] 
					);
				}
			}
		}
		
		$this->assign ( '_curlocal', $curlocal );
	}
	function _init_visitor() {
		$this->visitor = & env ( 'visitor', new UserVisitor () );
		
	}
	
	
	
	/**
	 *
	 * @author liulei
	 * @usage 展示插件（城市选择器，广告图）
	 * @param  	
	 * @return
	 */
	function show_widgt($param) {
		$set = & af ( "settings" );		
		// 广告图
		$this->assign ( "top_broadcast", $set->getOne ( "top_broadcast" ) );
		$this->assign ( "search_right", $set->getOne ( "search_right" ) );
		$this->assign ( "lunbo_right", $set->getOne ( "lunbo_right" ) );
		$sets = $set->getOne ( 'kefus' );
		$kefus = array ();
		for($i = 1; $i <= 7; $i ++) {
			$tel = $sets ['kefu' . $i . '_tel'];
			$temp = array ();
			if ($tel) {
				$temp ['tel'] = $tel;
			}
			$qq = $sets ['kefu' . $i . '_qq'];
			if ($qq) {
				$temp ['qq'] = $qq;
			}
			
			$temp && $kefus [] = $temp;
		}
		
		$this->assign ( "kefus", $kefus );
		// 企业客服
		$this->assign ( "service_key", $set->getOne ( "service_key" ) );
	}
	
	
	function init_cache() {
		$this->s = &cache_server ();
	}
	
}

?>
