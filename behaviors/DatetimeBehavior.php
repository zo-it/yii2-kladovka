<?php

namespace yii\kladovka\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord;


class DatetimeBehavior extends Behavior
{

    public $attributes = [];

    public $dateTimeFormat = 'Y-m-d H:i:s';

    public $dateFormat = 'Y-m-d';

    public $timeFormat = 'H:i:s';

    private $_preparedAttributes = null;

    protected function prepareAttributes()
    {
        if (!is_array($this->_preparedAttributes)) {
            $attributes = [];
            $owner = $this->owner;
            if ($owner instanceof ActiveRecord) {
                foreach ($this->attributes as $key => $value) {
                    $attribute = null;
                    $options = [
                        'dateTimeFormat' => $this->dateTimeFormat,
                        'dateFormat' => $this->dateFormat,
                        'timeFormat' => $this->timeFormat
                    ];
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
            ActiveRecord::EVENT_AFTER_INSERT => 'decodeData',
            ActiveRecord::EVENT_AFTER_UPDATE => 'decodeData',
            ActiveRecord::EVENT_AFTER_FIND => 'decodeData'
        ];
    }

    public function encodeData($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $tableSchema = $owner->getTableSchema();
            foreach ($this->prepareAttributes() as $attribute => $options) {
                switch ($tableSchema->getColumn($attribute)->dbType) {
                    case 'datetime': $format = $options['dateTimeFormat']; break;
                    case 'date': $format = $options['dateFormat']; break;
                    case 'time': $format = $options['timeFormat']; break;
                    default: $format = 'U'; break;
                }
                if ($owner->{$attribute}) {
                    if (is_int($owner->{$attribute})) {
                        $owner->{$attribute} = date($format, $owner->{$attribute});
                    } elseif (is_string($owner->{$attribute})) {
                        if (($owner->{$attribute} == '0000-00-00 00:00:00') || ($owner->{$attribute} == '0000-00-00') || ($owner->{$attribute} == '00:00:00')) {
                            $owner->{$attribute} = date($format, 0);
                        } elseif (preg_match('~^\d{9,10}$~', $owner->{$attribute})) {
                            $owner->{$attribute} = date($format, (int)$owner->{$attribute});
                        } elseif (preg_match('~^(\d{2})\D(\d{2})\D(\d{4})$~', $owner->{$attribute}, $match)) {
                            if (checkdate($match[2], $match[1], $match[3])) { // d/m/Y
                                $owner->{$attribute} = date($format, mktime(0, 0, 0, $match[2], $match[1], $match[3]));
                            } elseif (checkdate($match[1], $match[2], $match[3])) { // m/d/Y
                                $owner->{$attribute} = date($format, mktime(0, 0, 0, $match[1], $match[2], $match[3]));
                            }
                        } else {
                            $owner->{$attribute} = date($format, strtotime($owner->{$attribute}));
                        }
                    }
                } elseif (!is_null($owner->{$attribute})) {
                    $owner->{$attribute} = date($format, 0);
                }
            }
        }
    }

    public function decodeData($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            foreach ($this->prepareAttributes() as $attribute => $options) {
                if ($owner->{$attribute}) {
                    if (is_string($owner->{$attribute})) {
                        if (($owner->{$attribute} == '0000-00-00 00:00:00') || ($owner->{$attribute} == '0000-00-00') || ($owner->{$attribute} == '00:00:00')) {
                            $owner->{$attribute} = 0;
                        } elseif (preg_match('~^\d{9,10}$~', $owner->{$attribute})) {
                            $owner->{$attribute} = (int)$owner->{$attribute};
                        } else {
                            $owner->{$attribute} = strtotime($owner->{$attribute});
                        }
                    }
                } elseif (!is_null($owner->{$attribute})) {
                    $owner->{$attribute} = 0;
                }
            }
        }
    }
}
