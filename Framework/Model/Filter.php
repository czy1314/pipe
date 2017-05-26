<?php
namespace Framework\Model;
use \Framework\Base\Object;
class  Filter extends Object{
     function _valid($_autov,$data)
    {
        if (empty($_autov) || empty($data) || !is_array($data))
        {
            return $data;
        }
        $max = $filter = $reg = $default = $valid = '';
        reset($data);
        $is_multi = (key($data) === 0 && is_array($data[0]));
        if (!$is_multi)
        {
            $data = array($data);
        }
        foreach ($_autov as $_k => $_v)
        {
            if (is_array($_v))
            {
                $required = (isset($_v['required']) && $_v['required']) ? true : false;
                $type  = isset($_autov[$_k]['type']) ? $_autov[$_k]['type'] : 'string';
                $min  = isset($_autov[$_k]['min']) ? $_autov[$_k]['min'] : 0;
                $max  = isset($_autov[$_k]['max']) ? $_autov[$_k]['max'] : 0;
                $filter = isset($_autov[$_k]['filter']) ? $_autov[$_k]['filter'] : '';
                $valid= isset($_autov[$_k]['valid']) ? $_autov[$_k]['valid'] : '';
                $reg  = isset($_autov[$_k]['reg']) ? $_autov[$_k]['reg'] : '';
                $default = isset($_autov[$_k]['default']) ? $_autov[$_k]['default'] : '';
            }
            else
            {
                preg_match_all('/([a-z]+)(\((\d+),(\d+)\))?/', $_v, $result);
                $type = $result[1];
                $min  = $result[3];
                $max  = $result[4];
            }
            $field_name = $this->match_field($_k);
            foreach ($data as $_sk => $_sd)
            {
                $has_set = isset($data[$_sk][$_k]);

                if (!$has_set)
                {
                    // continue;
                }


                if ($required && $data[$_sk][$_k] == '')
                {

                    $this->_error(Lang::get("required_field"), $field_name);

                    return false;
                }

                /* 运行到此，说明该字段不是必填项可以为空 */

                $value = $data[$_sk][$_k];

                /* 默认值 */
                if (!$value && $default)
                {
                    $data[$_sk][$_k] = function_exists($default) ? $default() : $default;
                    continue;
                }

                /* 若还是空值，则没必要往下验证长度，正则，自定义和过滤，因为其已经是一个空值了 */
                if (!$value)
                {
                    continue;
                }

                /* 大小|长度限制 */
                if ($type == 'string')
                {
                    $strlen = strlen($value);
                    if ($min != 0 && $strlen < $min)
                    {
                        $this->_error(Lang::get('autov_length_lt_min'), $field_name);

                        return false;
                    }
                    if ($max != 0 && $strlen > $max)
                    {
                        $this->_error(Lang::get('autov_length_gt_max'), $field_name);

                        return false;
                    }
                }
                else
                {
                    if ($min != 0 && $value < $min)
                    {
                        $this->_error(Lang::get('autov_value_lt_min'), $field_name);

                        return false;
                    }
                    if ($max != 0 && $value > $max)
                    {
                        $this->_error(Lang::get('autov_value_gt_max'), $field_name);

                        return false;
                    }
                }

                /* 正则 */
                if ($reg)
                {
                    if (!preg_match($reg, $value))
                    {
                        $this->_error(Lang::get('check_match_error'), $field_name);
                        return false;
                    }
                }

                /* 自定义验证 */
                if ($valid && function_exists($valid))
                {
                    $result = $valid($value);
                    if ($result !== true)
                    {
                        $this->_error($result);

                        return false;
                    }
                }

                /* 过滤 */
                if ($filter)
                {
                    $funs    = explode(',', $filter);
                    foreach ($funs as $fun)
                    {
                        function_exists($fun) && $value = $fun($value);
                    }
                    $data[$_sk][$_k] = $value;
                }
            }
        }
        if (!$is_multi)
        {
            $data = $data[0];
        }

        return $data;
    }

}