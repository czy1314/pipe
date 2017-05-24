<?php

/**
 *    Desc
 *
 *    @author    LorenLei
 *    @usage    none
 */
use \ Application\User\Controller\Frontend;
class MemberApp extends Frontend
{
    var $_feed_enabled = false;
    function __construct()
    {
        $this->MemberApp();
    }
    function MemberApp()
    {
       
        parent::__construct();        
        $ms =& ms();
        $this->_feed_enabled = $ms->feed->feed_enabled();
        $this->assign('feed_enabled', $this->_feed_enabled);
        
    }
    function index()
    { 
        /* 清除新短消息缓存 */
        $cache_server =& cache_server();
        $cache_server->delete('new_pm_of_user_' . $this->visitor->get('user_id'));
        $user = $this->visitor->get();
        $user_mod =& m('member');
        $user = $user_mod->get_info($user['user_id']);     
        
		$this->assign('system_notice', $this->_get_system_notice($_SESSION['member_role']));
		
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    url('app=member'),
                         LANG::get('overview'));

        /* 当前用户中心菜单 */
        $this->_curitem('overview');
        $this->_config_seo('title', Lang::get('member_center'));        
        $this->display('member.index.html');
    }
   
	function _get_system_notice($member_role='buyer_admin')
	{
		
	    $article_cate_id = 1;
		$article_mod = &m('article');
		$acategory_mod = &m('acategory');		
		$cate_ids = $acategory_mod->get_descendant($article_cate_id);
		if($cate_ids){
			$conditions = ' AND cate_id ' . db_create_in($cate_ids);
		} else {
			$conditions = '';
		}
		
		$data = $article_mod->find(array(
			'conditions'=>'code = "" AND if_show=1' . $conditions,
			'fields'=>'article_id, title',
			'limit'=> 5,
			'order'=>'sort_order ASC, article_id DESC'
		));
		return $data;
	}
     function _run_action()
    {
         
		parent::_run_action();
      
    } 
   
    /**
     *    注册一个新用户
     *
     *    @author    LorenLei
     *    @return    void
     */
    function register()
    {
        if ($this->visitor->has_login)
        {
            $this->show_warning('has_login');

            return;
        }
        if (!IS_POST)
        {
            if (!empty($_GET['ret_url']))
            {
                $ret_url = trim($_GET['ret_url']);
            }
            else
            {
                if (isset($_SERVER['HTTP_REFERER']))
                {
                    $ret_url = $_SERVER['HTTP_REFERER'];
                }
                else
                {
                    $ret_url = SITE_URL . '/index.php';
                }
            }
            $this->assign('ret_url', rawurlencode($ret_url));
            $this->_curlocal(LANG::get('user_register'));
            $this->_config_seo('title', Lang::get('user_register') . ' - ' . Conf::get('site_title'));

            if (Conf::get('captcha_status.register'))
            {
                $this->assign('captcha', 1);
            }
            $this->assign_hidden_value();
            /* 导入jQuery的表单验证插件  tyioocom */
            $this->import_resource(array(
            	'script' => 'jquery.plugins/jquery.validate.js,jquery.plugins/poshy_tip/jquery.poshytip.js',
            	'style'  => 'jquery.plugins/poshy_tip/tip-yellowsimple/tip-yellowsimple.css')
			);
            $this->display('member.register.html');
        }
        else
        {
            if (!$_POST['agree'])
            {
                $this->show_warning('agree_first');

                return;
            }
            if (Conf::get('captcha_status.register') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
            {
                $this->show_warning('captcha_failed');
                return;
            }
            if ($_POST['password'] != $_POST['password_confirm'])
            {
                /* 两次输入的密码不一致 */
                $this->show_warning('inconsistent_password');
                return;
            }
            $tel = $_POST['phone_mob'];
            if (!$tel || !is_tel($tel))
            {
            	
            	$this->show_warning('请输入正确的手机号码');
            	return;
            }
            $role = intval($_POST['user_role']);
            if (!$role)
            {
            	 
            	$this->show_warning('请选择用户类型！');
            	return;
            }
            //提醒用户选择所在地区
            if(!$_SESSION['current_store']){
            
            	$this->show_warning('请选择您所在地区');
            	return ;
            
            }
            $code = $_POST['code'];
            if(trim($code))
            {
            	 
            	if(!$_SESSION[$tel.'_code'] == crypt($code,SALT) && trim($_POST['encode']) != crypt(trim($_POST['code']),SALT))
            	{
            		unset($_SESSION[$tel.'_code']);            		
            		$this->show_warning('验证码不正确!');
            		return ;
            	}
            	unset($_SESSION[$tel.'_code']);
            	
            }	
            /* 注册并登陆 */
            $user_name = trim($_POST['user_name']);
            $password  = $_POST['password'];
            $email     = trim($_POST['email']);
            $passlen = strlen($password);
            $user_name_len = strlen($user_name);
            if ($user_name_len < 3 || $user_name_len > 25)
            {
                $this->show_warning('user_name_length_error');

                return;
            }
            if ($passlen < 6 || $passlen > 20)
            {
                $this->show_warning('password_length_error');

                return;
            }
            if (!is_email($email))
            {
                $this->show_warning('email_error');

                return;
            }
            //给推荐人加钱			
			
            $ms =& ms(); //连接用户中心
            $user_id = $ms->user->register($user_name, $password, $email,
            		array(
            			'phone_mob'=>$tel,
            			 //推荐人id，或者称为虚拟商家id	
    					'vstore_id'=>$_SESSION['vstore_id']	,
            			'belong_store'=>$_SESSION['current_store']['store_id'],
            			'select_city'=>$_SESSION['current_store']['city']
            		)
            );
            //删除五级上级的下级缓存，note:注意当数据量大的时候会遇到瓶颈 
            if($_SESSION['vstore_id']){
            	$all_sups = $this->my_sups($user_id);
            	if(is_array($all_sups)){
            		foreach ($all_sups as $height=>$sup)
            		{
            			$this->s->delete($sup['user_id']."_my_subs");
            		}
            	}
            	           	
            }
            $this->addmoney_to_vstore('charges_reg_per',$user_id);
			$rec_model = & m('memberrec');
			//初始化用户认证数据
			$rec_model->add(array('user_id'=>$user_id,'user_role'=>$role,'tel'=>$tel));
			if ($rec_model->has_error())
			{
				$this->show_warning($rec_model->get_error());			
				return;
			}
            if (!$user_id)
            {
                $this->show_warning($ms->user->get_error());

                return;
            }
            $this->_hook('after_register', array('user_id' => $user_id));
            //$this->_do_wxloginrelation($user_id);
            //登录
            $this->_do_login($user_id);
            
            /* 同步登陆外部系统 */
            //$synlogin = $ms->user->synlogin($user_id);

            #TODO 可能还会发送欢迎邮件

            $this->show_message(Lang::get('register_successed'),
                'back_before_register', rawurldecode(strpos($_POST['ret_url'], 'login') !== false ?'index.php?app=member':$_POST['ret_url']),
                'enter_member_center', 'index.php?app=member'
            );
        }
    }


    /**
     *    检查用户是否存在
     *
     *    @author    LorenLei
     *    @return    void
     */
    function check_user()
    {
        $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
        if (!$user_name)
        {
            echo pipe_json_encode(false);

            return;
        }
        $ms =& ms();

        echo pipe_json_encode($ms->user->check_username($user_name));
    }

    /**
     *    修改基本信息
     *
     *    @author    Hyber
     *    @usage    none
     */
    function baseinfo(){
    
    	$user_id = $this->visitor->get('user_id');
    	if (!IS_POST)
    	{
    		/* 当前位置 */
    		$this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
    				LANG::get('basic_information'));
    
    		/* 当前用户中心菜单 */
    		$this->_curitem('my_profile');
    
    		/* 当前所处子菜单 */
    		$this->_curmenu('basic_information');
    
    		$ms =& ms();    //连接用户系统
    		$edit_avatar = $ms->user->set_avatar($this->visitor->get('user_id')); //获取头像设置方式
    
    		$model_user =& m('member');
    		$profile    = $model_user->get_info(intval($user_id));
    		$strs = str_split($profile['phone_mob']);
    		$strs[4] = '*';
    		$strs[5] = '*';
    		$strs[6] = '*';
    		$strs[7] = '*';
    		$profile['phone_mob'] = implode("", $strs);
    		$profile['portrait'] = portrait($profile['user_id'], $profile['portrait'], 'middle');
    		$this->assign('profile',$profile);
    		$this->import_resource(array(
    				'script' => 'jquery.plugins/jquery.validate.js',
    		));
    		
    		$this->assign('edit_avatar', $edit_avatar);
    		$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_profile'));
    		$this->display('member.baseinfo.html');
    	}
    	else
    	{
    		$data = array(
    				'real_name' => $_POST['real_name'],
    				'gender'    => $_POST['gender'],
    				'birthday'  => $_POST['birthday'],
    				'im_qq'     => $_POST['im_qq']
    		);
    
    		if (!empty($_FILES['portrait']))
    		{
    			$portrait = $this->_upload_portrait($user_id);
    			if ($portrait === false)
    			{
    				return;
    			}
    			$data['portrait'] = $portrait;
    		}
    
    		$model_user =& m('member');
    		$model_user->edit($user_id , $data);
    		if ($model_user->has_error())
    		{
    			$this->show_warning($model_user->get_error());
    
    			return;
    		}
    
    		$this->show_message('edit_profile_successed');
    	}
    }
    function check_full($info) {
    	$requrie_fiels  = array('real_name','gender','birthday','im_qq');
    	foreach ($requrie_fiels as $f)
    	{
    		if(!$info[$f])
    		{   			
    			
    			return false;
    		}
    	}
    	return true;
    }
    /**
     *    修改基本信息
     *
     *    @author    Hyber
     *    @usage    none
     */
    function profile(){

        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('basic_information'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
            $this->_curmenu('basic_information');

            $ms =& ms();    //连接用户系统
            $edit_avatar = $ms->user->set_avatar($this->visitor->get('user_id')); //获取头像设置方式

            $model_user =& m('member');
            $profile    = $model_user->get_info(intval($user_id));
           
            $tel = $profile['phone_mob'];
            if($tel)
            {
            	$strs = str_split($tel);
            	$strs[4] = '*';
            	$strs[5] = '*';
            	$strs[6] = '*';
            	$strs[7] = '*';
            	$profile['phone_mob'] = implode("", $strs);
            }
            $profile['portrait'] = portrait($profile['user_id'], $profile['portrait'], 'middle');
            $this->assign('profile',$profile);
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->assign('is_full_info',$this->check_full($profile));
            $this->assign('edit_avatar', $edit_avatar);
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_profile'));
            $this->display('member.profile.html');
        }
        else
        {
            $data = array(
                'real_name' => $_POST['real_name'],
                'gender'    => $_POST['gender'],
                'birthday'  => $_POST['birthday'],
                //'im_msn'    => $_POST['im_msn'],
                'im_qq'     => $_POST['im_qq'],
            	//'phone_mob' => $_POST['phone_mob']
            );

            if (!empty($_FILES['portrait']))
            {
                $portrait = $this->_upload_portrait($user_id);
                if ($portrait === false)
                {
                    return;
                }
                $data['portrait'] = $portrait;
            }

            $model_user =& m('member');
            $model_user->edit($user_id , $data);
            if ($model_user->has_error())
            {
                $this->show_warning($model_user->get_error());

                return;
            }

            $this->show_message('edit_profile_successed');
        }
    }
    
   
    /**
     *    修改密码
     *
     *    @author    Hyber
     *    @usage    none
     */
    function password(){
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('edit_password'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
            $this->_curmenu('edit_password');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_password'));
            $this->display('member.password.html');
        }
        else
        {
            /* 两次密码输入必须相同 */
            $orig_password      = $_POST['orig_password'];
            $new_password       = $_POST['new_password'];
            $confirm_password   = $_POST['confirm_password'];
            if ($new_password != $confirm_password)
            {
                $this->show_warning('twice_pass_not_match');

                return;
            }
            if (!$new_password)
            {
                $this->show_warning('no_new_pass');

                return;
            }
            $passlen = strlen($new_password);
            if ($passlen < 6 || $passlen > 20)
            {
                $this->show_warning('password_length_error');

                return;
            }

            /* 修改密码 */
            $ms =& ms();    //连接用户系统
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'password'  => $new_password
            ));
            if (!$result)
            {
                /* 修改不成功，显示原因 */
                $this->show_warning($ms->user->get_error());

                return;
            }

            $this->show_message('edit_password_successed');
        }
    }
    /**
     *    修改电子邮箱
     *
     *    @author    Hyber
     *    @usage    none
     */
    function email(){
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'),  'index.php?app=member',
                             LANG::get('edit_email'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
            $this->_curmenu('edit_email');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_email'));
            $this->display('member.email.html');
        }
        else
        {
            $orig_password  = $_POST['orig_password'];
            $email          = isset($_POST['email']) ? trim($_POST['email']) : '';
            if (!$email)
            {
                $this->show_warning('email_required');

                return;
            }
            if (!$orig_password)
            {
            	$this->show_warning('password_required');
            
            	return;
            }
           
            if (!is_email($email))
            {
                $this->show_warning('email_error');

                return;
            }
            
            $ms =& ms();    //连接用户系统
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'email' => $email
            ));
            if (!$result)
            {
                $this->show_warning($ms->user->get_error());

                return;
            }

            $this->show_message('edit_email_successed');
        }
    }

    /**
     * Feed设置
     *
     * @author LorenLei
     * @param
     * @return void
     **/
   
     /**
     *    三级菜单
     *
     *    @author    Hyber
     *    @return    void
     */
    function _get_member_submenu()
    {
        $submenus =  array(
            array(
                'name'  => 'basic_information',
                'url'   => 'index.php?app=member&amp;act=profile',
            ),
            array(
                'name'  => 'edit_password',
                'url'   => 'index.php?app=member&amp;act=password',
            ),
            array(
                'name'  => 'edit_email',
                'url'   => 'index.php?app=member&amp;act=email',
            ),
        );
       

        return $submenus;
    }

    /**
     * 上传头像
     *
     * @param int $user_id
     * @return mix false表示上传失败,空串表示没有上传,string表示上传文件地址
     */
    function _upload_portrait($user_id)
    {
        $file = $_FILES['portrait'];
        if ($file['error'] != UPLOAD_ERR_OK)
        {
            return '';
        }
        import('uploader');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->addFile($file);
        if ($uploader->file_info() === false)
        {
            $this->show_warning($uploader->get_error(), 'go_back', 'index.php?app=member&amp;act=profile');
            return false;
        }
        $uploader->root_dir(ROOT_PATH);
        return $uploader->save('data/files/mall/portrait/' . ceil($user_id / 500), $user_id);
    }

    function ajax_get_code_find_pwd()
    {

        $tel = trim($_POST['phone_mob']);
        if(is_tel($tel))
        {
            $code = get_code(6);
            $encode = crypt($code,SALT);
            $_SESSION[$tel.'_code'] = $encode;
            $result = $this->send_msg($tel,'您正在修改登录密码，验证码为：'.$code);

            if($result){

                $this->pop_message_($encode);
            }else{
                $this->pop_message_('获取验证码失败');
            }

            return ;
        }
        else{
            $this->pop_warning_('请填写正确的手机号码！');
            return ;
        }

    }
}

?>
