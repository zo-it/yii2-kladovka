<?php

namespace yii\kladovka\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord;


class TimestampBehavior extends Behavior
{

    public $createdAttribute = 'created_at';

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
            $tableSchema = $owner->getTableSchema();
            // created
            $createdAttribute = $this->createdAttribute;
            if ($createdAttribute && is_string($createdAttribute) && $owner->hasAttribute($createdAttribute)) {
                switch ($tableSchema->getColumn($createdAttribute)->dbType) {
                    case 'date': $format = $this->dateFormat; break;
                    case 'time': $format = $this->timeFormat; break;
                    case 'datetime': $format = $this->dateTimeFormat; break;
                    default: $format = 'U'; break;
                }
                if ($owner->{$createdAttribute}) {
                    if (is_int($owner->{$createdAttribute})) {
                        $owner->{$createdAttribute} = date($format, $owner->{$createdAttribute});
                    } elseif (is_string($owner->{$createdAttribute})) {
                        if (preg_match('~^\d+$~', $owner->{$createdAttribute})) {
                            $owner->{$createdAttribute} = date($format, (int)$owner->{$createdAttribute});
                        } elseif (preg_match('~^(\d{2})\D(\d{2})\D(\d{4})$~', $owner->{$createdAttribute}, $match)) {
                            if (checkdate($match[2], $match[1], $match[3])) { // d/m/Y
                                $owner->{$createdAttribute} = date($format, mktime(0, 0, 0, $match[2], $match[1], $match[3]));
                            } elseif (checkdate($match[1], $match[2], $match[3])) { // m/d/Y
                                $owner->{$createdAttribute} = date($format, mktime(0, 0, 0, $match[1], $match[2], $match[3]));
                            }
                        }
                    }
                } elseif ($owner->getIsNewRecord()) {
                    $owner->{$createdAttribute} = date($format);
                } else {
                    $owner->{$createdAttribute} = date($format, 0);
                }
            }
            // updated
            $updatedAttribute = $this->updatedAttribute;
            if ($updatedAttribute && is_string($updatedAttribute) && $owner->hasAttribute($updatedAttribute)) {
                switch ($tableSchema->getColumn($updatedAttribute)->dbType) {
                    case 'date': $format = $this->dateFormat; break;
                    case 'time': $format = $this->timeFormat; break;
                    case 'datetime': $format = $this->dateTimeFormat; break;
                    default: $format = 'U'; break;
                }
                $owner->{$updatedAttribute} = date($format);
            }
            // timestamp
            $timestampAttribute = $this->timestampAttribute;
            if ($timestampAttribute && is_string($timestampAttribute) && $owner->hasAttribute($timestampAttribute)) {
                switch ($tableSchema->getColumn($timestampAttribute)->dbType) {
                    case 'date': $format = $this->dateFormat; break;
                    case 'time': $format = $this->timeFormat; break;
                    case 'datetime': $format = $this->dateTimeFormat; break;
                    default: $format = 'U'; break;
                }
                $owner->{$timestampAttribute} = date($format);
            }
        }
    }

    public function decodeData($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            // created
            $createdAttribute = $this->createdAttribute;
            if ($createdAttribute && is_string($createdAttribute) && $owner->hasAttribute($createdAttribute)) {
                if ($owner->{$createdAttribute} && is_string($owner->{$createdAttribute})) {
                    if (($owner->{$createdAttribute} == '0000-00-00') || ($owner->{$createdAttribute} == '0000-00-00 00:00:00')) {
                        $owner->{$createdAttribute} = 0;
                    } elseif (preg_match('~^\d+$~', $owner->{$createdAttribute})) {
                        $owner->{$createdAttribute} = (int)$owner->{$createdAttribute};
                    } else {
                        $owner->{$createdAttribute} = strtotime($owner->{$createdAttribute});
                    }
                }
            }
            // updated
            $updatedAttribute = $this->updatedAttribute;
            if ($updatedAttribute && is_string($updatedAttribute) && $owner->hasAttribute($updatedAttribute)) {
                if ($owner->{$updatedAttribute} && is_string($owner->{$updatedAttribute})) {
                    if (($owner->{$updatedAttribute} == '0000-00-00') || ($owner->{$updatedAttribute} == '0000-00-00 00:00:00')) {
                        $owner->{$updatedAttribute} = 0;
                    } elseif (preg_match('~^\d+$~', $owner->{$updatedAttribute})) {
                        $owner->{$updatedAttribute} = (int)$owner->{$updatedAttribute};
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
                    } elseif (preg_match('~^\d+$~', $owner->{$timestampAttribute})) {
                        $owner->{$timestampAttribute} = (int)$owner->{$timestampAttribute};
                    } else {
                        $owner->{$timestampAttribute} = strtotime($owner->{$timestampAttribute});
                    }
                }
            }
        }
    }
}
