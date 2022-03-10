<?php

function hide_email($str): string
{
    $arr = explode('@', $str);
    $rest = substr($arr[0], 0, 4);
    $len = strlen($arr[0]) - 4;
    $str = $rest . str_repeat('*', $len) . "@" . $arr[1];
    unset($arr);
    return $str;
}
