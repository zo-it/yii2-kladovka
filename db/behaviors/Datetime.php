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

    protected function buildAttributes()
    {
        $defaultConfig = [
            'dateFormat' => $this->_dateFormat,
            'dateTimeFormat' => $this->_dateTimeFormat
        ];
        $attributes = [];
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            foreach ($this->_attributeNames as $attributeName) {
                if ($attributeName && is_string($attributeName)) {
                    if ($owner->hasAttribute($attributeName)) {
                        $attributes[$attributeName] = $defaultConfig;
                    }
                }
            }
            foreach ($this->_attributes as $attributeName => $config) {
                if ($attributeName && is_string($attributeName)) {
                    if ($config && is_string($config)) {
                        $config = [
                            'dateFormat' => $config,
                            'dateTimeFormat' => $config
                        ];
                    }
                    if (is_array($config)) {
                        if ($owner->hasAttribute($attributeName)) {
                            $attributes[$attributeName] = array_merge($defaultConfig, array_intersect_key($config, $defaultConfig));
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
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'normalizeBeforeSave',
            /*ActiveRecord::EVENT_BEFORE_INSERT => 'normalizeBeforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'normalizeBeforeSave',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'normalizeAfterFind',*/
            ActiveRecord::EVENT_AFTER_INSERT => 'normalizeAfterFind',
            ActiveRecord::EVENT_AFTER_UPDATE => 'normalizeAfterFind',
            ActiveRecord::EVENT_AFTER_FIND => 'normalizeAfterFind'
        ];
    }

    public function normalizeBeforeSave($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $tableSchema = $owner->getTableSchema();
            foreach ($this->buildAttributes() as $attributeName => $config) {
                //if ($attributeName && is_string($attributeName)) {
                    //if ($owner->hasAttribute($attributeName)) {
                        $format = ($tableSchema->getColumn($attributeName)->dbType == 'date') ? $config['dateFormat'] : $config['dateTimeFormat'];
                        if ($owner->{$attributeName}) {
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
                    //}
                //}
            }
        }
    }

    public function normalizeAfterFind($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            foreach ($this->buildAttributes() as $attributeName => $config) {
                //if ($attributeName && is_string($attributeName)) {
                    //if ($owner->hasAttribute($attributeName)) {
                        if ($owner->{$attributeName} && is_string($owner->{$attributeName})) {
                            if (($owner->{$attributeName} == '0000-00-00') || ($owner->{$attributeName} == '0000-00-00 00:00:00')) {
                                $owner->{$attributeName} = 0;
                            } else {
                                $owner->{$attributeName} = strtotime($owner->{$attributeName});
                            }
                        }
                    //}
                //}
            }
        }
    }
}
