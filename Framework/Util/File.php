<?php
class File extends \Framework\Base\Object{
    /**
     * fopen封装函数
     *
     * 
     * @param string $url
     * @param int $limit
     * @param string $post
     * @param string $cookie
     * @param boolen $bysocket
     * @param string $ip
     * @param int $timeout
     * @param boolen $block
     * @return responseText
     */
    function pipe_fopen($url, $limit = 500000, $post = '', $cookie = '', $bysocket = false, $ip = '', $timeout = 15, $block = true) {
        $return = '';
        $matches = parse_url ( $url );
        $host = $matches ['host'];
        $path = $matches ['path'] ? $matches ['path'] . ($matches ['query'] ? '?' . $matches ['query'] : '') : '/';
        $port = ! empty ( $matches ['port'] ) ? $matches ['port'] : 80;

        if ($post) {
            $out = "POST $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            // $out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= 'Content-Length: ' . strlen ( $post ) . "\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cache-Control: no-cache\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
            $out .= $post;
        } else {
            $out = "GET $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            // $out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
        }
        $fp = @fsockopen ( ($ip ? $ip : $host), $port, $errno, $errstr, $timeout );
        if (! $fp) {
            return '';
        } else {
            stream_set_blocking ( $fp, $block );
            stream_set_timeout ( $fp, $timeout );
            @fwrite ( $fp, $out );
            $status = stream_get_meta_data ( $fp );
            if (! $status ['timed_out']) {
                while ( ! feof ( $fp ) ) {
                    if (($header = @fgets ( $fp )) && ($header == "\r\n" || $header == "\n")) {
                        break;
                    }
                }

                $stop = false;
                while ( ! feof ( $fp ) && ! $stop ) {
                    $data = fread ( $fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit) );
                    $return .= $data;
                    if ($limit) {
                        $limit -= strlen ( $data );
                        $stop = $limit <= 0;
                    }
                }
            }
            @fclose ( $fp );
            return $return;
        }
    }
    /**
     * 创建目录（如果该目录的上级目录不存在，会先创建上级目录）
     * 依赖于 ROOT_PATH 常量，且只能创建 ROOT_PATH 目录下的目录
     * 目录分隔符必须是 / 不能是 \
     *
     * @param string $absolute_path
     *        	绝对路径
     * @param int $mode
     *        	目录权限
     * @return bool
     */
    function pipe_mkdir($absolute_path, $mode = 0777) {
        if (is_dir ( $absolute_path )) {
            return true;
        }

        $root_path = ROOT_PATH;
        $relative_path = str_replace ( $root_path, '', $absolute_path );
        $each_path = explode ( '/', $relative_path );
        $cur_path = $root_path; // 当前循环处理的路径
        foreach ( $each_path as $path ) {
            if ($path) {
                $cur_path = $cur_path . '/' . $path;
                if (! is_dir ( $cur_path )) {
                    if (@mkdir ( $cur_path, $mode )) {
                        fclose ( fopen ( $cur_path . '/index.htm', 'w' ) );
                    } else {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * 删除目录,不支持目录中带 ..
     *
     * @param string $dir
     *
     * @return boolen
     */
    function pipe_rmdir($dir) {
        $dir = str_replace ( array (
            '..',
            "\n",
            "\r"
        ), array (
            '',
            '',
            ''
        ), $dir );
        $ret_val = false;
        if (is_dir ( $dir )) {
            $d = @dir ( $dir );
            if ($d) {
                while ( false !== ($entry = $d->read ()) ) {
                    if ($entry != '.' && $entry != '..') {
                        $entry = $dir . '/' . $entry;
                        if (is_dir ( $entry )) {
                            pipe_rmdir ( $entry );
                        } else {
                            @unlink ( $entry );
                        }
                    }
                }
                $d->close ();
                $ret_val = rmdir ( $dir );
            }
        } else {
            $ret_val = unlink ( $dir );
        }

        return $ret_val;
    }


    function rm_file($dir) {
        if (is_dir ( $dir )) {
            $res = opendir ( $dir );
            // 列出 images 目录中的文件
            while ( ($file = readdir ( $res )) !== false ) {
                @unlink ( $dir . '/' . $file );
            }
            closedir ( $res );
        }
    }
    function file_ext($filename) {
        return trim ( substr ( strrchr ( $filename, '.' ), 1, 10 ) );
    }

}