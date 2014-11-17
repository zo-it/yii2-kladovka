<?php

namespace yii\kladovka\behaviors;

use yii\db\ActiveRecord;


class DatetimeBehavior extends AttributesBehavior
{

    public $dateTimeFormat = 'Y-m-d H:i:s';

    public $dateFormat = 'Y-m-d';

    public $timeFormat = 'H:i:s';

    public $autoDecoding = false;

    protected function defaultOptions()
    {
        return [
            'dateTimeFormat' => $this->dateTimeFormat,
            'dateFormat' => $this->dateFormat,
            'timeFormat' => $this->timeFormat,
            'autoDecoding' => $this->autoDecoding
        ];
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'encodeData',
            //ActiveRecord::EVENT_AFTER_VALIDATE => 'decodeData',
            //ActiveRecord::EVENT_BEFORE_INSERT => 'encodeData',
            ActiveRecord::EVENT_AFTER_INSERT => 'decodeData',
            //ActiveRecord::EVENT_BEFORE_UPDATE => 'encodeData',
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
                $columnSchema = $tableSchema->getColumn($attribute);
                switch ($columnSchema->dbType) {
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
                        } elseif (preg_match('~^\-?\d{9,10}$~', $owner->{$attribute})) {
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
                } elseif ($columnSchema->allowNull) {
                    $owner->{$attribute} = null;
                /*} else {
                    $owner->{$attribute} = date($format, 0);*/
                }
            }
        }
    }

    public function decodeData($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $tableSchema = $owner->getTableSchema();
            foreach ($this->prepareAttributes() as $attribute => $options) {
if (!$options['autoDecoding']) {
continue;
}
                $columnSchema = $tableSchema->getColumn($attribute);
                if ($owner->{$attribute}) {
                    if (is_string($owner->{$attribute})) {
                        if (($owner->{$attribute} == '0000-00-00 00:00:00') || ($owner->{$attribute} == '0000-00-00') || ($owner->{$attribute} == '00:00:00')) {
                            $owner->{$attribute} = 0;
                        } elseif (preg_match('~^\-?\d{9,10}$~', $owner->{$attribute})) {
                            $owner->{$attribute} = (int)$owner->{$attribute};
                        } else {
                            $owner->{$attribute} = strtotime($owner->{$attribute});
                        }
                    }
                } elseif ($columnSchema->allowNull) {
                    $owner->{$attribute} = null;
                /*} else {
                    $owner->{$attribute} = 0;*/
                }
            }
        }
    }
}
