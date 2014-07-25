<?php

namespace yii\kladovka\db\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord,
    yii\base\ModelEvent;


class TimeDelete extends Behavior
{

    private $_deleteAttribute = 'deleted';

    public function setDeleteAttribute($deleteAttribute)
    {
        $this->_deleteAttribute = $deleteAttribute;
    }

    public function getDeleteAttribute()
    {
        return $this->_deleteAttribute;
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

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeSave',
            /*ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'afterFind',*/
            ActiveRecord::EVENT_AFTER_INSERT => 'afterFind',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterFind',
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind'
        ];
    }

    public function beforeDelete($event)
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
            $deleteAttribute = $this->getDeleteAttribute();
            if ($deleteAttribute && is_string($deleteAttribute)) {
                if ($owner->hasAttribute($deleteAttribute)) {
                    if ($this->beforeDeleteOwner()) {
                        $owner->{$deleteAttribute} = true;
                        $result = $owner->save($runValidation, $attributeNames);
                        $this->afterDeleteOwner();
                        return $result;
                    }
                }
            }
        }
        return false;
    }

    public function beforeSave($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $deleteAttribute = $this->getDeleteAttribute();
            if ($deleteAttribute && is_string($deleteAttribute)) {
                if ($owner->hasAttribute($deleteAttribute)) {
                    if ($owner->{$deleteAttribute}) {
                        switch ($owner->getTableSchema()->getColumn($deleteAttribute)->dbType) {
                            case 'date': $format = $this->getDateFormat(); break;
                            default: $format = $this->getDateTimeFormat();
                        }
                        if (is_bool($owner->{$deleteAttribute})) {
                            $owner->{$deleteAttribute} = date($format);
                        } elseif (is_int($owner->{$deleteAttribute})) {
                            $owner->{$deleteAttribute} = date($format, $owner->{$deleteAttribute});
                        } elseif (is_string($owner->{$deleteAttribute}) && preg_match('~^(\d{2})\D(\d{2})\D(\d{4})$~', $owner->{$deleteAttribute}, $match)) {
                            if (checkdate($match[2], $match[1], $match[3])) { // d/m/Y
                                $owner->{$deleteAttribute} = date($format, mktime(0, 0, 0, $match[2], $match[1], $match[3]));
                            } elseif (checkdate($match[1], $match[2], $match[3])) { // m/d/Y
                                $owner->{$deleteAttribute} = date($format, mktime(0, 0, 0, $match[1], $match[2], $match[3]));
                            }
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
            $deleteAttribute = $this->getDeleteAttribute();
            if ($deleteAttribute && is_string($deleteAttribute)) {
                if ($owner->hasAttribute($deleteAttribute)) {
                    if ($owner->{$deleteAttribute} && is_string($owner->{$deleteAttribute})) {
                        if (($owner->{$deleteAttribute} == '0000-00-00') || ($owner->{$deleteAttribute} == '0000-00-00 00:00:00')) {
                            $owner->{$deleteAttribute} = 0;
                        } else {
                            $owner->{$deleteAttribute} = strtotime($owner->{$deleteAttribute});
                        }
                    }
                }
            }
        }
    }
}
