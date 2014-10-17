<?php

namespace yii\kladovka\db;

use yii\db\ActiveRecord as YiiActiveRecord,
    yii\helpers\VarDumper,
    Yii;


class ActiveRecord extends YiiActiveRecord
{

    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    public function logError($message, $category = 'application')
    {
        if (!is_scalar($message)) {
            $message = VarDumper::dumpAsString($message);
        }
        Yii::error($message, $category);
        if ($this->hasErrors()) {
            Yii::error(VarDumper::dumpAsString([
                'class' => get_class($this),
                'attributes' => $this->getAttributes(),
                'errors' => $this->getErrors()
            ]));
        }
    }
}
