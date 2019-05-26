<?php

namespace App\Utility;

class StringUtilities
{
    public static function slug(string $str)
    {
        $str = strtolower($str);
        $str = preg_replace('/\W/', '-', $str);
        $str = preg_replace('/--+/', '-', $str);

        return $str;
    }
}
