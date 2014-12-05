<?php

namespace yii\kladovka\helpers;

use Yii;


class BaseText
{

    public static function strtotime($time, $now = null)
    {
        if (is_int($time)) {
            return $time;
        }
        if (is_null($now)) {
            $now = time();
        } elseif ($now && is_string($now)) {
            $now = static::strtotime($now);
        }
        if ($time && is_string($time)) {
            if (($time == '0000-00-00 00:00:00') || ($time == '0000-00-00') || ($time == '00:00:00')) {
                return 0;
            } elseif (preg_match('~^\-?\d{9,10}$~', $time)) {
                return (int)$time;
            } elseif (preg_match('~^(\d{1,2})\D(\d{1,2})\D(\d{4})( \d{2}\:\d{2}(?:\:\d{2})?)?$~', $time, $match)) {
                if (checkdate($match[2], $match[1], $match[3])) { // d/m/Y
                    $time = $match[3] . '-' . $match[2] . '-' . $match[1] . (array_key_exists(4, $match) ? $match[4] : '');
                } elseif (checkdate($match[1], $match[2], $match[3])) { // m/d/Y
                    $time = $match[3] . '-' . $match[1] . '-' . $match[2] . (array_key_exists(4, $match) ? $match[4] : '');
                }
            }
        }
        return strtotime($time, $now);
    }

    public static function date($format, $timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = time();
        } elseif ($timestamp && is_string($timestamp)) {
            $timestamp = static::strtotime($timestamp);
        }
        $date = date($format, $timestamp);
        if (array_key_exists('kladovka', Yii::$app->getI18n()->translations)) {
            $date = preg_replace_callback('~\w{3,}~', function ($match) {
                return Yii::t('kladovka', $match[0]);
            }, $date);
        }
        return $date;
    }

    public static function date2($format, $timestamp = null)
    {
        if (strncmp(Yii::$app->language, 'ru', 2) == 0) {
            $format = str_replace('F', 'F_', $format);
        }
        return static::date($format, $timestamp);
    }

    public static function slug($title)
    {
        $fix = [
            '~[\s_]+~' => '_',
            '~[^a-zа-яё_\-]~iu' => ''
        ];
        return preg_replace(array_keys($fix), array_values($fix), $title);
    }

    public static function juiDateFormat($format)
    {
        return strtr($format, [
            'Y' => 'yy',
            //'y' => 'y',
            'F' => 'MM',
            //'M' => 'M',
            'm' => 'mm',
            'n' => 'm',
            'l' => 'DD',
            'D' => 'D',
            'd' => 'dd',
            'j' => 'd'
        ]);
    }

    public static function juiTimeFormat($format)
    {
        return strtr($format, [
            'A' => 'TT',
            'a' => 'tt',
            'H' => 'HH',
            'G' => 'H',
            'h' => 'hh',
            'g' => 'h',
            'i' => 'mm',
            's' => 'ss'
        ]);
    }
}
