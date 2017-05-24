<?php

return array(
    'code'      => 'wxjsapi',
    'name'      => Lang::get('wxjsapi'),
    'desc'      => Lang::get('wxjsapi_desc'),
    'is_online' => '1',
    'author'    => 'vchuangcn.taobao.com',
    'website'   => 'http://mp.weixin.qq.com',
    'version'   => '1.0',
    'currency'  => Lang::get('wxjsapi_currency'),
    'config'    => array(
        'appid'   => array(        //AppId
            'text'  => Lang::get('appid'),
            'desc'  => Lang::get('appid_desc'),
            'type'  => 'text',
        ),
		'mchid'   => array(        //Mchid
            'text'  => Lang::get('mchid'),
            'desc'  => Lang::get('mchid_desc'),
            'type'  => 'text',
        ),
		'key'   => array(        //key
            'text'  => Lang::get('key'),
            'desc'  => Lang::get('key_desc'),
            'type'  => 'text',
        ),
		'appsecret'   => array(        //Appsecret
            'text'  => Lang::get('appsecret'),
            'desc'  => Lang::get('appsecret_desc'),
            'type'  => 'text',
        )
    ),
);

?>