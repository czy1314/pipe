<?
namespace  Framework\Base;
/**
 *    语言项管理
 *
 *    @author    LorenLei
 *    @param    none
 *    @return    void
 */
class Lang
{
    /**
     *    获取指定键的语言项
     *
     *    @author    LorenLei
     *    @param     none
     *    @return    mixed
     */
    public  static  function &get($key = '')
    {
        if (Lang::_valid_key($key) == false)
        {
            return $key;
        }
        $vkey = $key ? strtokey("{$key}", '$GLOBALS[\'__ECLANG__\']') : '$GLOBALS[\'__ECLANG__\']';
        $tmp = eval('if(isset(' . $vkey . '))return ' . $vkey . ';else{ return $key; }');

        return $tmp;
    }

    /**
     * 验证key的有效性
     *
     * @author Hyber
     * @param string $key
     * @return bool
     */
    public  static  function _valid_key($key)
    {
    	      
    	    	
        if (strpos($key, ' ') !== false)
        {
            return false;
        }
        #todo 暂时只判断是否含有空格
        return true;
    }

    /**
     *    加载指定的语言项至全局语言数据中
     *
     *    @author    LorenLei
     *    @param    none
     *    @return    void
     */
    //temp
    public  static  function load($lang_file)
    {
        static $loaded = array();
        $old_lang = $new_lang = array();
        $file_md5 = md5($lang_file);
        if (!isset($loaded[$file_md5]))
        {
            $new_lang = Lang::fetch($lang_file);
            $loaded[$file_md5] = $lang_file;
        }
        else
        {
            return;
        }
        $old_lang =& $GLOBALS['__ECLANG__'];
        if (is_array($old_lang))
        {
            $new_lang = array_merge($old_lang, $new_lang);
        }

        $GLOBALS['__ECLANG__'] = $new_lang;
    }
    /**
     *    加载指定的语言项至全局语言数据中
     *
     *    @author    LorenLei
     *    @param    none
     *    @return    void
     */
    public  static  function core_load($lang_file)
    {
        if($lang_file){
            $lang_file = ROOT_PATH."Framework/Lang/{$lang_file}.lang.php";
        }else{
            $lang_file = ROOT_PATH."Framework/Lang/ccommon.lang.php";
        }
        static $loaded = array();
        $old_lang = $new_lang = array();
        $file_md5 = md5($lang_file);
        if (!isset($loaded[$file_md5]))
        {
            $new_lang = Lang::fetch($lang_file);
            $loaded[$file_md5] = $lang_file;
        }
        else
        {
            return;
        }
        $old_lang =& $GLOBALS['__ECLANG__'];
        if (is_array($old_lang))
        {
            $new_lang = array_merge($old_lang, $new_lang);
        }

        $GLOBALS['__ECLANG__'] = $new_lang;
    }

    /**
     *    获取一个语言文件的内容
     *
     *    @author    LorenLei
     *    @param     string $lang_file
     *    @return    array
     */
    public  static  function fetch($lang_file)
    {
        return is_file($lang_file) ? include($lang_file) : array();
    }
}
?>