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

    public static function hasRealPrimaryKey()
    {
        return (bool)static::getTableSchema()->primaryKey;
    }

    public static function hasPrimaryKey()
    {
        return (bool)static::primaryKey();
    }

    public function implodePrimaryKey($glue = ',')
    {
        return implode($glue, array_values($this->getPrimaryKey(true)));
    }

    public function generateAttributeLabel($name)
    {
        return Yii::t('kladovka', parent::generateAttributeLabel($name));
    }
}
