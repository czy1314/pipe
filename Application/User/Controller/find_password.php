<?php
/**
 * 找回密码控制器
 * @author cheng
 */
class Find_passwordApp extends MallBaseApp
{
    var $_password_mod;
    function __construct()
    {
        $this->Find_passwordApp();
    }

    function Find_passwordApp()
    {
        parent::FrontendApp();
        $this->_password_mod = &m("member");
    }

    /**
     * 显示文本框及处理提交的用户信息
     *
     */
    function index()
    {
       if(!IS_POST)
       {   if (Conf::get('captcha_status.register'))
           {
               $this->assign('captcha', 1);
           }
           $this->import_resource('jquery.plugins/jquery.validate.js');
           $this->display("find_password.html");
       }
       else
       {
           $addr = $_SERVER['HTTP_REFERER'];
                      
           if (empty($_POST['username']) || empty($_POST['phone_mob']) )
           {
               $this->show_warning("unsettled_required",
                   'go_back', $addr);
               return ;
           }
          
           if (Conf::get('captcha_status.register') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
           {
           		$this->show_warning('captcha_failed');
           		return;
           }
           if (!$_POST['code'])
           {
           	$this->show_warning("手机验证码不能为空！",
           			'go_back', $addr);
           	return ;
           }
           
           $username = trim($_POST['username']);
           $phone_mob = trim($_POST['phone_mob']);
           if( $_SESSION[$phone_mob.'_code'] != crypt(trim($_POST['code']),SALT) && trim($_POST['encode']) != crypt(trim($_POST['code']),SALT)){
             
               $this->show_warning("手机验证码不正确或者失效",
                   'go_back', $addr);
               return ;
           }
           
           $info = $this->_password_mod->get("user_name='{$username}'");
          
           if (empty($info) || $info['phone_mob'] != $phone_mob)
           {
               $this->show_warning('不存在符合此用户名与手机号码的用户',
                   'go_back', $addr);

               return;
           }
            
            $word = $this->_rand();
            $md5word = md5($word);
            $res = $this->_password_mod->get($info['user_id']);
          
            $this->_password_mod->edit($info['user_id'], array('activation' => "{$md5word}"));
            $this->show_message("",'back_index', "index.php?app=find_password&act=set_password&id={$info['user_id']}&activation={$word}");

            return;
       }
    }

    /**
     * 显示设置密码及处理提交的新密码信息
     *
     */
    function set_password()
    {
        if (!IS_POST)
        {
            if (!isset($_GET['id']) || !isset($_GET['activation']) || empty($_GET['activation'])) {
                $this->show_warning("request_error", 'back_index', 'index.php');
                return;
            }
            $id = intval(trim($_GET['id']));
            $activation = trim($_GET['activation']);
            $res = $this->_password_mod->get_info($id);
            if (md5($activation) != $res['activation']) {
                $this->show_warning("invalid_link", 'back_index', 'index.php');
                return;
            }
            $this->assign('id', $id);
            $this->assign('activation', $activation);
            $this->import_resource('jquery.plugins/jquery.validate.js');
            $this->display("set_password.html");
        }
        else
        {
            if (!isset($_POST['id']) || !isset($_POST['activation']) || empty($_POST['activation']))
            {
                $this->show_warning("request_error",
                    'back_index', 'index.php');
                return ;
            }
            $id = intval(trim($_POST['id']));
            $activation = trim($_POST['activation']);
            $res = $this->_password_mod->get_info($id);
            if (md5($activation) != $res['activation'])
            {
                $this->show_warning("invalid_link",
                    'back_index', 'index.php');
                return ;
            }
            
            if (empty($_POST['new_password']) || empty($_POST['confirm_password']))
            {
                $this->show_warning("unsettled_required");
                return ;
            }
            if (trim($_POST['new_password']) != trim($_POST['confirm_password']))
            {
                $this->show_warning("password_not_equal");
                return ;
            }
            $password = trim($_POST['new_password']);
            $passlen = strlen($password);
            if ($passlen < 6 || $passlen > 20)
            {
                $this->show_warning('password_length_error');

                return;
            }

            $id = intval($_GET['id']);
            $word = $this->_rand();
            $md5word = md5($word);
            $old_password=trim($_POST['new_password']);
            
            $ms =& ms();        //连接用户系统
            $ms->user->edit($id, $old_password, array('password' => $password), true); //强制修改
            if ($ms->user->has_error())
            {
                $this->show_warning($ms->user->get_error());

                return;
            }
            $ret = $this->_password_mod->edit($id, array('activation' => $md5word));

            $this->show_message("edit_success",
                'login_in', 'index.php?app=member&act=login',
                'back_index', 'index.php');
            return ;
        }

    }

    /**
     * 构造15位的随机字符串
     *
     * @return string | 生成的字符串
     */
    function _rand()
    {
        $word = $this->generate_code();
        return $word;
    }

    function generate_code($len = 15)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0, $count = strlen($chars); $i < $count; $i++)
        {
            $arr[$i] = $chars[$i];
        }

        mt_srand((double) microtime() * 1000000);
        shuffle($arr);
        $code = substr(implode('', $arr), 5, $len);
        return $code;
    }
}
?>
