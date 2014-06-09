<?php

namespace ivanchkv\kladovka\db\behaviors;

use yii\db\ActiveRecord;


class Timestamp extends \yii\base\Behavior
{

    private $_timestampAttribute = 'timestamp';

    public function setTimestampAttribute($timestampAttribute)
    {
        $this->_timestampAttribute = $timestampAttribute;
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

    private $_timeFormat = 'H:i:s';

    public function setTimeFormat($timeFormat)
    {
        $this->_timeFormat = $timeFormat;
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
            if ($this->_timestampAttribute && is_string($this->_timestampAttribute)) {
                if ($owner->hasAttribute($this->_timestampAttribute)) {
                    $dbType = $owner->getTableSchema()->getColumn($this->_timestampAttribute)->dbType;
                    $format = ($dbType == 'date') ? $this->_dateFormat : (($dbType == 'time') ? $this->_timeFormat : $this->_dateTimeFormat);
                    $owner->{$this->_timestampAttribute} = date($format);
                }
            }
        }
    }

    public function afterFind($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            if ($this->_timestampAttribute && is_string($this->_timestampAttribute)) {
                if ($owner->hasAttribute($this->_timestampAttribute)) {
                    if ($owner->{$this->_timestampAttribute} && is_string($owner->{$this->_timestampAttribute})) {
                        if (($owner->{$this->_timestampAttribute} == '0000-00-00') || ($owner->{$this->_timestampAttribute} == '0000-00-00 00:00:00') || ($owner->{$this->_timestampAttribute} == '00:00:00')) {
                            $owner->{$this->_timestampAttribute} = 0;
                        } else {
                            $owner->{$this->_timestampAttribute} = strtotime($owner->{$this->_timestampAttribute});
                        }
                    }
                }
            }
        }
    }
}
