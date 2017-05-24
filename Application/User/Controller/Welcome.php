<?php 
use \ Framework\Controller\BaseController;
class Welcome extends BaseController
{
	function __construct()
    {
        parent::__construct();
    }
    function Welcome()
    {
        parent::__construct();  
        
    }
    function index()
    { 
		echo 'Welcome Pipe!';
        
    }
}