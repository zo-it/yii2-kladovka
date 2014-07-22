<?php

namespace yii\kladovka\db\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord;


class Datetime extends Behavior
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

    private $_dateFormat = 'Y-m-d';

    public function setDateFormat($dateFormat)
    {
        $this->_dateFormat = $dateFormat;
    }

    public function getDateFormat()
    {
        return $this->_dateFormat;
    }

    private $_dateTimeFormat = 'Y-m-d H:i:s';

    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->_dateTimeFormat = $dateTimeFormat;
    }

    public function getDateTimeFormat()
    {
        return $this->_dateTimeFormat;
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
            'dateFormat' => $this->getDateFormat(),
            'dateTimeFormat' => $this->getDateTimeFormat()
        ];
    }

    protected function buildAttributes()
    {
        $attributes = [];
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
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
                                'dateFormat' => $attributeConfig,
                                'dateTimeFormat' => $attributeConfig
                            ];
                        }
                        if (is_array($attributeConfig)) {
                            $attributes[$attributeName] = array_merge($attributeDefaultConfig, array_intersect_key($attributeConfig, $attributeDefaultConfig));
                        }
                    }
                }
            }
        }
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
                if ($owner->{$attributeName}) {
                    switch ($owner->getTableSchema()->getColumn($attributeName)->dbType) {
                        case 'date': $format = $attributeConfig['dateFormat']; break;
                        default: $format = $attributeConfig['dateTimeFormat'];
                    }
                    if (is_int($owner->{$attributeName})) {
                        $owner->{$attributeName} = date($format, $owner->{$attributeName});
                    } elseif (is_string($owner->{$attributeName}) && preg_match('~^(\d{2})\D(\d{2})\D(\d{4})$~', $owner->{$attributeName}, $match)) {
                        if (checkdate($match[2], $match[1], $match[3])) { // d/m/Y
                            $owner->{$attributeName} = date($format, mktime(0, 0, 0, $match[2], $match[1], $match[3]));
                        } elseif (checkdate($match[1], $match[2], $match[3])) { // m/d/Y
                            $owner->{$attributeName} = date($format, mktime(0, 0, 0, $match[1], $match[2], $match[3]));
                        }
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
