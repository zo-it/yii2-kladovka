<?php

namespace yii\kladovka\helpers;


class Html extends BaseHtml
{
}

if (!class_exists('yii\helpers\Html', false)) {
    class_alias('yii\kladovka\helpers\Html', 'yii\helpers\Html', false);
}
