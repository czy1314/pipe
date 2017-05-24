<?php
/**
 *    访问者基础类，集合了当前访问用户的操作
 *
 * @author    LorenLei
 * @return    void
 */
namespace \Common\Conctroller;

class HiController extends Object
{
    var $has_login = false;
    var $info = null;
    var $privilege = null;
    var $_info_key = '';

    function __construct()
    {
        $this->BaseVisitor();
    }


    function login()
    {
        $this->display('login.html');
    }
    function logout()
    {
        $this->visitor->logout();
    }



    function display($tpl)
    {

        /* 新消息 */
        $this->assign('new_message', isset ($this->visitor) ? $this->_get_new_message() : '');
        $init = new Init_FrontendApp();
        $this->assign('navs', $this->_get_navs()); // 自定义导航
        $this->assign('acc_help', ACC_HELP); // 帮助中心分类code
        $this->assign('site_title', Conf::get('site_title'));
        $this->assign('site_logo', Conf::get('site_logo'));
        $this->assign('statistics_code', Conf::get('statistics_code')); // 统计代码
        $current_url = explode('/', $_SERVER ['REQUEST_URI']);
        $count = count($current_url);
        $this->assign('current_url', $count > 1 ? $current_url [$count - 1] : $_SERVER ['REQUEST_URI']); // 用于设置导航状态(以后可能会有问题)

        // 广告图，城市选择器
        $this->show_widgt(1);
        // 表单隐藏域
        $this->auto_hidden();
        $user = $this->visitor->get();
        $user_mod =& m('member');
        $user = $user_mod->get_info($user['user_id']);
        $user['portrait'] = portrait($user['user_id'], $user['portrait'], 'middle');
        $this->assign('user', $user);

        $this->_config_seo(array(
            'title' => Conf::get('site_title'),
            'description' => Conf::get('site_description'),
            'keywords' => Conf::get('site_keywords')
        ));
        parent::display($tpl);

    }

    /**
     * 导入jq.ui.js，dialog.js jq.ui.css jq.validate.js
     */
    function import_resource_jqui()
    {
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"'
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => ''
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => ''
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => ''
                )
            ),
            'style' => 'jquery.ui/templates/ui-lightness/jquery.ui.css'
        ));
    }

    function goheader($oauth_url)
    {
        header('Expires: 0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cahe, must-revalidate');
        header('Cache-Control: post-chedk=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $oauth_url");
        exit ();
    }

    function getcode_by_redirect()
    {
        // return;
        if (!$this->visitor->has_login) {
            $s = &cache_server();
            if ($s->get(session_id() . '_code')) {
                $_GET ['code'] = $s->get(session_id() . '_code');
                $this->_wxautologin();
                return;
            }
            $back_url = SITE_URL . '/index.php?app=member&act=login&ret_url=' . $_GET ['ret_url'];
            $redirect_uri = urlencode($back_url);
            $state = 'wechat';
            $scope = 'snsapi_base';
            $wxconfig = $this->init_wxconfig();
            $oauth_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $wxconfig ['appid'] . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=' . $scope . '&state=' . $state . '#wechat_redirect';
            header('Expires: 0');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cahe, must-revalidate');
            header('Cache-Control: post-chedk=0, pre-check=0', false);
            header('Pragma: no-cache');
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: $oauth_url");
        }

        return;
    }


    /* 执行登录动作 */
    function _do_login($user_id)
    {
        $mod_user = &m('member');

        $user_info = $mod_user->get(array(
            'conditions' => "user_id = '{$user_id}'",
            'fields' => 'user_id, user_name, reg_time, last_login, last_ip'
        ));


        /* 分派身份 */
        $this->visitor->assign($user_info);
        /* 更新用户登录信息 */
        $mod_user->edit("user_id = '{$user_id}'", "last_login = '" . gmtime() . "', last_ip = '" . real_ip() . "', logins = logins + 1");

    }

    /* 取得导航 */
    function _get_navs()
    {
        $cache_server = &cache_server();
        $key = 'common.navigation';
        $data = $cache_server->get($key);
        if ($data === false) {
            $data = array(
                'header' => array(),
                'middle' => array(),
                'footer' => array()
            );
            $nav_mod = &m('navigation');
            $rows = $nav_mod->find(array(
                'order' => 'type, sort_order'
            ));
            foreach ($rows as $row) {
                $data [$row ['type']] [] = $row;
            }
            $cache_server->set($key, $data, 86400);
        }

        return $data;
    }

    /**
     * 获取JS语言项
     *
     * @author LorenLei
     * @param
     *            none
     * @return void
     */
    function jslang()
    {
        ////$lang = Lang::fetch ( lang_file ( 'jslang' ) );
        ////parent::jslang ( $lang );
    }


    /**
     * 当前位置
     *
     * @author LorenLei
     * @param
     *            none
     * @return void
     */
    function _curlocal($arr)
    {
        $curlocal = array(
            array(
                'text' => Lang::get('index'),
                'url' => SITE_URL . '/index.php'
            )
        );
        if (is_array($arr)) {
            $curlocal = array_merge($curlocal, $arr);
        } else {
            $args = func_get_args();
            if (!empty ($args)) {
                $len = count($args);
                for ($i = 0; $i < $len; $i += 2) {
                    $curlocal [] = array(
                        'text' => $args [$i],
                        'url' => $args [$i + 1]
                    );
                }
            }
        }

        $this->assign('_curlocal', $curlocal);
    }

    function _init_visitor()
    {
        $this->visitor = &env('visitor', new UserVisitor ());

    }


}

?>