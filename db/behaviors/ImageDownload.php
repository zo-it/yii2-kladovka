<?php

namespace ivanchkv\kladovka\db\behaviors;

use yii\db\ActiveRecord,
    ivanchkv\kladovka\net\Curl;


class ImageDownload extends \yii\base\Behavior
{

    private $_rules = [];

    public function setRules(array $rules)
    {
        $this->_rules = $rules;
    }

    private $_attributes = [];

    public function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    protected function buildAttributes()
    {
        $attributes = [];
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            foreach ($this->_attributes as $sourceAttributeName => $config) {
                if ($sourceAttributeName && is_string($sourceAttributeName) && $config && is_array($config)) {
                    if ($owner->hasAttribute($sourceAttributeName)) {
                        $config2 = [];
                        foreach ($config as $key => $value) {
                            if (is_int($key) && $value && is_string($value)) {
                                $destAttributeName = $value;
                                $rules = $this->_rules;
                                if ($owner->hasAttribute($destAttributeName)) {
                                    $config2[$destAttributeName] = $rules;
                                }
                            } elseif ($key && is_string($key) && $value && is_array($value)) {
                                $destAttributeName = $key;
                                $rules = $value;
                                if ($owner->hasAttribute($destAttributeName)) {
                                    $config2[$destAttributeName] = $rules;
                                }
                            }
                        }
                        $attributes[$sourceAttributeName] = $config2;
                    }
                }
            }
        }
        return $attributes;
    }

    protected function processImageDownload(array &$newAttributes = [])
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            foreach ($this->buildAttributes() as $sourceAttributeName => $destAttributes) {
                if ($owner->{$sourceAttributeName} && is_string($owner->{$sourceAttributeName}) && preg_match('~^(https?\://[^\s]+)(?:\s(\d+))?$~i', $owner->{$sourceAttributeName}, $match)) {
$url = $match[1];
$curl = Curl::init($url)->setIsTempFilename(true);
$curl->execute();
$owner->{$sourceAttributeName} = $url . ' ' . $curl->getHttpCode();
$newAttributes[$sourceAttributeName] = $owner->{$sourceAttributeName};
$filename = $curl->getFilename();
if (file_exists($filename)) {
foreach ($destAttributes as $destAttributeName => $rules) {
//
var_dump($destAttributeName);
//
}
}
                }
            }
        }
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeSave',
            /*ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'afterFind',*/
//ActiveRecord::EVENT_AFTER_INSERT => 'afterFind',
//ActiveRecord::EVENT_AFTER_UPDATE => 'afterFind',
//ActiveRecord::EVENT_AFTER_FIND => 'afterFind'
        ];
    }

    public function beforeSave($event)
    {
        $owner = $this->owner;
        if (($owner instanceof ActiveRecord) && $owner->getPrimaryKey()) {
$this->processImageDownload();
        }
    }

    public function afterFind($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
$newAttributes = [];
$this->processImageDownload($newAttributes);
        }
    }
}
