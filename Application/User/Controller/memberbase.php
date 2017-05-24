<?php
/**
 * 用户中心子系统基础类
 *
 * @author LorenLei
 *         @usage none
 */
namespace Application\User\Controller;
use  Application\User\Controller\Frontend;
class Memberbase extends  Frontend{
    function _run_action() {

        /* 只有登录的用户才可访问 */
        if (! $this->visitor->has_login && ! in_array ( ACT, array (
                'login',
                'ajax_get_code_',
                'register',
                'check_user'
            ) )) {
            if (! IS_AJAX) {

                header ( 'Location:index.php?app=member&act=login&ret_url=' . rawurlencode ( $_SERVER ['PHP_SELF'] . '?' . $_SERVER ['QUERY_STRING'] ) );

                return;
            } else {
                $this->json_error ( 'login_please' );
                return;
            }
        }

        parent::_run_action ();
    }
    /**
     * 当前选中的菜单项
     *
     * @author LorenLei
     * @param string $item
     * @return void
     */
    function _curitem($item) {
        $this->assign ( 'has_store', 1 );
        // psmb
        $member_menu = $this->_get_member_menu();
        if ($this->visitor->check_do_action () === false) {

            $this->assign ( 'member_role', 'buyer_admin' );

        }

        $this->assign ( '_member_menu', $member_menu );
        $this->assign ( '_curitem', $item );
    }
    /**
     * 当前选中的子菜单
     *
     * @author LorenLei
     * @param string $item
     * @return void
     */
    function _curmenu($item) {
        $_member_submenu = $this->_get_member_submenu ();

        foreach ( $_member_submenu as $key => $value ) {
            $_member_submenu [$key] ['text'] = $value ['text'] ? $value ['text'] : Lang::get ( $value ['name'] );
        }

        $this->assign ( '_member_submenu', $_member_submenu );
        $this->assign ( '_curmenu', $item );
    }
    /**
     * 获取子菜单列表
     *
     * @author LorenLei
     * @param
     *        	none
     * @return void
     */
    function _get_member_submenu() {
        return array ();
    }
    /**
     * 获取用户中心全局菜单列表
     *
     * @author LorenLei
     * @param
     *        	none
     * @return void
     */
    function _get_member_menu() {
        $menu = array ();

        /* 我的Boot */
        $menu ['my_Boot'] = array (
            'name' => 'my_Boot',
            'text' => '个人中心',
            'submenu' => array (
                'overview' => array (
                    'text' => Lang::get ( 'overview' ),
                    'url' => 'index.php?app=member',
                    'name' => 'overview',
                    'icon' => 'ico1 icon-book'
                ),

                'my_profile' => array (
                    'text' => '修改资料',
                    'url' => 'index.php?app=member&act=profile',
                    'name' => 'my_profile',
                    'icon' => 'ico1 icon-picture'
                ),
                'message' => array (
                    'text' => Lang::get ( 'message' ),
                    'url' => 'index.php?app=message&act=newpm',
                    'name' => 'message',
                    'icon' => 'ico1  icon-info-sign'
                ),

                'my_wxconfig'  => array(
                    'text'  => '接口配置',
                    'url'   => 'index.php?app=my_wxconfig',
                    'name'  => 'my_wxconfig',
                    'icon'  => 'ico1 icon-cog',
                ),
                'to_send'  => array(
                    'text'  => '发送模板消息',
                    'url'   => 'index.php?app=wxmb',
                    'name'  => 'to_send',
                    'icon'  => 'ico1 icon-fighter-jet',
                ),
                /* 'mb_manage'  => array(
                        'text'  => '模板管理',
                        'url'   => 'index.php?app=mb_manage',
                        'name'  => 'mb_manage',
                        'icon'  => 'ico1 icon-manage',
                ) */

            )
        );


        return $menu;
    }
}
