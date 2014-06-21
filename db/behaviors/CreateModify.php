<?php

namespace ivanchkv\kladovka\db\behaviors;

use yii\db\ActiveRecord;


class CreateModify extends \yii\base\Behavior
{

    private $_modifyAttribute = 'modified';

    public function setModifyAttribute($modifyAttribute)
    {
        $this->_modifyAttribute = $modifyAttribute;
    }

    private $_createAttribute = 'created';

    public function setCreateAttribute($createAttribute)
    {
        $this->_createAttribute = $createAttribute;
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
            if ($this->_modifyAttribute && is_string($this->_modifyAttribute)) {
                if ($owner->hasAttribute($this->_modifyAttribute)) {
                    switch ($tableSchema->getColumn($this->_modifyAttribute)->dbType) {
                        case 'date': $format = $this->_dateFormat; break;
                        default: $format = $this->_dateTimeFormat;
                    }
                    $owner->{$this->_modifyAttribute} = date($format);
                }
            }
            if ($this->_createAttribute && is_string($this->_createAttribute)) {
                if ($owner->hasAttribute($this->_createAttribute)) {
                    switch ($tableSchema->getColumn($this->_createAttribute)->dbType) {
                        case 'date': $format = $this->_dateFormat; break;
                        default: $format = $this->_dateTimeFormat;
                    }
                    if ($owner->{$this->_createAttribute}) {
                        if (is_int($owner->{$this->_createAttribute})) {
                            $owner->{$this->_createAttribute} = date($format, $owner->{$this->_createAttribute});
                        } elseif (is_string($owner->{$this->_createAttribute}) && preg_match('~^(\d{2})\D(\d{2})\D(\d{4})$~', $owner->{$this->_createAttribute}, $match)) {
                            if (checkdate($match[2], $match[1], $match[3])) { // d/m/Y
                                $owner->{$this->_createAttribute} = date($format, mktime(0, 0, 0, $match[2], $match[1], $match[3]));
                            } elseif (checkdate($match[1], $match[2], $match[3])) { // m/d/Y
                                $owner->{$this->_createAttribute} = date($format, mktime(0, 0, 0, $match[1], $match[2], $match[3]));
                            }
                        }
                    } elseif ($owner->getIsNewRecord()) {
                        $owner->{$this->_createAttribute} = date($format);
                    } else {
                        $owner->{$this->_createAttribute} = date($format, 0);
                    }
                }
            }
        }
    }

    public function afterFind($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            if ($this->_modifyAttribute && is_string($this->_modifyAttribute)) {
                if ($owner->hasAttribute($this->_modifyAttribute)) {
                    if ($owner->{$this->_modifyAttribute} && is_string($owner->{$this->_modifyAttribute})) {
                        if (($owner->{$this->_modifyAttribute} == '0000-00-00') || ($owner->{$this->_modifyAttribute} == '0000-00-00 00:00:00')) {
                            $owner->{$this->_modifyAttribute} = 0;
                        } else {
                            $owner->{$this->_modifyAttribute} = strtotime($owner->{$this->_modifyAttribute});
                        }
                    }
                }
            }
            if ($this->_createAttribute && is_string($this->_createAttribute)) {
                if ($owner->hasAttribute($this->_createAttribute)) {
                    if ($owner->{$this->_createAttribute} && is_string($owner->{$this->_createAttribute})) {
                        if (($owner->{$this->_createAttribute} == '0000-00-00') || ($owner->{$this->_createAttribute} == '0000-00-00 00:00:00')) {
                            $owner->{$this->_createAttribute} = 0;
                        } else {
                            $owner->{$this->_createAttribute} = strtotime($owner->{$this->_createAttribute});
                        }
                    }
                }
            }
        }
    }
}
