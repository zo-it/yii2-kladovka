<?php

namespace yii\kladovka\db\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord;


class Timestamp extends Behavior
{

    private $_timestampAttribute = 'timestamp';

    public function setTimestampAttribute($timestampAttribute)
    {
        $this->_timestampAttribute = $timestampAttribute;
    }

    public function getTimestampAttribute()
    {
        return $this->_timestampAttribute;
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

    private $_timeFormat = 'H:i:s';

    public function setTimeFormat($timeFormat)
    {
        $this->_timeFormat = $timeFormat;
    }

    public function getTimeFormat()
    {
        return $this->_timeFormat;
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
            $timestampAttribute = $this->getTimestampAttribute();
            if ($timestampAttribute && is_string($timestampAttribute)) {
                if ($owner->hasAttribute($timestampAttribute)) {
                    switch ($owner->getTableSchema()->getColumn($timestampAttribute)->dbType) {
                        case 'date': $format = $this->getDateFormat(); break;
                        case 'time': $format = $this->getTimeFormat(); break;
                        default: $format = $this->getDateTimeFormat();
                    }
                    $owner->{$timestampAttribute} = date($format);
                }
            }
        }
    }

    public function afterFind($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $timestampAttribute = $this->getTimestampAttribute();
            if ($timestampAttribute && is_string($timestampAttribute)) {
                if ($owner->hasAttribute($timestampAttribute)) {
                    if ($owner->{$timestampAttribute} && is_string($owner->{$timestampAttribute})) {
                        if (($owner->{$timestampAttribute} == '0000-00-00') || ($owner->{$timestampAttribute} == '00:00:00') || ($owner->{$timestampAttribute} == '0000-00-00 00:00:00')) {
                            $owner->{$timestampAttribute} = 0;
                        } else {
                            $owner->{$timestampAttribute} = strtotime($owner->{$timestampAttribute});
                        }
                    }
                }
            }
        }
    }
}
