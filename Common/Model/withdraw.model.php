<?php

/* 会员 认证 */
class WithdrawModel extends BaseModel {
	var $table = 'withdraw';
	var $prikey = 'wd_id';
	var $_name = 'withdraw';
	var $_relation = array (
			'belongs_to_user' => array (
					'type' => BELONGS_TO,
					'reverse' => 'has_wds',
					'model' => 'member' 
			),
			'belongs_to_store' => array (
					'type' => BELONGS_TO,
					'reverse' => 'has_wds',
					'model' => 'store' 
			) 
	);
}

?>