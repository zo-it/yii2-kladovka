<?php

namespace yii\kladovka\helpers;

use yii\helpers\BaseHtml;


class Html extends BaseHtml
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

// Yii::$classMap['yii\helpers\Html'] = '@yii/kladovka/helpers/Html.php';
if (!class_exists('yii\helpers\Html', false)) {
    class_alias('yii\kladovka\helpers\Html', 'yii\helpers\Html', false);
}
