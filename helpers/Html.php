<?php

namespace yii\kladovka\helpers;

use yii\helpers\BaseHtml;


class Html extends BaseHtml
{

    public static function label($content, $for = null, $options = [])
    {
        $labelTag = 'label';
        if (array_key_exists('tag', $options)) {
            $labelTag = $options['tag'];
            unset($options['tag']);
        }
        if ($labelTag == 'label') {
            $options['for'] = $for;
        }
        return static::tag($labelTag, $content, $options);
    }
}

class_alias('yii\kladovka\helpers\Html', 'yii\helpers\Html', false);
