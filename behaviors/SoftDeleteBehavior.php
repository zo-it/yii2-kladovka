<?php

namespace yii\kladovka\behaviors;

use yii\base\Behavior,
    yii\db\ActiveRecord,
    yii\base\ModelEvent;


class SoftDeleteBehavior extends Behavior
{

    public $deletedAttribute = 'deleted';

    public $value = 1;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'invalidEvent'
        ];
    }

    public function invalidEvent($event)
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
            $deletedAttribute = $this->deletedAttribute;
            if ($deletedAttribute && is_string($deletedAttribute) && $owner->hasAttribute($deletedAttribute)) {
                if ($this->beforeDeleteOwner()) {
                    $owner->{$deletedAttribute} = $this->value;
                    $result = $owner->save($runValidation, $attributeNames);
                    $this->afterDeleteOwner();
                    return $result;
                }
            }
        }
        return false;
    }
}
