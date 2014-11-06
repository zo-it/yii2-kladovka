<?php

namespace yii\kladovka\behaviors;

use yii\db\ActiveRecord;


class NullableBehavior extends AttributesBehavior
{

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'encodeData',
            ActiveRecord::EVENT_BEFORE_INSERT => 'encodeData',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'encodeData'
        ];
    }

    public function encodeData($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $tableSchema = $owner->getTableSchema();
            foreach ($this->prepareAttributes() as $attribute => $options) {
                if (($owner->{$attribute} === '') && $tableSchema->getColumn($attribute)->allowNull) {
                    $owner->{$attribute} = null;
                }
            }
        }
    }
}
