<?php 
function __autoload($class) {
	
 	require_once("D:\phpStudy\WWW\wxmb\\".$class.'.php'); 
}
use \Application\User\Controller\testA;
$a = new testA();
        echo '<pre>';
    	echo date('Y-y-d h:i:s',time()).'<br/>';
    	var_dump($a);
    	echo '</pre>';
    	exit;


?>