<?php 
use  \Common\Controller\HiController;
use \ Application\User\Model\MemberModel;
use \ Framework\Util\Input;
class Welcome extends HiController
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
        $input = new Input();
        $data = array(
            'sEmail'=>$input->post('sEmail'),
            'sPassword'=> $input->post('sPassword'),
            'sNickName'=> $input->post('sNickName')
        );
        $userModel = new MemberModel();
        $userModel->check($data);
        $hasOne = $userModel->find(array('count'=>true,'conditions'=>"sEmail='{$data['sEmail']}'"));
        if($hasOne){
            $this->jsonResult(-1,'该邮箱已经被注册~');
        }
        $hasOne = $userModel->find(array('count'=>true,'conditions'=>"sNickName='{$data['sNickName']}'"));
        if($hasOne){
            $this->jsonResult(-1,'该昵称已经被使用~');
        }
        $data['sSalt'] = md5(json_encode($data).microtime());
        $data['sPassword'] = crypt($data['sPassword'],$data['sSalt']);
        if($userModel->save($data)){
            $this->jsonResult(0,'注册成功~');
        }else{
            $this->jsonResult(-1,'注册失败~');
        }
        
    }
    function login()
    {
        $input = new Input();
        $data = array(
            'sEmail'=>$input->post('sEmail'),
            'sPassword'=> $input->post('sPassword')
        );
        $userModel = new MemberModel();
        $userModel->check($data,false);
        $hasOne = $userModel->getOne(array('conditions'=>"sEmail='{$data['sEmail']}'"));
        if(!$hasOne){
            $this->jsonResult(-1,'用户不存在，请注册之后再登录~');
        }
        $pass = crypt($data['sPassword'],$hasOne['sSalt']);
        if($pass == $hasOne['sPassword']){
            $this->jsonResult(0,'登录成功！');
            unset($hasOne['sSalt']);
            unset($hasOne['sPassword']);
            $_SESSION['userInfo'] = $hasOne;
        }else{
            $this->jsonResult(-1,'密码错误，请重新登录再试~');
        }

    }

}