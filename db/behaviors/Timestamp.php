<?php

namespace ivanchkv\kladovka\db\behaviors;

use yii\db\ActiveRecord;


class Timestamp extends \yii\base\Behavior
{

    private $_updateAttribute = 'modified';

    public function setUpdateAttribute($updateAttribute)
    {
        $this->_updateAttribute = $updateAttribute;
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
            if ($this->_updateAttribute && is_string($this->_updateAttribute)) {
                if ($owner->hasAttribute($this->_updateAttribute)) {
                    $format = ($tableSchema->getColumn($this->_updateAttribute)->dbType == 'date') ? $this->_dateFormat : $this->_dateTimeFormat;
                    $owner->{$this->_updateAttribute} = date($format);
                }
            }
            if ($this->_createAttribute && is_string($this->_createAttribute)) {
                if ($owner->hasAttribute($this->_createAttribute)) {
                    $format = ($tableSchema->getColumn($this->_updateAttribute)->dbType == 'date') ? $this->_dateFormat : $this->_dateTimeFormat;
                    if ($owner->{$this->_createAttribute}) {
                        if (is_int($owner->{$this->_createAttribute})) {
                            $owner->{$this->_createAttribute} = date($format, $owner->{$this->_createAttribute});
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
            if ($this->_updateAttribute && is_string($this->_updateAttribute)) {
                if ($owner->hasAttribute($this->_updateAttribute)) {
                    if ($owner->{$this->_updateAttribute} && is_string($owner->{$this->_updateAttribute})) {
                        if (($owner->{$this->_updateAttribute} == '0000-00-00') || ($owner->{$this->_updateAttribute} == '0000-00-00 00:00:00')) {
                            $owner->{$this->_updateAttribute} = 0;
                        } else {
                            $owner->{$this->_updateAttribute} = strtotime($owner->{$this->_updateAttribute});
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
