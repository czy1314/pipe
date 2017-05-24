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
        #TODO
    }
    /**
     *    触发错误
     *
     *    @author    LorenLei
     *    @param     string $errmsg
     *    @return    void
     */
    function _error($msg, $obj = '')
    {
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