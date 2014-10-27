<?php

namespace yii\kladovka\helpers;

use yii\helpers\VarDumper,
    yii\base\Model,
    Yii;


class Log
{

    public static function beginMethod($token, $category = 'application')
    {
        Yii::beginProfile($token, $category);
    }

    public static function endMethod($token, $category = 'application')
    {
        Yii::endProfile($token, $category);
    }

    public static function error($message, $category = 'application')
    {
        if (!is_scalar($message)) {
            $message = VarDumper::dumpAsString($message);
        }
        Yii::error($message, $category);
    }

    public static function modelErrors(Model $model, $message, $category = 'application')
    {
        static::error($message, $category);
        if ($model->hasErrors()) {
            Yii::error(VarDumper::dumpAsString([
                'class' => get_class($model),
                'attributes' => $model->getAttributes(),
                'errors' => $model->getErrors()
            ]), $category);
        }
    }
}
