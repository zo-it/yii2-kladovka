<?php

namespace yii\kladovka\db\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord;


class CreateModify extends Behavior
{

    private $_modifyAttribute = 'modified';

    public function setModifyAttribute($modifyAttribute)
    {
        $this->_modifyAttribute = $modifyAttribute;
    }

    public function getModifyAttribute()
    {
        return $this->_modifyAttribute;
    }

    private $_createAttribute = 'created';

    public function setCreateAttribute($createAttribute)
    {
        $this->_createAttribute = $createAttribute;
    }

    public function getCreateAttribute()
    {
        return $this->_createAttribute;
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
            $tableSchema = $owner->getTableSchema();
            $modifyAttribute = $this->getModifyAttribute();
            if ($modifyAttribute && is_string($modifyAttribute)) {
                if ($owner->hasAttribute($modifyAttribute)) {
                    switch ($tableSchema->getColumn($modifyAttribute)->dbType) {
                        case 'date': $format = $this->getDateFormat(); break;
                        default: $format = $this->getDateTimeFormat();
                    }
                    $owner->{$modifyAttribute} = date($format);
                }
            }
            $createAttribute = $this->getCreateAttribute();
            if ($createAttribute && is_string($createAttribute)) {
                if ($owner->hasAttribute($createAttribute)) {
                    switch ($tableSchema->getColumn($createAttribute)->dbType) {
                        case 'date': $format = $this->getDateFormat(); break;
                        default: $format = $this->getDateTimeFormat();
                    }
                    if ($owner->{$createAttribute}) {
                        if (is_int($owner->{$createAttribute})) {
                            $owner->{$createAttribute} = date($format, $owner->{$createAttribute});
                        } elseif (is_string($owner->{$createAttribute}) && preg_match('~^(\d{2})\D(\d{2})\D(\d{4})$~', $owner->{$createAttribute}, $match)) {
                            if (checkdate($match[2], $match[1], $match[3])) { // d/m/Y
                                $owner->{$createAttribute} = date($format, mktime(0, 0, 0, $match[2], $match[1], $match[3]));
                            } elseif (checkdate($match[1], $match[2], $match[3])) { // m/d/Y
                                $owner->{$createAttribute} = date($format, mktime(0, 0, 0, $match[1], $match[2], $match[3]));
                            }
                        }
                    } elseif ($owner->getIsNewRecord()) {
                        $owner->{$createAttribute} = date($format);
                    } else {
                        $owner->{$createAttribute} = date($format, 0);
                    }
                }
            }
        }
    }

    public function afterFind($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $modifyAttribute = $this->getModifyAttribute();
            if ($modifyAttribute && is_string($modifyAttribute)) {
                if ($owner->hasAttribute($modifyAttribute)) {
                    if ($owner->{$modifyAttribute} && is_string($owner->{$modifyAttribute})) {
                        if (($owner->{$modifyAttribute} == '0000-00-00') || ($owner->{$modifyAttribute} == '0000-00-00 00:00:00')) {
                            $owner->{$modifyAttribute} = 0;
                        } else {
                            $owner->{$modifyAttribute} = strtotime($owner->{$modifyAttribute});
                        }
                    }
                }
            }
            $createAttribute = $this->getCreateAttribute();
            if ($createAttribute && is_string($createAttribute)) {
                if ($owner->hasAttribute($createAttribute)) {
                    if ($owner->{$createAttribute} && is_string($owner->{$createAttribute})) {
                        if (($owner->{$createAttribute} == '0000-00-00') || ($owner->{$createAttribute} == '0000-00-00 00:00:00')) {
                            $owner->{$createAttribute} = 0;
                        } else {
                            $owner->{$createAttribute} = strtotime($owner->{$createAttribute});
                        }
                    }
                }
            }
        }
    }
}
