<?php
/**
 *    前台控制器基础类
 *
 *    @author    LorenLei
 *    @usage    none
 */
namespace  Application\User\Controller;
use \Common\Controller\BaseVisitor;
use \Common\Controller\ZmApp;
class Frontend extends ZmApp {
	var $s;
	function __construct() {
		$this->FrontendApp ();
	}
	function FrontendApp() {
		////Lang::core_load ( lang_file ( 'common' ) );
		////Lang::core_load ( lang_file ( APP ) );
		parent::__construct ();
	}
	function _run_action() {
		
		$this->init_cache();
		parent::_run_action ();
	}


	
	function init_cache() {
		$this->s = &cache_server ();
	}
	
}

?>
