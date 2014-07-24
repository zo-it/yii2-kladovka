<?php

namespace yii\kladovka\db\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord;


class Arrayable extends Behavior
{

    private $_attributeNames = [];

    public function setAttributeNames(array $attributeNames)
    {
        $this->_attributeNames = $attributeNames;
    }

    public function getAttributeNames()
    {
        return $this->_attributeNames;
    }

    private $_separator = ',';

    public function setSeparator($separator)
    {
        $this->_separator = $separator;
    }

    public function getSeparator()
    {
        return $this->_separator;
    }

    private $_emptyValue = null;

    public function setEmptyValue($emptyValue)
    {
        $this->_emptyValue = $emptyValue;
    }

    public function getEmptyValue()
    {
        return $this->_emptyValue;
    }

    private $_attributes = [];

    public function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    protected function getAttributeDefaultConfig()
    {
        return [
            'separator' => $this->getSeparator(),
            'emptyValue' => $this->getEmptyValue()
        ];
    }

    protected function buildAttributes()
    {
        $attributes = [];
        $owner = $this->owner;
        //if ($owner instanceof ActiveRecord) {
            $attributeDefaultConfig = $this->getAttributeDefaultConfig();
            foreach ($this->getAttributeNames() as $attributeName) {
                if ($attributeName && is_string($attributeName)) {
                    if ($owner->hasAttribute($attributeName)) {
                        $attributes[$attributeName] = $attributeDefaultConfig;
                    }
                }
            }
            foreach ($this->getAttributes() as $attributeName => $attributeConfig) {
                if ($attributeName && is_string($attributeName) && $attributeConfig/* && (is_string($attributeConfig) || is_array($attributeConfig))*/) {
                    if ($owner->hasAttribute($attributeName)) {
                        if (is_string($attributeConfig)) {
                            $attributeConfig = [
                                'separator' => $attributeConfig
                            ];
                        }
                        if (is_array($attributeConfig)) {
                            $attributes[$attributeName] = array_merge($attributeDefaultConfig, array_intersect_key($attributeConfig, $attributeDefaultConfig));
                        }
                    }
                }
            }
        //}
        return $attributes;
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeSave',
            /*ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'afterFind',*/
            ActiveRecord::EVENT_AFTER_INSERT => 'afterFind',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterFind',
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind'
        ];
    }

    public function beforeSave($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            foreach ($this->buildAttributes() as $attributeName => $attributeConfig) {
                if (is_array($owner->{$attributeName})) {
                    if ($owner->{$attributeName}) {
                        $owner->{$attributeName} = implode($attributeConfig['separator'], $owner->{$attributeName});
                    } else {
                        $owner->{$attributeName} = $attributeConfig['emptyValue'];
                    }
                }
            }
        }
    }

    public function afterFind($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            foreach ($this->buildAttributes() as $attributeName => $attributeConfig) {
                if (!is_array($owner->{$attributeName})) {
                    if ($owner->{$attributeName}) {
                        $owner->{$attributeName} = explode($attributeConfig['separator'], $owner->{$attributeName});
                    } else {
                        $owner->{$attributeName} = [];
                    }
                }
            }
        }
    }
}
