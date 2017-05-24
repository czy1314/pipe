<?php
namespace   Framework\lib;
class OrderPush
{
	public   $appid;
	public   $secrect;
	public static    $accessToken;
	/*获得所有用户的openid*/
	var $total_data = array();
	var $next_openid = '';
	var $sended_count = 0;
	function  __construct($appid='', $secrect='')
	{
		$this->appid = $appid;
		$this->secrect = $secrect;
		if(!self::$accessToken){
			self::$accessToken = $this->getToken($appid, $secrect);
		}
		
	}

	/**
	 * 发送post请求
	 * @param string $url
	 * @param string $param
	 * @return bool|mixed
	 */
	function request_post($url = '', $param = '')
	{
		if (empty($url) || empty($param)) {
			return false;
		}
		$postUrl = $url;
		$curlPost = $param;
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
		curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
		curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);		
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);		
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$data = curl_exec($ch); //运行curl
		
		curl_close($ch);
		
		return $data;
	}


	/**
	 * 发送get请求
	 * @param string $url
	 * @return bool|mixed
	 */
	function request_get($url = '')
	{
		if (empty($url)) {
		
			return false;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$data = curl_exec($ch);
		
		curl_close($ch);
		
		return $data;
	}

	/**
	 * @param $appid
	 * @param $appsecret
	 * @return mixed
	 * 获取token 一个token生存期为7200秒 即两个小时
	 */
    function getToken($appid='', $appsecret='')
	{       include './token.php';
		    if(isset($token['time']) && (time()-$token['time'] - 7000) < 0){
		    	
		    	return $token['data'];
		    }
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
		    
		    $token = $this->request_get($url);
			$token = json_decode(stripslashes($token));
			$arr = json_decode(json_encode($token), true);
			$access_token = $arr['access_token'];	
	       
	    	if($access_token){
	    		file_put_contents('./token.php','<?php return $token ='.var_export(array('time'=>time(),'data'=>$access_token),true).';?>');
	    	}
	    		
		    return $access_token;
	}


	/**
	 * 发送自定义的模板消息
	 * @param $touser
	 * @param $template_id
	 * @param $url
	 * @param $data
	 * @param string $topcolor
	 * @return bool
	 */
	public function doSend($touser, $template_id, $url, $data, $topcolor = '#7B68EE')
	{
        if(!self::$accessToken){
        	echo "<script>alert('token令牌失效，刷新,重新发送');</script>";
        	return false;
        }
		
		$template = array(
				'touser' => $touser,
				'template_id' => $template_id,
				'url' => $url,
				'topcolor' => $topcolor,
				'data' => $data
		);
		$json_template = json_encode($template);
		
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . self::$accessToken;
		//$dataRes = json_decode($this->request_post($url,urldecode($json_template)),true);
		
		$s = file_get_contents('http://www.baidu.com');
		$dataRes = array('errcode'=>0);	
		$this->sended_count++;
			    		
		if (isset($dataRes['errcode']) && $dataRes['errcode'] == 0) {
			
			return true;
		} else {
			if($this->sended_count<3){
				$this->doSend($touser, $template_id, $url, $data,'#7B68EE');
					
			}
			//超过n次，则认为发送失败
			if($this->sended_count >= 3)
			{
				if($dataRes['errcode'] == 45009){
					echo "<script>alert('公众号接口访问次数到达了上限,一日内无法再进行访问!');</script>";
					exit;
				}
				return false;
			}
		}
	}
	var $sended_count_openids = 0;
	public function get_user_openids($next_openid = ''){
		
		$this->next_openid = !empty($next_openid)? $next_openid: $this->next_openid;
		$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . self::$accessToken . '&next_openid='.$this->next_openid;
		$dataRes = json_decode($this->request_get($url),true);
		$this->sended_count_openids++;
		if (!$dataRes || (isset($dataRes['errcode']) && $dataRes['errcode'] != 0)) {
			
			if($this->sended_count_openids < 3){
				$this->get_user_openids($this->next_openid);
					
			}
			//超过n次，则认为发送失败
			if($this->sended_count_openids == 3)
			{
				if(!empty($this->total_data)){
					return $this->total_data;
				}
				return false;
			}
		} else {
			
			if($dataRes['count'] > 0){
				$this->total_data = array_merge($dataRes['data']['openid'],$this->total_data);		
				
				if($dataRes['next_openid']){
					$this->next_openid = $dataRes['next_openid'];
					$this->get_user_openids();
				}
			}
			$this->next_openid = 0;
			return $this->total_data;
		}
	}
}
?>