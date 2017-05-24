<?php

/* 微公众平台关键词自动回复管理控制器 */
class Mb_manageApp extends MemberBaseApp {
	var $wxfile_mod;
	var $template_id = 0;
	function __construct() {
		$this->Mb_manageApp ();
	}
	function Mb_manageApp() {
		parent::__construct ();
		
	}
	/**
	*@author	LorenLei
	*@usage		查看所有模板
	*@param
	*@return   
	*/
	function index() {
		;
	}
	/**
	*@author	LorenLei
	*@usage		添加模板
	*@param
	*@return   
	*/
	function add() {
		/* 当前位置 */
		$this->_curlocal(LANG::get('member_center'), 'index.php?app=member', '模板管理', 'index.php?app=mb_manage', '添加模板');
		/* 当前用户中心菜单 */
		$this->_curitem('mb_manage');
		$this->_config_seo('title', Lang::get('member_center') . ' - 添加模板');
		$this->display('wxmb.add.form.html');
	}
	
	/**
	*@author	LorenLei
	*@usage		删除模板
	*@param     $_POST['mb_id'] 
	*@return    boolean|string
	*/
	function del() {
		;
	}
	
}

?>