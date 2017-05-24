<?php

return array(
    'code'      => 'alipayplat',
    'name'      => Lang::get('alipayplat'),
    'desc'      => Lang::get('alipayplat_desc'),
    'is_online' => '1',
    'author'    => 'TODO@TEAM',
    'website'   => 'http://www.alipay.com',
    'version'   => '1.0',
    'currency'  => Lang::get('alipayplat_currency'),
    'config'    => array(
        'alipayplat_account'   => array(        //账号
            'text'  => Lang::get('alipayplat_account'),
            'desc'  => Lang::get('alipayplat_account_desc'),
            'type'  => 'text',
        ),
    ),
);

?>