<?php
/**
 * @author    LorenLei
 * @return    void
 */
namespace Common\Controller;
use \ Framework\Controller\BaseController;
class HiController extends BaseController
{
    var $has_login = false;
    var $info = null;
    var $privilege = null;
    var $_info_key = '';
    function __construct(){
        $this->HiController();
    }
    function HiController(){
        parent::__construct();
        if(!$this->isLogin()){
            $this->_error('登陆态失效，请重新登录~');
            exit;
        }
    }
    function isLogin()
    {
       return isset($_SESSION['userInfo']);
    }


}

?>