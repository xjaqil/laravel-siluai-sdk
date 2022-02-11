<?php


namespace Aqil\SiluAi\Kernel\Support;


/**
 * @param $map
 * @param $salt
 * @return string
 */
function generate_sign($map, $salt): string
{
    $rList = array();
    foreach ($map as $k => $v) {
        if ($k == "other_settle_params" || $k == "app_id" || $k == "sign" || $k == "thirdparty_id")
            continue;
        $value = trim(strval($v));
        $len = strlen($value);
        if ($len > 1 && substr($value, 0, 1) == "\"" && substr($value, $len, $len - 1) == "\"")
            $value = substr($value, 1, $len - 1);
        $value = trim($value);
        if ($value == "" || $value == "null")
            continue;
        array_push($rList, $value);
    }
    array_push($rList, $salt);
    sort($rList, 2);
    return md5(implode('&', $rList));
}
