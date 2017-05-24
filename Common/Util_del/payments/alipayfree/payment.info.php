<?php

return array(
    'code'      => 'alipayfree',
    'name'      => Lang::get('alipayfree'),
    'desc'      => Lang::get('alipayfree_desc'),
    'is_online' => '1',
    'author'    => 'TODO@TEAM',
    'website'   => 'http://www.alipay.com',
    'version'   => '1.0',
    'currency'  => Lang::get('alipay_currency'),
    'config'    => array(
        'alipayfree_account'   => array(        //账号
            'text'  => Lang::get('alipayfree_account'),
            'desc'  => Lang::get('alipayfree_account_desc'),
            'type'  => 'text',
        ),
    ),
);

?>