<?php
namespace   Framework\lib;
class Init_Weixin
{
	 function uploadfile($para)
	 {
		import('image.func');
		import('uploader');
		$uploader = new Uploader();
		$uploader->allowed_type(IMAGE_FILE_TYPE);
		$uploader->allowed_size(2097152); // 2M
		$files = $para; //$_FILES['activity_banner'];
		if ($files['error'] == UPLOAD_ERR_OK)
		{
				  /* 处理文件上传 */
				  $file = array(
					  'name'      => $files['name'],
					  'type'      => $files['type'],
					  'tmp_name'  => $files['tmp_name'],
					  'size'      => $files['size'],
					  'error'     => $files['error']
				  );
				  $uploader->addFile($file);
				  if (!$uploader->file_info())
				  {
					  $data = current($uploader->get_error());
					  $res = Lang::get($data['msg']);
					  $this->view_iframe();
					  echo "<script type='text/javascript'>alert('{$res}');</script>";
					  return false;
				  }
  
				  $uploader->root_dir(ROOT_PATH);
				  $dirname = 'data/files/mall/weixin';
				  $filename  = $uploader->random_filename();
				  $file_path = $uploader->save($dirname, $filename);
				  
		}
		return $file_path;
	 }
	
	  function _return_mimetype($filename)
	  {
		  preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);
		  switch(strtolower($fileSuffix[1]))
		  {
			  case "js" :
				  return "application/x-javascript";
  
			  case "json" :
				  return "application/json";
  
			  case "jpg" :
			  case "jpeg" :
			  case "jpe" :
				  return "image/jpeg";
  
			  case "png" :
			  case "gif" :
			  case "bmp" :
			  case "tiff" :
				  return "image/".strtolower($fileSuffix[1]);
  
			  case "css" :
				  return "text/css";
  
			  case "xml" :
				  return "application/xml";
  
			  case "doc" :
			  case "docx" :
				  return "application/msword";
  
			  case "xls" :
			  case "xlt" :
			  case "xlm" :
			  case "xld" :
			  case "xla" :
			  case "xlc" :
			  case "xlw" :
			  case "xll" :
				  return "application/vnd.ms-excel";
  
			  case "ppt" :
			  case "pps" :
				  return "application/vnd.ms-powerpoint";
  
			  case "rtf" :
				  return "application/rtf";
  
			  case "pdf" :
				  return "application/pdf";
  
			  case "html" :
			  case "htm" :
			  case "php" :
				  return "text/html";
  
			  case "txt" :
				  return "text/plain";
  
			  case "mpeg" :
			  case "mpg" :
			  case "mpe" :
				  return "video/mpeg";
  
			  case "mp3" :
				  return "audio/mpeg3";
  
			  case "wav" :
				  return "audio/wav";
  
			  case "aiff" :
			  case "aif" :
				  return "audio/aiff";
  
			  case "avi" :
				  return "video/msvideo";
  
			  case "wmv" :
				  return "video/x-ms-wmv";
  
			  case "mov" :
				  return "video/quicktime";
  
			  case "rar" :
				  return "application/x-rar-compressed";
  
			  case "zip" :
			  return "application/zip";
  
			  case "tar" :
				  return "application/x-tar";
  
			  case "swf" :
				  return "application/x-shockwave-flash";
  
			  default :
			  if(function_exists("mime_content_type"))
			  {
				  $fileSuffix = mime_content_type($filename);
			  }
			  return "unknown/" . trim($fileSuffix[0], ".");
		  }
	  }
	  
	  /**
     * 微信菜单AJAX返回数据标准
     *
     * @param int $status
     * @param string $msg
     * @param mixed $data
     * @param string $dialog
     */
    function ajaxReturn($status=1, $msg='', $data='', $dialog='') {
        $data = array(
            'status' => $status,
            'msg' => $msg,
            'data' => $data,
            'dialog' => $dialog,
        );
		header('Content-Type:text/html; charset=utf-8');
        exit(json_encode($data));
    }
	
public static function curl_redir_exec ($ch,$debug="")
{
static $curl_loops = 0;
static $curl_max_loops = 20;
if ($curl_loops++ >= $curl_max_loops)
{
$curl_loops = 0;
return FALSE;
}
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
$debbbb = $data;
list($header, $data) = explode("＼n＼n", $data, 2);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($http_code == 301 || $http_code == 302) {
$matches = array();
preg_match('/Location:(.*?)＼n/', $header, $matches);
$url = @parse_url(trim(array_pop($matches)));
//print_r($url);
if (!$url)
{
//couldn't process the url to redirect to
$curl_loops = 0;
return $data;
}
$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
/*    if (!$url['scheme'])
$url['scheme'] = $last_url['scheme'];
if (!$url['host'])
$url['host'] = $last_url['host'];
if (!$url['path'])
$url['path'] = $last_url['path'];*/
$new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:'');
curl_setopt($ch, CURLOPT_URL, $new_url);
//    debug('Redirecting to', $new_url);
return curl_redir_exec($ch);
} else {
$curl_loops=0;
return $debbbb;
}
}
	function curl($appid,$secret)
    {
		 $ch = curl_init(); 
		 curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret); 
		 //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		 //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		 //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		 //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		 //Init_Weixin::curl_redir_exec($ch);
		 //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		 //curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
		// curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		 //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		 
		 //curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$tmpInfo = curl_exec($ch); 
		 if (curl_errno($ch)) {  
			echo 'Errno'.curl_error($ch);
		 }
		 curl_close($ch); 
		 $arr= json_decode($tmpInfo,true);
		 //weileshouyevar_dump($arr);
		 return $arr;
    }
	
	function curl_menu($ACCESS_TOKEN,$data)
    {
		$ch = curl_init(); 
		 curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$ACCESS_TOKEN); 
		 //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		 curl_setopt($ch, CURLOPT_POST, 1);
		 curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
		 curl_setopt($ch, CURLOPT_TIMEOUT, 1);		
		 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		 //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		 //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
		 curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		 $tmpInfo = curl_exec($ch); 
		 if (curl_errno($ch)) {  
		 
			echo 'Errno'.curl_error($ch);
		 }
		 
		 curl_close($ch); 
		 $arr= json_decode($tmpInfo,true);
		 //weileshouyevar_dump($data);
		 echo '</br>-------';
		 //weileshouyevar_dump($arr);
		return $arr;
    }
}
?>
