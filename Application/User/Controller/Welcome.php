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
        $this->load('input');
        var_dump($this);
        $userModel = new MemberModel();
        var_dump($userModel->test());
		echo 'Welcome Pipe!';
        
    }
}