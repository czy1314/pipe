<?php
namespace  Application\User\Model;
use \Framework\Model\BaseModel;
class MemberModel extends BaseModel
{
    var $table  = 'tbuser';
    var $prikey = 'iUserId';
    var $_name  = 'user';

    /* 与其它模型之间的关系 */
    var $_relation = array(

         // 一个用户有多条收到的短信
        'has_received_message' => array(
            'model'         => 'message',
            'type'          => HAS_MANY,
            'foreign_key'   => 'to_id',
            'dependent' => true
        ),
        // 一个用户有多条发送出去的短信
        'has_sent_message' => array(
            'model'         => 'message',
            'type'          => HAS_MANY,
            'foreign_key'   => 'from_id',
            'dependent' => true
        ),

        // 会员和好友是多对多的关系（会员拥有多个好友）
        'has_friend' => array(
            'model'        => 'member',
            'type'         => MANY_TO_MANY,
            'middle_table' => 'friend',
            'foreign_key'  => 'owner_id',
            'reverse'      => 'be_friend',
        ),
        // 好友是多对多的关系（会员拥有多个好友）
        'be_friend' => array(
            'model'        => 'member',
            'type'         => MANY_TO_MANY,
            'middle_table' => 'friend',
            'foreign_key'  => 'friend_id',
            'reverse'      => 'has_friend',
        )

    );

    var $_autov = array(
        'sEmail' => array(
            'required'  => true,
            'filter'    => 'trim',
            'valid' => 'is_email'
        ),
        'sPassword' => array(
            'required' => true,
            'filter'   => 'trim',
            'min'      => 6,
            'max'=>20
        ),
        'sNickName' => array(
            'required' => true,
            'filter'   => 'trim',
            'min'      => 1,
            'max'      => 20,
        ),
    );

    /*
     * 判断名称是否唯一
     */
    function test()
    {

        return count($this->find(array('conditions' => '1=1'))) == 0;
    }

    function drop($conditions, $fields = 'portrait')
    {
        if ($droped_rows = parent::drop($conditions, $fields))
        {
            restore_error_handler();
            $droped_data = $this->getDroppedData();
            foreach ($droped_data as $row)
            {
                $row['portrait'] && @unlink(ROOT_PATH . '/' . $row['portrait']);
            }
            reset_error_handler();
        }
        return $droped_rows;
    }
}

?>