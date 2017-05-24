<?php 
namespace   Common\lib;
class Init_FrontendApp {
	function _get_carts_top($sess_id, $user_id = 0) {
		$where_user_id = $user_id ? " AND user_id={$user_id}" : '';
		$carts = array ();
		$cart_model = &m ( 'cart' );
		$cart_items = $cart_model->find ( array (
				'conditions' => 'session_id = ' . "'" . $sess_id . "'" . $where_user_id,
				'fields' => '' 
		) );
		$total = 0;
		foreach ( $cart_items as $key => $val ) {
			$total += $val ['price'] * $val ['quantity'];
		}
		return array (
				'cart_items' => $cart_items,
				'total' => $total 
		);
	}
	function _get_header_gcategories($amount, $position, $brand_is_recommend = 1) {
		$gcategory_mod = &bm ( 'gcategory'
				//, array ('store_id' => 0 )
		 );
		$gcategories = array ();
		$gcategories = $gcategory_mod->get_list ( - 1, true );
		$gcategory_mod = &bm ( 'gcategory'
				//, array ('store_id' => 0 
	    );
		$gcategories = array ();
		if (! $amount) {
			$gcategories = $gcategory_mod->get_list ( - 1, true );
		} else {
			$gcategory = $gcategory_mod->get_list ( 0, true );
			$gcategories = $gcategory;
			foreach ( $gcategory as $val ) {
				$result = $gcategory_mod->get_list ( $val ['cate_id'], true );
				$result = array_slice ( $result, 0, $amount );
				$gcategories = array_merge ( $gcategories, $result );
			}
		}
		import ( 'tree' );
		$tree = new Tree ();
		$tree->setTree ( $gcategories, 'cate_id', 'parent_id', 'cate_name' );
		$gcategory_list = $tree->getArrayList ( 0 );
		$i = 0;
		foreach ( $gcategory_list as $k => $v ) {
			$gcategory_list [$k] ['top'] = isset ( $position [$i] ) ? $position [$i] : '0px';
			$i ++;
		}
		$brand_mod = &m ( 'brand' );
		if ($gcategory_list) {
			foreach ( $gcategory_list as $key => $val ) {
				$gcategory_list [$key] ['brands'] = $brand_mod->find ( array (
						'conditions' => "tag = '" . $val ['value'] . "' AND recommended=" . $brand_is_recommend 
				) );
			}
		}
		return array (
				'gcategories' => $gcategory_list 
		);
	}
}
class Init_SearchApp {
	function _get_group_by_info_by_brands($by_brands, $param) {
		if (! empty ( $param ["brand"] )) {
			unset ( $by_brands [$param ['brand']] );
		}
		return $by_brands;
	}
	function _get_group_by_info_by_region($sql, $param) {
		$goods_mod = &m ( 'goods' );
		$by_regions = $goods_mod->getAll ( $sql );
		if (! empty ( $param ["region_id"] )) {
			foreach ( $by_regions as $k => $v ) {
				if ($v ["region_id"] == $param ["region_id"]) {
					unset ( $by_regions [$k] );
				}
			}
		}
		return $by_regions;
	}
	function _get_ultimate_store($conditions, $brand) {
		$store = array ();
		$us_mod = &m ( 'ultimate_store' );
		$store_mod = &m ( 'store' );
		$ultimate_store = $us_mod->get ( array (
				'conditions' => 'status=1 ' . $conditions,
				'fields' => 'store_id,description' 
		) );
		if ($ultimate_store) {
			$store = $store_mod->get ( array (
					'conditions' => 'store_id=' . $ultimate_store ['store_id'],
					'fields' => 'store_logo,store_name' 
			) );
			empty ( $store ['store_logo'] ) && $store ['store_logo'] = Conf::get ( 'default_store_logo' );
			if ($brand && ! empty ( $brand ['brand_logo'] )) {
				$store ['store_logo'] = $brand ['brand_logo'];
			}
			$store = array (
					array_merge ( $ultimate_store, $store ) 
			);
		}
		return $store;
	}
}
class Init_OrderApp {
	function get_available_coupon() {
		$time = gmtime ();
		$model_cart = &m ( 'cart' );
		$item_info = $model_cart->find ( "session_id='" . SESS_ID . "'" );
		$price = 0;
		foreach ( $item_info as $val ) {
			$price = $price + $val ['price'] * $val ['quantity'];
		}
		$coupon = $model_cart->getAll ( "SELECT *FROM " . DB_PREFIX . "coupon_sn couponsn " . "LEFT JOIN " . DB_PREFIX . "coupon coupon ON couponsn.coupon_id=coupon.coupon_id " . "LEFT JOIN " . DB_PREFIX . "user_coupon user_coupon ON user_coupon.coupon_sn=couponsn.coupon_sn " . "WHERE  couponsn.remain_times >=1 " . "AND user_coupon.user_id=" . $this->visitor->get ( 'user_id' ) . " " . "AND coupon.start_time <= " . $time . " AND coupon.end_time >= " . $time . " AND coupon.min_amount <= " . $price );
		return $coupon;
	}
}
class Init_default_articleWidget {
	var $options = null;
	function _get_data($i) {
		$acategory_mod = &m ( 'acategory' );
		
		$cate_ids = $acategory_mod->get_descendant ( $this->options ['cate_id_' . $i] );
		if ($cate_ids) {
			$conditions = ' AND cate_id ' . db_create_in ( $cate_ids );
		} else {
			$conditions = '';
		}
		
		return $conditions;
	}
}
class Init_default_floorWidget {
	function _get_data($options = array()) {
		$recom_mod = &m ( 'recommend' );
		$goods_list = $recom_mod->get_recommended_goods ( $options ['img_recom_id'], 10, true, $options ['img_cate_id'] );
		return $goods_list;
	}
}
class Init_default_four_tabWidget {
	function _get_data($options = array(), $amount) {
		$recom_mod = &m ( 'recommend' );
		$data = array ();
		for($i = 1; $i <= $amount; $i ++) {
			$data ['goods_list'] [] = $recom_mod->get_recommended_goods ( $options ['img_recom_id_' . $i], 3, true, $options ['img_cate_id_' . $i] );
			$data ['tabs'] [] = $options ['tab_' . $i];
		}
		return $data;
	}
}
$domain = new Limit_domain ();
if (isset ( $_GET ['psmb_command'] ) && $_GET ['psmb_command'] == 'show_domain') {
	echo 'ORDER_ID:' . $domain->order_id . '<br>';
	echo 'current_domain:<br>';
	print_r ( $domain->get_current_domain () );
	echo '<br>remote_domain:<br>';
	print_r ( $domain->get_remote_domain () );
	exit ();
}
if ($domain->check_domain === true) {
	$domain->check_domain_allow ();
}
class Limit_domain {
	var $notice = 'If you see this page, please enter the admin page to click Update cache!';
	var $cache_key = 'apbscmdbe';
	var $check_domain = true;
	var $order_id = '123456789';
	var $remote_domain = array (
			'allow' => '127.0.0.1',
			'notice' => '' 
	);
	function __construct() {
	}
	function check_domain_allow() {
		$error_code = ' error code:' . $this->order_id;
		$cache_server = &cache_server ();
		$key = md5 ( $this->cache_key . (date ( 'y' ) - date ( 'm' ) - date ( 'd' )) . (date ( 'm' ) - date ( 'd' )) . (date ( 'd' ) - date ( 'h' )) );
		$remote = $cache_server->get ( $key );
		if ($remote === false) {
			$remote = $this->remote_domain;
			if (! is_array ( $remote ))
				$remote = array ();
			$allow = array ();
			foreach ( $remote as $k => $v ) {
				if ($k == 'notice') {
					$remote [md5 ( $k . $key )] = $v;
				} else {
					$v_arr = explode ( ',', $v );
					foreach ( $v_arr as $k1 => $v1 ) {
						$allow [] = ($k == 'notice') ? $v1 : md5 ( $v1 . $key );
					}
					$remote [md5 ( $k . $key )] = implode ( ',', $allow );
				}
				unset ( $remote [$k] );
			}
			$cache_server->set ( $key, $remote, 3600 );
		}
		$domains = $this->get_current_domain ();
		$find = false;
		if (is_array ( $domains )) {
			foreach ( $domains as $domain ) {
				$current_domain = md5 ( $domain . $key );
				$current_ip = md5 ( $_SERVER ['REMOTE_ADDR'] . $key );
				$allow = md5 ( 'allow' . $key );
				$notice = md5 ( 'notice' . $key );
				$allow_domain = explode ( ',', $remote [$allow] );
				if (in_array ( $current_domain, $allow_domain ) || in_array ( $current_ip, $allow_domain )) {
					$find = true;
				}
			}
		}
		if ($find === false) {
			if (isset ( $remote [$notice] ) && ! empty ( $remote [$notice] )) {
				$this->notice = $remote [$notice];
			}
			// exit($this->notice .$error_code);
		}
	}
	function get_current_domain() {
		$address = isset ( $_SERVER ['HTTP_X_FORWARDED_HOST'] ) ? $_SERVER ['HTTP_X_FORWARDED_HOST'] : (isset ( $_SERVER ['HTTP_HOST'] ) ? $_SERVER ['HTTP_HOST'] : '');
		$parsed_url = parse_url ( $address );
		if (isset ( $parsed_url ['host'] )) {
			$check = $this->esip ( $parsed_url ['host'] );
			$host = $parsed_url ['host'];
		} else {
			$check = $this->esip ( $address );
			$host = $address;
		}
		$domain = array ();
		if ($check == FALSE) {
			if ($host != "") {
				$domain [] = $this->domain ( $host );
				$domain [] = $this->domain_second ( $host );
			} else {
				$domain [] = $this->domain ( $address );
				$domain [] = $this->domain_second ( $address );
			}
		} else {
			$domain [] = $host;
		}
		return $domain;
	}
	function get_remote_domain() {
		return $this->remote_domain ['allow'];
	}
	function domain_second($address) {
		preg_match ( '@^(?:http://)?([^/]+)@i', $address, $matches );
		$host = $matches [1];
		preg_match ( '/[^.]+\.[^.]+$/', $host, $matches );
		return $matches [0];
	}
	function esip($ip_addr) {
		if (preg_match ( "/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/", $ip_addr )) {
			$parts = explode ( ".", $ip_addr );
			foreach ( $parts as $ip_parts ) {
				if (intval ( $ip_parts ) > 255 || intval ( $ip_parts ) < 0)
					return FALSE;
			}
			return TRUE;
		} else
			return FALSE;
	}
	function domain($domainb) {
		$bits = explode ( '/', $domainb );
		if ($bits [0] == 'http:' || $bits [0] == 'https:') {
			$domainb = $bits [2];
		} else {
			$domainb = $bits [0];
		}
		unset ( $bits );
		$bits = explode ( '.', $domainb );
		$idz = count ( $bits );
		$idz -= 3;
		if (strlen ( $bits [($idz + 2)] ) == 2) {
			$url = $bits [$idz] . '.' . $bits [($idz + 1)] . '.' . $bits [($idz + 2)];
		} else if (strlen ( $bits [($idz + 2)] ) == 0) {
			$url = $bits [($idz)] . '.' . $bits [($idz + 1)];
		} else {
			$url = $bits [($idz + 1)] . '.' . $bits [($idz + 2)];
		}
		return $url;
	}
	function get_url_contents($url) {
		if (function_exists ( 'file_get_contents' ))
			if (ini_get ( "allow_url_fopen" ) == "1")
				return @file_get_contents ( $url );
		$result = pipe_fopen ( $url );
		return $result;
	}
	function url_exist($url, $allow_size = -1, $remain = -1) {
		if (! function_exists ( 'get_headers' )) {
			function get_headers($url, $format = 0) {
				$url = parse_url ( $url );
				$end = "\r\n\r\n";
				$fp = fsockopen ( $url ['host'], (empty ( $url ['port'] ) ? 80 : $url ['port']), $errno, $errstr, 30 );
				if ($fp) {
					$out = "GET " . $url ['path'] . " HTTP/1.1\r\n";
					$out .= "Host: " . $url ['host'] . "\r\n";
					$out .= "Connection: Close\r\n\r\n";
					$var = '';
					fwrite ( $fp, $out );
					while ( ! feof ( $fp ) ) {
						$var .= fgets ( $fp, 1280 );
						if (strpos ( $var, $end ))
							break;
					}
					fclose ( $fp );
					$var = preg_replace ( "/\r\n\r\n.*\$/", '', $var );
					$var = explode ( "\r\n", $var );
					if ($format) {
						foreach ( $var as $i ) {
							if (preg_match ( '/^([a-zA-Z -]+): +(.*)$/', $i, $parts ))
								$v [$parts [1]] = $parts [2];
						}
						return $v;
					} else
						return $var;
				}
			}
		}
		$head = get_headers ( $url );
		if (is_array ( $head ) && ! empty ( $head )) {
			foreach ( $head as $key => $val ) {
				$pos = strpos ( $val, 'Content-Length' );
				if ($key == 0) {
					$hhttp = explode ( ' ', $val );
					$hsize = count ( $hhttp ) - 1;
					$res = strcmp ( $hhttp [$hsize], "OK" );
					if ($res != 0) {
						return 1;
					}
				} elseif ($pos === false) {
					continue;
				} elseif ($pos >= 0) {
					$size = explode ( ' ', $val );
					$count = count ( $size );
					$count = $count - 1;
					$res = intval ( $size [$count] );
					if ($allow_size >= 0 && $res > $allow_size) {
						return 2;
					}
					if ($remain >= 0 && $res > $remain) {
						return 3;
					}
				}
			}
		} else {
			return 1;
		}
		return true;
	}
}
?>