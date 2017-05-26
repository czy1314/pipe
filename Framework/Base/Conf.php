<?
namespace  Framework\Base;
/**
 *    配置管理器
 *
 *    @author    Garbin
 *    @usage    none
 */
class Conf
{
    /**
     *    加载配置项
     *
     *    @author    Garbin
     *    @param     mixed $conf
     *    @return    bool
     */
    static function load($conf)
    {
    	
        $old_conf = isset($GLOBALS['Wyfq_CONFIG']) ? $GLOBALS['Wyfq_CONFIG'] : array();
        if (is_string($conf))
        {
            $conf = include($conf);
        }
        if (is_array($old_conf))
        {
            $GLOBALS['Wyfq_CONFIG'] = array_merge($old_conf, $conf);
        }
        else
        {
            $GLOBALS['Wyfq_CONFIG'] = $conf;
        }
    }
    /**
     *    获取配置项
     *
     *    @author    Garbin
     *    @param     string $k
     *    @return    mixed
     */
   static function get($key = '')
   {
        $vkey = $key ? strtokey("{$key}", '$GLOBALS[\'Wyfq_CONFIG\']') : '$GLOBALS[\'Wyfq_CONFIG\']';

        return eval('if(isset(' . $vkey . '))return ' . $vkey . ';else{ return null; }');
    }
}
?>