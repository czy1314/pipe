<?php

/**
 * 验证输入的邮件地址是否合法
 * @param string $email
 * 需要验证的邮件地址
 * @return bool
 */
function is_email($user_email)
{
    $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,5}\$/i";
    if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false) {
        if (preg_match($chars, $user_email)) {
            return true;
        } else {
            return '邮箱地址不合法';
        }
    } else {
        return '邮箱地址不合法';
    }
}

/**
 * 验证手机号码
 */
function is_tel($tel)
{
    if (preg_match("/1[3458]{1}\d{1}-?\d{8}$/", trim($tel))) {
        return true;
    } else {
        return false;
    }
}

/**
 * 检查是否为一个合法的时间格式
 *
 * @param string $time
 * @return void
 */
function is_time($time)
{
    $pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';

    return preg_match($pattern, $time);
}

/**
 * 验证身份证号码
 * @param $id_card
 * @return bool
 *
 */
function is_idcard($id_card)
{
    if (strlen($id_card) == 18) {
        return $this->idcard_checksum18($id_card);
    } elseif ((strlen($id_card) == 15)) {
        $id_card = $this->idcard_15to18($id_card);
        return $this->idcard_checksum18($id_card);
    } else {
        return false;
    }
}

// 计算身份证校验码，根据国家标准GB 11643-1999
function idcard_verify_number($idcard_base)
{
    if (strlen($idcard_base) != 17) {
        return false;
    }
    // 加权因子
    $factor = array(
        7,
        9,
        10,
        5,
        8,
        4,
        2,
        1,
        6,
        3,
        7,
        9,
        10,
        5,
        8,
        4,
        2
    );
    // 校验码对应值
    $verify_number_list = array(
        '1',
        '0',
        'X',
        '9',
        '8',
        '7',
        '6',
        '5',
        '4',
        '3',
        '2'
    );
    $checksum = 0;
    for ($i = 0; $i < strlen($idcard_base); $i++) {
        $checksum += substr($idcard_base, $i, 1) * $factor [$i];
    }
    $mod = $checksum % 11;
    $verify_number = $verify_number_list [$mod];
    return $verify_number;
}

// 将15位身份证升级到18位
function idcard_15to18($idcard)
{
    if (strlen($idcard) != 15) {
        return false;
    } else {
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (array_search(substr($idcard, 12, 3), array(
                '996',
                '997',
                '998',
                '999'
            )) !== false
        ) {
            $idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 9);
        } else {
            $idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 9);
        }
    }
    $idcard = $idcard . $this->idcard_verify_number($idcard);
    return $idcard;
}

// 18位身份证校验码有效性检查
function idcard_checksum18($idcard)
{
    if (strlen($idcard) != 18) {
        return false;
    }
    $idcard_base = substr($idcard, 0, 17);
    if ($this->idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))) {
        return false;
    } else {
        return true;
    }
}