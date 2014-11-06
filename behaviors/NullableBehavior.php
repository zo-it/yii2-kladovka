<?php

namespace yii\kladovka\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord;


class NullableBehavior extends Behavior
{

    public $attributes = [];

    private $_preparedAttributes = null;

    protected function prepareAttributes()
    {
        if (!is_array($this->_preparedAttributes)) {
            $attributes = [];
            $owner = $this->owner;
            if ($owner instanceof ActiveRecord) {
                foreach ($this->attributes as $key => $value) {
                    $attribute = null;
                    $options = [];
                    if (is_int($key) && $value && is_string($value) && $owner->hasAttribute($value)) {
                        $attribute = $value;
                    } elseif ($key && is_string($key) && $owner->hasAttribute($key) && $value && is_array($value)) {
                        $attribute = $key;
                        $options = array_merge($options, array_intersect_key($value, $options));
                    }
                    if ($attribute) {
                        $attributes[$attribute] = $options;
                    }
                }
            }
            $this->_preparedAttributes = $attributes;
        }
        return $this->_preparedAttributes;
    }

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
