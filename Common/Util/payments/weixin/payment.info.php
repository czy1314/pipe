<?php

return array(
    'code'      => 'weixin',
    'name'      => Lang::get('weixin'),
    'desc'      => Lang::get('weixin_desc'),
    'is_online' => '1',
    'author'    => 'vchuangcn.taobao.com',
    'website'   => 'http://mp.weixin.qq.com',
    'version'   => '1.0',
    'currency'  => Lang::get('weixin_currency'),
    'config'    => array(
        'weixin_appid'   => array(        //账号
            'text'  => Lang::get('weixin_appid'),
            'desc'  => Lang::get('weixin_appid_desc'),
            'type'  => 'text',
        ),
		'weixin_paySignkey'   => array(        //账号
            'text'  => Lang::get('weixin_paySignkey'),
            'desc'  => Lang::get('weixin_paySignkey_desc'),
            'type'  => 'text',
        ),
		'weixin_appSecret'   => array(        //账号
            'text'  => Lang::get('weixin_appSecret'),
            'desc'  => Lang::get('weixin_appSecret_desc'),
            'type'  => 'text',
        ),
		'weixin_partnerId'   => array(        //账号
            'text'  => Lang::get('weixin_partnerId'),
            'desc'  => Lang::get('weixin_partnerId_desc'),
            'type'  => 'text',
        ),
		'weixin_partnerKey'   => array(        //账号
            'text'  => Lang::get('weixin_partnerKey'),
            'desc'  => Lang::get('weixin_partnerKey_desc'),
            'type'  => 'text',
        )
 
    ),
);

?>