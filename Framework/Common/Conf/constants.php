<?php
/* 记录程序启动时间 */
define ( 'START_TIME', pipe_microtime () );

/* 判断请求方式 */
define ( 'IS_POST', (strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST') );

/* 判断请求方式 */
define ( 'IN_ECM', true );

/* 定义PHP_SELF常量 */
define ( 'PHP_SELF', htmlentities ( isset ( $_SERVER ['PHP_SELF'] ) ? $_SERVER ['PHP_SELF'] : $_SERVER ['SCRIPT_NAME'] ) );

/* 当前Boot程序版本 */
define ( 'VERSION', '2.3.0' );

/* 当前Boot程序Release */
define ( 'RELEASE', '20120918' );
define ( 'VERSION', '2.3.0' );
