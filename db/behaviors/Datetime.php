<?php

namespace ivanchkv\kladovka\db\behaviors;

use yii\db\ActiveRecord;


class Datetime extends \yii\base\Behavior
{

    private $_attributeNames = [];

    public function setAttributeNames(array $attributeNames)
    {
        $this->_attributeNames = $attributeNames;
    }

    private $_dateFormat = 'Y-m-d';

    public function setDateFormat($dateFormat)
    {
        $this->_dateFormat = $dateFormat;
    }

    private $_dateTimeFormat = 'Y-m-d H:i:s';

    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->_dateTimeFormat = $dateTimeFormat;
    }

    private $_attributes = [];

    public function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    public function getAttributes()
    {
$owner = $this->owner;
if ($owner instanceof ActiveRecord) {
$attributes = [];
foreach ($this->_attributeNames as $attributeName) {
if (is_string($attributeName)) {
if ($owner->hasAttribute($attributeName)) {
$attributes[$attributeName] = [];
}
}
}
foreach ($this->_attributes as $attributeName => $config) {
if (is_string($attributeName) && is_array($config)) {
if ($owner->hasAttribute($attributeName)) {
$attributes[$attributeName] = [];
}
}
}
}
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'normalizeBeforeSave',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'normalizeAfterFind',
            ActiveRecord::EVENT_BEFORE_INSERT => 'normalizeBeforeSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'normalizeAfterFind',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'normalizeBeforeSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'normalizeAfterFind'
        ];
    }

    public function normalizeBeforeSave($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            foreach ($this->getAttributes() as $attributeName => $config) {
                if ($attributeName && is_string($attributeName)) {
if ($owner->hasAttribute($attributeName)) {
if ($owner->{$attributeName} && is_int($owner->{$attributeName})) {
$owner->{$attributeName} = date($this->_dateTimeFormat, $owner->{$attributeName});
}
}
                }
            }
        }
    }

    public function normalizeAfterFind($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            foreach ($this->getAttributes() as $attributeName => $config) {
                if ($attributeName && is_string($attributeName)) {
                    if ($owner->hasAttribute($attributeName)) {
                        if ($owner->{$attributeName} && is_string($owner->{$attributeName})) {
                            if (($owner->{$attributeName} == '0000-00-00') || ($owner->{$attributeName} == '0000-00-00 00:00:00')) {
                                $owner->{$attributeName} = 0;
                            } else {
                                $owner->{$attributeName} = strtotime($owner->{$attributeName});
                            }
                        }
                    }
                }
            }
        }
    }
}
