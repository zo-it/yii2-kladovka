<?php

namespace yii\kladovka\helpers;

use Yii;


class Text
{

    public static function date($format, $timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = time();
        } elseif ($timestamp && is_string($timestamp)) {
            if (($timestamp == '0000-00-00 00:00:00') || ($timestamp == '0000-00-00') || ($timestamp == '00:00:00')) {
                $timestamp = 0;
            } elseif (preg_match('~^\-?\d{9,10}$~', $timestamp)) {
                $timestamp = (int)$timestamp;
            } elseif (preg_match('~^(\d{2})\D(\d{2})\D(\d{4})$~', $timestamp, $match)) {
                if (checkdate($match[2], $match[1], $match[3])) { // d/m/Y
                    $timestamp = mktime(0, 0, 0, $match[2], $match[1], $match[3]);
                } elseif (checkdate($match[1], $match[2], $match[3])) { // m/d/Y
                    $timestamp = mktime(0, 0, 0, $match[1], $match[2], $match[3]);
                } else {
                    $timestamp = strtotime($timestamp);
                }
            } else {
                $timestamp = strtotime($timestamp);
            }
        }
        if (array_key_exists('kladovka', Yii::$app->getI18n()->translations)) {
            return preg_replace_callback('~\w{3,}~', function ($match) {
                return Yii::t('kladovka', $match[0]);
            }, date($format, $timestamp));
        }
        return date($format, $timestamp);
    }

    public static function date2($format, $timestamp = null)
    {
        return static::date(str_replace('F', 'F_', $format), $timestamp);
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
