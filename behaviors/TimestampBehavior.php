<?php

namespace yii\kladovka\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord;


class TimestampBehavior extends Behavior
{

    public $createdAtAttribute = 'created_at';

    public $updatedAtAttribute = 'updated_at';

    public $timestampAttribute = 'timestamp';

    public $dateTimeFormat = 'Y-m-d H:i:s';

    public $dateFormat = 'Y-m-d';

    public $timeFormat = 'H:i:s';

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
            $createdAtAttribute = $this->createdAtAttribute;
            if ($createdAtAttribute && is_string($createdAtAttribute) && $owner->hasAttribute($createdAtAttribute)) {
                switch ($tableSchema->getColumn($createdAtAttribute)->dbType) {
                    case 'datetime': $format = $this->dateTimeFormat; break;
                    case 'date': $format = $this->dateFormat; break;
                    case 'time': $format = $this->timeFormat; break;
                    default: $format = 'U'; break;
                }
                if ($owner->{$createdAtAttribute}) {
                    if (is_int($owner->{$createdAtAttribute})) {
                        $owner->{$createdAtAttribute} = date($format, $owner->{$createdAtAttribute});
                    } elseif (is_string($owner->{$createdAtAttribute})) {
                        if (($owner->{$createdAtAttribute} == '0000-00-00 00:00:00') || ($owner->{$createdAtAttribute} == '0000-00-00') || ($owner->{$createdAtAttribute} == '00:00:00')) {
                            $owner->{$createdAtAttribute} = date($format, 0);
                        } elseif (preg_match('~^\d{9,10}$~', $owner->{$createdAtAttribute})) {
                            $owner->{$createdAtAttribute} = date($format, (int)$owner->{$createdAtAttribute});
                        } elseif (preg_match('~^(\d{2})\D(\d{2})\D(\d{4})$~', $owner->{$createdAtAttribute}, $match)) {
                            if (checkdate($match[2], $match[1], $match[3])) { // d/m/Y
                                $owner->{$createdAtAttribute} = date($format, mktime(0, 0, 0, $match[2], $match[1], $match[3]));
                            } elseif (checkdate($match[1], $match[2], $match[3])) { // m/d/Y
                                $owner->{$createdAtAttribute} = date($format, mktime(0, 0, 0, $match[1], $match[2], $match[3]));
                            }
                        } else {
                            $owner->{$createdAtAttribute} = date($format, strtotime($owner->{$createdAtAttribute}));
                        }
                    }
                } elseif ($owner->getIsNewRecord()) {
                    $owner->{$createdAtAttribute} = date($format);
                } elseif (!is_null($owner->{$createdAtAttribute})) {
                    $owner->{$createdAtAttribute} = date($format, 0);
                }
            }
            // updated
            $updatedAtAttribute = $this->updatedAtAttribute;
            if ($updatedAtAttribute && is_string($updatedAtAttribute) && $owner->hasAttribute($updatedAtAttribute)) {
                switch ($tableSchema->getColumn($updatedAtAttribute)->dbType) {
                    case 'datetime': $format = $this->dateTimeFormat; break;
                    case 'date': $format = $this->dateFormat; break;
                    case 'time': $format = $this->timeFormat; break;
                    default: $format = 'U'; break;
                }
                $owner->{$updatedAtAttribute} = date($format);
            }
            // timestamp
            $timestampAttribute = $this->timestampAttribute;
            if ($timestampAttribute && is_string($timestampAttribute) && $owner->hasAttribute($timestampAttribute)) {
                switch ($tableSchema->getColumn($timestampAttribute)->dbType) {
                    case 'datetime': $format = $this->dateTimeFormat; break;
                    case 'date': $format = $this->dateFormat; break;
                    case 'time': $format = $this->timeFormat; break;
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
            $createdAtAttribute = $this->createdAtAttribute;
            if ($createdAtAttribute && is_string($createdAtAttribute) && $owner->hasAttribute($createdAtAttribute)) {
                if ($owner->{$createdAtAttribute}) {
                    if (is_string($owner->{$createdAtAttribute})) {
                        if (($owner->{$createdAtAttribute} == '0000-00-00 00:00:00') || ($owner->{$createdAtAttribute} == '0000-00-00') || ($owner->{$createdAtAttribute} == '00:00:00')) {
                            $owner->{$createdAtAttribute} = 0;
                        } elseif (preg_match('~^\d{9,10}$~', $owner->{$createdAtAttribute})) {
                            $owner->{$createdAtAttribute} = (int)$owner->{$createdAtAttribute};
                        } else {
                            $owner->{$createdAtAttribute} = strtotime($owner->{$createdAtAttribute});
                        }
                    }
                } elseif (!is_null($owner->{$createdAtAttribute})) {
                    $owner->{$createdAtAttribute} = 0;
                }
            }
            // updated
            $updatedAtAttribute = $this->updatedAtAttribute;
            if ($updatedAtAttribute && is_string($updatedAtAttribute) && $owner->hasAttribute($updatedAtAttribute)) {
                if ($owner->{$updatedAtAttribute}) {
                    if (is_string($owner->{$updatedAtAttribute})) {
                        if (($owner->{$updatedAtAttribute} == '0000-00-00 00:00:00') || ($owner->{$updatedAtAttribute} == '0000-00-00') || ($owner->{$updatedAtAttribute} == '00:00:00')) {
                            $owner->{$updatedAtAttribute} = 0;
                        } elseif (preg_match('~^\d{9,10}$~', $owner->{$updatedAtAttribute})) {
                            $owner->{$updatedAtAttribute} = (int)$owner->{$updatedAtAttribute};
                        } else {
                            $owner->{$updatedAtAttribute} = strtotime($owner->{$updatedAtAttribute});
                        }
                    }
                } elseif (!is_null($owner->{$updatedAtAttribute})) {
                    $owner->{$updatedAtAttribute} = 0;
                }
            }
            // timestamp
            $timestampAttribute = $this->timestampAttribute;
            if ($timestampAttribute && is_string($timestampAttribute) && $owner->hasAttribute($timestampAttribute)) {
                if ($owner->{$timestampAttribute}) {
                    if (is_string($owner->{$timestampAttribute})) {
                        if (($owner->{$timestampAttribute} == '0000-00-00 00:00:00') || ($owner->{$timestampAttribute} == '0000-00-00') || ($owner->{$timestampAttribute} == '00:00:00')) {
                            $owner->{$timestampAttribute} = 0;
                        } elseif (preg_match('~^\d{9,10}$~', $owner->{$timestampAttribute})) {
                            $owner->{$timestampAttribute} = (int)$owner->{$timestampAttribute};
                        } else {
                            $owner->{$timestampAttribute} = strtotime($owner->{$timestampAttribute});
                        }
                    }
                } elseif (!is_null($owner->{$timestampAttribute})) {
                    $owner->{$timestampAttribute} = 0;
                }
            }
        }
    }
}
