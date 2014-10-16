<?php

namespace yii\kladovka\helpers;


class Ru
{

    private static $_F = [
        'Января', 'Февраля', 'Марта',
        'Апреля', 'Мая', 'Июня',
        'Июля', 'Августа', 'Сентября',
        'Октября', 'Ноября', 'Декабря'
    ];

    private static $_M = [
        'Янв', 'Фев', 'Мрт', 'Апр', 'Май', 'Июн',
        'Июл', 'Авг', 'Сен', 'Окт', 'Нбр', 'Дек'
    ];

    public static function date($format, $timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = time();
        }
        $i = date('n', $timestamp) - 1;
        $map = [
            'F' => static::$_F[$i],
            'M' => static::$_M[$i]
        ];
        return date(str_replace(array_keys($map), array_values($map), $format), $timestamp);
    }

    public static function slug($title)
    {
        $map = [
            '~[\s_]+~' => '_',
            '~[^a-zа-яё_\-]~iu' => ''
        ];
        return preg_replace(array_keys($map), array_values($map), $title);
    }
}
