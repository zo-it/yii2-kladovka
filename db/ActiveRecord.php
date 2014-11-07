<?php

namespace yii\kladovka\db;

use yii\db\ActiveRecord as YiiActiveRecord,
    Yii;


class ActiveRecord extends YiiActiveRecord
{

    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    public function implodePrimaryKey($glue = ',')
    {
        return implode($glue, array_values($this->getPrimaryKey(true)));
    }
}
