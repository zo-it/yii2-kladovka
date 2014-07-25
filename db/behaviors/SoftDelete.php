<?php

namespace yii\kladovka\db\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord,
    yii\base\ModelEvent;


class SoftDelete extends Behavior
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

    private $_deleteValue = 1;

    public function setDeleteValue($deleteValue)
    {
        $this->_deleteValue = $deleteValue;
    }

    public function getDeleteValue()
    {
        return $this->_deleteValue;
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete'
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

    public function softDelete($runValidation = true, $attributeNames = null)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            $deleteAttribute = $this->getDeleteAttribute();
            if ($deleteAttribute && is_string($deleteAttribute)) {
                if ($owner->hasAttribute($deleteAttribute)) {
                    if ($this->beforeDeleteOwner()) {
                        $owner->{$deleteAttribute} = $this->getDeleteValue();
                        $result = $owner->save($runValidation, $attributeNames);
                        $this->afterDeleteOwner();
                        return $result;
                    }
                }
            }
        }
        return false;
    }
}
