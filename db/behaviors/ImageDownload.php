<?php

namespace ivanchkv\kladovka\db\behaviors;

use Yii,
    yii\db\ActiveRecord,
    yii\helpers\Url,
    yii\helpers\Html,
    ivanchkv\kladovka\net\Curl,
    ivanchkv\kladovka\image\Magick;


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

    private $_rules = [];

    public function setRules(array $rules)
    {
        $this->_rules = $rules;
    }

    private $_downloadDir = '@app/web/uploads';

    public function setDownloadDir($downloadDir)
    {
        $this->_downloadDir = $downloadDir;
    }

    private $_downloadUrl = '@web/uploads';

    public function setDownloadUrl($downloadUrl)
    {
        $this->_downloadUrl = $downloadUrl;
    }

    private $_defaultImageUrl = false;

    public function setDefaultImageUrl($defaultImageUrl)
    {
        $this->_defaultImageUrl = $defaultImageUrl;
    }

    private $_attributes = [];

    public function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    protected function buildAttributes()
    {
        $defaultConfig = [
            'rules' => $this->_rules,
            'downloadDir' => $this->_downloadDir,
            'downloadUrl' => $this->_downloadUrl,
            'defaultImageUrl' => $this->_defaultImageUrl
        ];
        $attributes = [];
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
            foreach ($this->_attributes as $sourceAttributeName => $destAttributes) {
                if ($sourceAttributeName && is_string($sourceAttributeName) && $destAttributes && is_array($destAttributes)) {
                    if ($owner->hasAttribute($sourceAttributeName)) {
                        $destAttributes2 = [];
                        foreach ($destAttributes as $key => $value) {
                            if (is_int($key) && $value && is_string($value)) {
                                $destAttributeName = $value;
                                $config = $defaultConfig;
                                if ($owner->hasAttribute($destAttributeName)) {
                                    $destAttributes2[$destAttributeName] = $config;
                                }
                            } elseif ($key && is_string($key) && $value && is_array($value)) {
                                $destAttributeName = $key;
                                if (array_key_exists('rules', $value)) {
                                    $config = array_merge($defaultConfig, array_intersect_key($config, $defaultConfig));
                                } else {
                                    $config = $defaultConfig;
                                    $config['rules'] = $value;
                                }
                                if ($owner->hasAttribute($destAttributeName)) {
                                    $destAttributes2[$destAttributeName] = $config;
                                }
                            }
                        }
                        $attributes[$sourceAttributeName] = $destAttributes2;
                    }
                }
            }
        }
        return $attributes;
    }

    public function getAttributeConfig($attributeName)
    {
        foreach ($this->buildAttributes() as $sourceAttributeName => $destAttributes) {
            foreach ($destAttributes as $destAttributeName => $config) {
                if ($destAttributeName == $attributeName) {
                    return $config;
                }
            }
        }
        return false;
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
$extension = array_key_exists($contentType, $contentTypeFileExtensionMap) ? $contentTypeFileExtensionMap[$contentType] : 'ext';
$basename = $primaryKey . '.' . $extension;

foreach ($destAttributes as $destAttributeName => $config) {
$config['rules']['inputFilename'] = $inputFilename;

$dirname = Yii::getAlias($config['downloadDir'] . DIRECTORY_SEPARATOR . $owner->tableName() . DIRECTORY_SEPARATOR . $destAttributeName . DIRECTORY_SEPARATOR . $basename[0]);
if (!file_exists($dirname)) {
mkdir($dirname, 0770, true);
}
$outputFilename = $dirname . DIRECTORY_SEPARATOR . $basename;
$config['rules']['outputFilename'] = $outputFilename;
if (Magick::init($config['rules'])->execute()) {
$owner->{$destAttributeName} = $basename;
$newAttributes[$destAttributeName] = $owner->{$destAttributeName};
}
//
}


}
                }
            }
        }
    }

    public function getFilename($attributeName)
    {
        $attributeConfig = $this->getAttributeConfig($attributeName);
        if ($attributeConfig && is_array($attributeConfig)) {
            $basename = $this->owner->{$attributeName};
            $dirname = Yii::getAlias($attributeConfig['downloadDir'] . DIRECTORY_SEPARATOR . $this->owner->tableName() . DIRECTORY_SEPARATOR . $attributeName . DIRECTORY_SEPARATOR . $basename[0]);
            $filename = $dirname . DIRECTORY_SEPARATOR . $basename;
            if (file_exists($filename)) {
                return $filename;
            }
        }
        return false;
    }

    public function getUrl($attributeName)
    {
        $attributeConfig = $this->getAttributeConfig($attributeName);
        if ($attributeConfig && is_array($attributeConfig)) {
            $basename = $this->owner->{$attributeName};
            $dirname = Yii::getAlias($attributeConfig['downloadDir'] . DIRECTORY_SEPARATOR . $this->owner->tableName() . DIRECTORY_SEPARATOR . $attributeName . DIRECTORY_SEPARATOR . $basename[0]);
            $filename = $dirname . DIRECTORY_SEPARATOR . $basename;
            if (file_exists($filename)) {
                return Url::to($attributeConfig['downloadUrl'] . '/' . $this->owner->tableName() . '/' . $attributeName . '/' . $basename[0] . '/' . $basename);
            } elseif ($attributeConfig['defaultImageUrl'] && is_string($attributeConfig['defaultImageUrl'])) {
                return Url::to($attributeConfig['defaultImageUrl']);
            }
        }
        return false;
    }

    public function getImgTag($attributeName, $options = [])
    {
        $url = $this->getUrl($attributeName);
        if ($url && is_string($url)) {
            return Html::img($url, $options);
        }
        return false;
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
