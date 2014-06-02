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

    private $_dateTimeFormat = 'Y-m-d H:i:s';

    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->_dateTimeFormat = $dateTimeFormat;
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'normalizeBeforeSave',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'normalizeAfterFind',
            ActiveRecord::EVENT_BEFORE_INSERT => 'normalizeBeforeSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'normalizeAfterFind',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'normalizeBeforeSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'normalizeAfterFind'
        ];
    }

    public function normalizeBeforeSave($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            if ($this->_updateAttribute && is_string($this->_updateAttribute)) {
                if ($owner->hasAttribute($this->_updateAttribute)) {
                    $owner->{$this->_updateAttribute} = date($this->_dateTimeFormat);
                }
            }
            if ($this->_createAttribute && is_string($this->_createAttribute)) {
                if ($owner->hasAttribute($this->_createAttribute)) {
                    if ($owner->{$this->_createAttribute}) {
                        if (is_int($owner->{$this->_createAttribute})) {
                            $owner->{$this->_createAttribute} = date($this->_dateTimeFormat, $owner->{$this->_createAttribute});
                        }
                    } elseif ($owner->getIsNewRecord()) {
                        $owner->{$this->_createAttribute} = date($this->_dateTimeFormat);
                    }
                }
            }
        }
    }

    public function normalizeAfterFind($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            if ($this->_updateAttribute && is_string($this->_updateAttribute)) {
                if ($owner->hasAttribute($this->_updateAttribute)) {
                    if ($owner->{$this->_updateAttribute} && is_string($owner->{$this->_updateAttribute})) {
                        $owner->{$this->_updateAttribute} = strtotime($owner->{$this->_updateAttribute});
                    }
                }
            }
            if ($this->_createAttribute && is_string($this->_createAttribute)) {
                if ($owner->hasAttribute($this->_createAttribute)) {
                    if ($owner->{$this->_createAttribute} && is_string($owner->{$this->_createAttribute})) {
                        $owner->{$this->_createAttribute} = strtotime($owner->{$this->_createAttribute});
                    }
                }
            }
        }
    }
}
