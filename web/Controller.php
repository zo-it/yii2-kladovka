<?php

namespace yii\kladovka\web;

use yii\web\Controller as YiiController,
    yii\helpers\VarDumper,
    Yii;


class Controller extends YiiController
{

    public function logError($message, $category = 'application')
    {
        if (!is_scalar($message)) {
            $message = VarDumper::dumpAsString($message);
        }
        Yii::error($message, $category);
    }
}
