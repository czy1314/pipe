<?php
/**
 *    访问者基础类，集合了当前访问用户的操作
 *
 *    @author    LorenLei
 *    @return    void
 */
namespace\Common\Conctroller;
class BaseVisitor extends Object
{
    var $has_login = false;
    var $info      = null;
    var $privilege = null;
    var $_info_key = '';
    function __construct()
    {
        $this->BaseVisitor();
    }
    function BaseVisitor()
    {
    	
        if (!empty($_SESSION[$this->_info_key]['user_id']))
        {
            $this->info         = $_SESSION[$this->_info_key];
            $this->has_login    = true;
           
        }
        else
        {
        	      
            $this->info         = array(
                'user_id'   => 0,
                'user_name' => Lang::get('guest')
            );
            $this->has_login    = false;
        }
    }
    function assign($user_info)
    {
        $_SESSION[$this->_info_key]   =   $user_info;
    }

 /**
     *    获取当前登录用户的详细信息
     *
     *    @author    LorenLei
     *    @return    array      用户的详细信息
     */
    function get_detail()
    {  
        /* 未登录，则无详细信息 */
        if (!$this->has_login)
        {
            return array();
        }
       
        /* 取出详细信息 */
        static $detail = null;
        
        if ($detail == null)
        {
            $detail = $this->_get_detail();
        }
       
        return $detail;
    }

    /**
     *    获取用户详细信息
     *
     *    @author    LorenLei
     *    @return    array
     */
    function _get_detail()
    {
        $model_member=m('member');
               
        /* 获取当前用户的详细信息，包括权限 */
        $member_info = $model_member->get(array(
            'conditions'    => "member.user_id = '{$this->info['user_id']}'",
           
            'fields'        => 'email, password, real_name, logins, ugrade, portrait, feed_config',
           
        	));
        
        return $member_info;
    }
    /**
     *    获取当前用户的指定信息
     *
     *    @author    LorenLei
     *    @param     string $key  指定用户信息
     *    @return    string  如果值是字符串的话
     *               array   如果是数组的话
     */
    function get($key = null)
    {
        $info = null;

        if (empty($key))
        {
            /* 未指定key，则返回当前用户的所有信息：基础信息＋详细信息 */
            $info = array_merge((array)$this->info, (array)$this->get_detail());
                   
        }
        else
        { 
       
            /* 指定了key，则返回指定的信息 */
            if (isset($this->info[$key]))
            {
                /* 优先查找基础数据 */
            	
                $info = $this->info[$key];
                
            }
            else
            {
            	
                /* 若基础数据中没有，则查询详细数据 */
                $detail = $this->get_detail();
                $info = isset($detail[$key]) ? $detail[$key] : null;
                
            }
        }

        return $info;
    }

    /**
     *    登出
     *
     *    @author    LorenLei
     *    @return    void
     */
    function logout()
    {
        session_destroy();
    }
    function i_can($event, $privileges = array())
    {
        $fun_name = 'check_' . $event;

        return $this->$fun_name($privileges);
    }
    //获取用户信息包括权限
    function get_priv()
    {
    	$model_member=m('member');   
    		 
    	$member_info = $model_member->findAll(array(
    			'join'   		=> 'manage_mall',  
    			'conditions'    => "member.user_id = '{$this->info['user_id']}'",
    			'fields'        => 'email, password, real_name, logins, ugrade, portrait,
    					 privs, feed_config'
    	));
    	       
    	return $detail = current($member_info);    	
    }   
    //根据权限字符串验证权限
    /**
     * 如果是店主则返回店铺id，如果是超级管理员，则返回true ,$_SESSION['is_manager'] = true,
     * 如果此行为在白名单，则返回true，否则返回false，代表用户没有任何权限.
     * @return mixed|boolean
     */
    function check_do_action()
    {
        return true;	
    }

}
?>