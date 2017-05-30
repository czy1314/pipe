<?
namespace Framework\Base;
/**
 *    所有类的基础类
 *
 *    @author    LorenLei
 *    @usage    none
 */
class Object
{
    var $_errors = array();
    var $_errnum = 0;
    function __construct()
    {
        $this->Object();
    }
    function Object()
    {

    }

    /**
     * 导入并实例化一个类,实例化对象作为
     * @author LorenLei
     * @return boolean|object
     */
    //temp
    public function load($class_name) {
        static $loader = null;
        if (empty ( $class_name )) {
            return false;
        }
        if(!empty($loader[$class_name])){
            if(!isset($this->$class_name)){
                $this->$class_name =  $loader[$class_name];
            }
            return $this->$class_name;
        }
        $path = ROOT_PATH . '/Framework/Util/' .  ucfirst($class_name) . '.php';

        if(file_exists($path)){
            include_once($path);
            $uclass = ucfirst($class_name);
            if(class_exists($uclass = ucfirst($class_name),false)){
                $loader[$class_name] = new $uclass();
                $this->$class_name =  $loader[$class_name];


                return $loader[$class_name];
            }
        }

        return false;

    }

    /**
     *    触发错误
     *
     *    @author    LorenLei
     *    @param     string $errmsg
     *    @return    void
     */
    //temp
    function _error($msg, $obj = '',$data ='',$level = ERROR_USER)
    {
        $this->log_error($msg);
        collect_error($msg);
        if(IS_AJAX){
            $this->jsonResult(-1,$msg,$data);
        }
       /* $data = array('ret'=>-1,'msg'=>$msg,'data'=>$data);
        exit(json_encode($data));
        if(is_array($msg))
        {
            $this->_errors = array_merge($this->_errors, $msg);
            $this->_errnum += count($msg);
        }
        else
        {
        	//compact创建 array('msg'=>$msg,'obj'=>$obj);数组
            $this->_errors[] = compact('msg', 'obj');
            $this->_errnum++;
        }
        switch($level){
            case ERROR_USER:
                $this->jsonResult($msg,$data);
            case ERROR_FAITAL:
                $this->handle_faital($msg);
            default:

        }*/
    }
    //temp
    function error($msg,$data ='',$level = ERROR_USER)
    {
        $this->log_error($msg);
        collect_error($msg);
        switch($level){
            case ERROR_USER:
                $this->jsonResult($msg,$data);
            case ERROR_FAITAL:
                $this->handle_faital($msg);
            default:;
        }
    }

    /**
     * 支持ajax，debug信息查看
     * @param int $ret
     * @param $msg
     * @param string $data
     */
    function jsonResult($ret = 0,$msg,$data=''){
        if(DEBUG_MODE){
            $data = array('ret'=>$ret,'msg'=>$msg,'data'=>$data,'trace_info'=>collect_error());
        }else{
            $data = array('ret'=>$ret,'msg'=>$msg,'data'=>$data);
        }
        exit(json_encode($data));
    }

    function handle_faital($msg){

    }
    function log_error($msg){
        $log_path = ROOT_PATH.'/Common/Log/'.date('Y-m-d').'.txt';
        if(!is_dir(ROOT_PATH.'/Common/Log/')){
            mkdir(ROOT_PATH.'/Common/Log/','0664');
        }
        $msg = date('Y-m-d H:i:s').'-'.real_ip().'-'.$_SERVER['HTTP_USER_AGENT'].'-'.$msg."\r\n";
        $ch = fopen($log_path,'ab');
        if($ch){
            flock($ch,LOCK_EX);
            fwrite($ch,$msg);
            flock($ch,LOCK_UN);
            fclose($ch);
        }
    }
    /**
     *    检查是否存在错误
     *
     *    @author    LorenLei
     *    @return    int
     */
    function has_error()
    {
        return $this->_errnum;
    }

    /**
     *    获取错误列表
     *
     *    @author    LorenLei
     *    @return    array
     */
    function get_error()
    {
        return $this->_errors;
    }


}
?>