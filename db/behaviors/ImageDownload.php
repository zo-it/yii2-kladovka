<?php

namespace ivanchkv\kladovka\db\behaviors;

use Yii,
    yii\db\ActiveRecord,
    ivanchkv\kladovka\net\Curl;


class ImageDownload extends \yii\base\Behavior
{

    public function init()
    {
        if (array_key_exists(__CLASS__, Yii::$app->params)) {
            $config = Yii::$app->params[__CLASS__];
            if ($config && is_array($config)) {
                foreach ($config as $key => $value) {
                    if ($key && is_string($key)) {
                        $methodName = 'set' . ucfirst($key);
                        if (method_exists($this, $methodName)) {
                            $this->{$methodName}($value);
                        }
                    }
                }
            }
        }
    }

    private $_downloadDir = '@app/uploads';

    public function setDownloadDir($downloadDir)
    {
        $this->_downloadDir = $downloadDir;
    }

    private $_downloadUrl = '';

    public function setDownloadUrl($downloadUrl)
    {
        $this->_downloadUrl = $downloadUrl;
    }

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
$primaryKey = $owner->getPrimaryKey();
if (is_array($primaryKey)) {
$primaryKey = vsprintf(implode('-%s_', array_keys($primaryKey)) . '-%s', array_values($primaryKey));
}
            foreach ($this->buildAttributes() as $sourceAttributeName => $destAttributes) {
                if ($owner->{$sourceAttributeName} && is_string($owner->{$sourceAttributeName}) && preg_match('~^(https?\://[^\s]+)(?:\s(\d+))?$~i', $owner->{$sourceAttributeName}, $match)) {
$url = $match[1];
$curl = Curl::init($url)->setIsTempFilename(true);
$curl->execute();
$owner->{$sourceAttributeName} = $url . ' ' . $curl->getHttpCode();
$newAttributes[$sourceAttributeName] = $owner->{$sourceAttributeName};
$inputFilename = $curl->getFilename();
if (file_exists($inputFilename)) {
$contentType = mime_content_type($inputFilename);
$contentTypeFileExtensionMap = [
'image/jpeg' => 'jpg'
];
$fileExtension = array_key_exists($contentType, $contentTypeFileExtensionMap) ? $contentTypeFileExtensionMap[$contentType] : 'ext';
$fileBasename = $primaryKey . '.' . $fileExtension;

foreach ($destAttributes as $destAttributeName => $rules) {
//
$fileDirname = Yii::getAlias($this->_downloadDir) . DIRECTORY_SEPARATOR . $owner::tableName() . DIRECTORY_SEPARATOR . $destAttributeName . DIRECTORY_SEPARATOR . $fileBasename[0];
if (!file_exists($fileDirname)) {
mkdir($fileDirname, 0770, true);
}
$outputFilename = $fileDirname . DIRECTORY_SEPARATOR . $fileBasename;
var_dump($outputFilename);
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
