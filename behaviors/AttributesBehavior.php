<?php

namespace yii\kladovka\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord;


class AttributesBehavior extends Behavior
{

    public $attributes = [];

    protected function defaultOptions()
    {
        return [];
    }

    private $_preparedAttributes = null;

    protected function prepareAttributes()
    {
        if (!is_array($this->_preparedAttributes)) {
            $attributes = [];
            $owner = $this->owner;
            if ($owner instanceof ActiveRecord) {
                foreach ($this->attributes as $key => $value) {
                    $attribute = null;
                    $options = $this->defaultOptions();
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
}
