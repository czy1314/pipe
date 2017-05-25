<?php 
use \ Framework\Controller\BaseController;
use \ Application\User\Model\MemberModel;
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

        var_dump(new MemberModel());

		echo 'Welcome Pipe!';
        
    }
}