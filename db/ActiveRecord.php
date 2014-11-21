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

    public function getAttributeLabel($attribute)
    {
        return Yii::t('kladovka', parent::getAttributeLabel($attribute));
    }
}
