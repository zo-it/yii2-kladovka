<?php

namespace yii\kladovka\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord;


class TimestampBehavior extends Behavior
{

    //public $createdAttribute = 'created_at';

    public $updatedAttribute = 'updated_at';

    public $timestampAttribute = 'timestamp';

    public $dateFormat = 'Y-m-d';

    public $timeFormat = 'H:i:s';

    public $dateTimeFormat = 'Y-m-d H:i:s';

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
            // updated
            $updatedAttribute = $this->updatedAttribute;
            if ($updatedAttribute && is_string($updatedAttribute) && $owner->hasAttribute($updatedAttribute)) {
                switch ($tableSchema->getColumn($updatedAttribute)->dbType) {
                    case 'date': $format = $this->dateFormat; break;
                    case 'time': $format = $this->timeFormat; break;
                    default: $format = $this->dateTimeFormat; break;
                }
                $owner->{$updatedAttribute} = date($format);
            }
            // timestamp
            $timestampAttribute = $this->timestampAttribute;
            if ($timestampAttribute && is_string($timestampAttribute) && $owner->hasAttribute($timestampAttribute)) {
                switch ($owner->getTableSchema()->getColumn($timestampAttribute)->dbType) {
                    case 'date': $format = $this->dateFormat; break;
                    case 'time': $format = $this->timeFormat; break;
                    default: $format = $this->dateTimeFormat; break;
                }
                $owner->{$timestampAttribute} = date($format);
            }
        }
    }

    public function decodeData($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            // updated
            $updatedAttribute = $this->updatedAttribute;
            if ($updatedAttribute && is_string($updatedAttribute) && $owner->hasAttribute($updatedAttribute)) {
                if ($owner->{$updatedAttribute} && is_string($owner->{$updatedAttribute})) {
                    if (($owner->{$updatedAttribute} == '0000-00-00') || ($owner->{$updatedAttribute} == '0000-00-00 00:00:00')) {
                        $owner->{$updatedAttribute} = 0;
                    } else {
                        $owner->{$updatedAttribute} = strtotime($owner->{$updatedAttribute});
                    }
                }
            }
            // timestamp
            $timestampAttribute = $this->timestampAttribute;
            if ($timestampAttribute && is_string($timestampAttribute) && $owner->hasAttribute($timestampAttribute)) {
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
