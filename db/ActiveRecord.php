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

    public function logErrors()
    {
        if ($this->hasErrors()) {
            Yii::error(VarDumper::dumpAsString([
                'class' => get_class($this),
                'attributes' => $this->getAttributes(),
                'errors' => $this->getErrors()
            ]));
        }
    }
}
