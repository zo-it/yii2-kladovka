<?php

namespace yii\kladovka\helpers;

use yii\helpers\BaseHtml as YiiBaseHtml;


class BaseHtml extends YiiBaseHtml
{

    public static function label($content, $for = null, $options = [])
    {
        $tag = 'label';
        if (array_key_exists('tag', $options)) {
            $tag = $options['tag'];
            unset($options['tag']);
        }
        if ($tag == 'label') {
            $options['for'] = $for;
        }
        return static::tag($tag, $content, $options);
    }
}
