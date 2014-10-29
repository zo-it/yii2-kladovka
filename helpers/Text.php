<?php

namespace yii\mozayka\helpers;

use Yii;


class Text
{

    public static function date($format, $timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = time();
        }
        return preg_replace_callback('~\w{3,}~', function ($m) { return Yii::t('mozayka', $m[0]); }, date($format, $timestamp));
    }
}
