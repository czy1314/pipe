<?php

/* 会员 member */
class MemberModel extends BaseModel {
	var $table = 'member';
	var $prikey = 'user_id';
	var $_name = 'member';
	
	/* 与其它模型之间的关系 */
	var $_relation = array (
			
			'has_wxrelation' => array (
					'model' => 'wxrelation', // 模型的名称
					'type' => HAS_MANY, // 关系类型
					'foreign_key' => 'user_id', // 外键名
					'dependent' => true  // 依赖
						),
			'manage_mall' => array (
					'model' => 'userpriv',
					'type' => HAS_ONE,
					'foreign_key' => 'user_id',
					// 'ext_limit' => array('store_id' => 0),
					'dependent' => true 
			),
		
			
			// 一个用户有多条收到的短信
			'has_received_message' => array (
					'model' => 'message',
					'type' => HAS_MANY,
					'foreign_key' => 'to_id',
					'dependent' => true 
			),
			// 一个用户有多条发送出去的短信
			'has_sent_message' => array (
					'model' => 'message',
					'type' => HAS_MANY,
					'foreign_key' => 'from_id',
					'dependent' => true 
			),
			
	)
	;
	
	/*
	 * var $_autov = array( 'user_name' => array( 'required' => true, 'filter' => 'trim', ), 'password' => array( 'required' => true, 'filter' => 'trim', 'min' => 6, ), );
	 */
    /*
     * 判断名称是否唯一
     */
    function unique($user_name, $user_id = 0) {
    	if(WX_WAP){
    		return true;
    	}
		$conditions = "user_name = '" . $user_name . "'";
		$user_id && $conditions .= " AND user_id <> '" . $user_id . "'";
		return count ( $this->find ( array (
				'conditions' => $conditions 
		) ) ) == 0;
	}
	function drop($conditions, $fields = 'portrait') {
		if ($droped_rows = parent::drop ( $conditions, $fields )) {
			restore_error_handler ();
			$droped_data = $this->getDroppedData ();
			foreach ( $droped_data as $row ) {
				$row ['portrait'] && @unlink ( ROOT_PATH . '/' . $row ['portrait'] );
			}
			reset_error_handler ();
		}
		return $droped_rows;
	}
}

?>