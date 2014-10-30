<?php

namespace yii\kladovka\helpers;

use Yii;


class Text
{

    public static function date($format, $timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = time();
        }
        if (array_key_exists('kladovka', Yii::$app->getI18n()->translations)) {
            return preg_replace_callback('~\w{3,}~', function ($m) { return Yii::t('kladovka', $m[0]); }, date($format, $timestamp));
        }
        return date($format, $timestamp);
    }
}
