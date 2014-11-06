<?php

namespace yii\kladovka\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord,
    yii\base\ModelEvent;


class TimeDeleteBehavior extends Behavior
{

    public $deletedAtAttribute = 'deleted_at';

    public $dateTimeFormat = 'Y-m-d H:i:s';

    public $dateFormat = 'Y-m-d';

    public $timeFormat = 'H:i:s';

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'encodeData',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'decodeData',
            ActiveRecord::EVENT_BEFORE_INSERT => 'encodeData',
            ActiveRecord::EVENT_AFTER_INSERT => 'decodeData',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'encodeData',
            ActiveRecord::EVENT_AFTER_UPDATE => 'decodeData',
            ActiveRecord::EVENT_AFTER_FIND => 'decodeData',
            ActiveRecord::EVENT_BEFORE_DELETE => 'invalidEvent'
        ];
    }

    public function invalidEvent($event)
    {
        if ($event->sender !== $this) {
            $event->isValid = false;
        }
    }

    protected function beforeDeleteOwner()
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $event = new ModelEvent;
            $event->sender = $this;
            $owner->trigger(ActiveRecord::EVENT_BEFORE_DELETE, $event);
            return $event->isValid;
        }
        return false;
    }

    protected function afterDeleteOwner()
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $owner->trigger(ActiveRecord::EVENT_AFTER_DELETE);
        }
    }

    public function timeDelete($runValidation = true, $attributeNames = null)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $deletedAtAttribute = $this->deletedAtAttribute;
            if ($deletedAtAttribute && is_string($deletedAtAttribute) && $owner->hasAttribute($deletedAtAttribute)) {
                if ($this->beforeDeleteOwner()) {
                    $owner->{$deletedAtAttribute} = time();
                    $result = $owner->save($runValidation, $attributeNames);
                    $this->afterDeleteOwner();
                    return $result;
                }
            }
        }
        return false;
    }

    public function encodeData($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $tableSchema = $owner->getTableSchema();
            $deletedAtAttribute = $this->deletedAtAttribute;
            if ($deletedAtAttribute && is_string($deletedAtAttribute) && $owner->hasAttribute($deletedAtAttribute)) {
                $columnSchema = $tableSchema->getColumn($deletedAtAttribute);
                switch ($columnSchema->dbType) {
                    case 'datetime': $format = $this->dateTimeFormat; break;
                    case 'date': $format = $this->dateFormat; break;
                    case 'time': $format = $this->timeFormat; break;
                    default: $format = 'U'; break;
                }
                if ($owner->{$deletedAtAttribute}) {
                    if (is_int($owner->{$deletedAtAttribute})) {
                        $owner->{$deletedAtAttribute} = date($format, $owner->{$deletedAtAttribute});
                    } elseif (is_string($owner->{$deletedAtAttribute})) {
                        if (($owner->{$deletedAtAttribute} == '0000-00-00 00:00:00') || ($owner->{$deletedAtAttribute} == '0000-00-00') || ($owner->{$deletedAtAttribute} == '00:00:00')) {
                            $owner->{$deletedAtAttribute} = date($format, 0);
                        } elseif (preg_match('~^\-?\d{9,10}$~', $owner->{$deletedAtAttribute})) {
                            $owner->{$deletedAtAttribute} = date($format, (int)$owner->{$deletedAtAttribute});
                        } elseif (preg_match('~^(\d{2})\D(\d{2})\D(\d{4})$~', $owner->{$deletedAtAttribute}, $match)) {
                            if (checkdate($match[2], $match[1], $match[3])) { // d/m/Y
                                $owner->{$deletedAtAttribute} = date($format, mktime(0, 0, 0, $match[2], $match[1], $match[3]));
                            } elseif (checkdate($match[1], $match[2], $match[3])) { // m/d/Y
                                $owner->{$deletedAtAttribute} = date($format, mktime(0, 0, 0, $match[1], $match[2], $match[3]));
                            }
                        } else {
                            $owner->{$deletedAtAttribute} = date($format, strtotime($owner->{$deletedAtAttribute}));
                        }
                    }
                } elseif ($columnSchema->allowNull) {
                    $owner->{$deletedAtAttribute} = null;
                /*} else {
                    $owner->{$deletedAtAttribute} = date($format, 0);*/
                }
            }
        }
    }

    public function decodeData($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $tableSchema = $owner->getTableSchema();
            $deletedAtAttribute = $this->deletedAtAttribute;
            if ($deletedAtAttribute && is_string($deletedAtAttribute) && $owner->hasAttribute($deletedAtAttribute)) {
                $columnSchema = $tableSchema->getColumn($deletedAtAttribute);
                if ($owner->{$deletedAtAttribute}) {
                    if (is_string($owner->{$deletedAtAttribute})) {
                        if (($owner->{$deletedAtAttribute} == '0000-00-00 00:00:00') || ($owner->{$deletedAtAttribute} == '0000-00-00') || ($owner->{$deletedAtAttribute} == '00:00:00')) {
                            $owner->{$deletedAtAttribute} = 0;
                        } elseif (preg_match('~^\-?\d{9,10}$~', $owner->{$deletedAtAttribute})) {
                            $owner->{$deletedAtAttribute} = (int)$owner->{$deletedAtAttribute};
                        } else {
                            $owner->{$deletedAtAttribute} = strtotime($owner->{$deletedAtAttribute});
                        }
                    }
                } elseif ($columnSchema->allowNull) {
                    $owner->{$deletedAtAttribute} = null;
                /*} else {
                    $owner->{$deletedAtAttribute} = 0;*/
                }
            }
        }
    }
}
