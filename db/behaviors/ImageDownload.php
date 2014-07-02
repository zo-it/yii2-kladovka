<?php

namespace yii\kladovka\db\behaviors;

use Yii,
    yii\db\ActiveRecord,
    yii\helpers\Url,
    yii\helpers\Html,
    yii\kladovka\net\Curl,
    yii\kladovka\image\magick\Convert;


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

    private $_downloadDir = '@app/web/uploads';

    public function setDownloadDir($downloadDir)
    {
        $this->_downloadDir = $downloadDir;
    }

    private $_dirMode = 0770;

    public function setDirMode($dirMode)
    {
        $this->_dirMode = $dirMode;
    }

    private $_convertConfig = [];

    public function setConvertConfig(array $convertConfig)
    {
        $this->_convertConfig = $convertConfig;
    }

    private $_downloadUrl = '@web/uploads';

    public function setDownloadUrl($downloadUrl)
    {
        $this->_downloadUrl = $downloadUrl;
    }

    private $_modifyAttribute = 'modified';

    public function setModifyAttribute($modifyAttribute)
    {
        $this->_modifyAttribute = $modifyAttribute;
    }

    private $_defaultImageUrl = false;

    public function setDefaultImageUrl($defaultImageUrl)
    {
        $this->_defaultImageUrl = $defaultImageUrl;
    }

    private $_htmlOptions = [];

    public function setHtmlOptions(array $htmlOptions)
    {
        $this->_htmlOptions = $htmlOptions;
    }

    private $_attributes = [];

    public function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    protected function buildAttributes()
    {
        $defaultConfig = [
            'downloadDir' => $this->_downloadDir,
            'dirMode' => $this->_dirMode,
            'convertConfig' => $this->_convertConfig,
            'downloadUrl' => $this->_downloadUrl,
            'modifyAttribute' => $this->_modifyAttribute,
            'defaultImageUrl' => $this->_defaultImageUrl,
            'htmlOptions' => $this->_htmlOptions
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
                            } elseif ($key && is_string($key) && $value/* && (is_string($value) || is_array($value))*/) {
                                $destAttributeName = $key;
                                if (is_string($value)) {
                                    $value = [
                                        'resize' => $value
                                    ];
                                }
                                if (is_array($value)) {
                                    if (array_key_exists('convertConfig', $value)) {
                                        $config = array_merge($defaultConfig, array_intersect_key($value, $defaultConfig));
                                    } else {
                                        $config = array_merge($defaultConfig, ['convertConfig' => $value]);
                                    }
                                    if ($owner->hasAttribute($destAttributeName)) {
                                        $destAttributes2[$destAttributeName] = $config;
                                    }
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

    protected function getAttributeConfig($attributeName)
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
            $primaryKeyCrc32 = sprintf('%u', crc32($primaryKey));
            $basenamePrefix = substr($primaryKeyCrc32, 0, 2) . DIRECTORY_SEPARATOR;
            foreach ($this->buildAttributes() as $sourceAttributeName => $destAttributes) {
                if ($owner->{$sourceAttributeName} && is_string($owner->{$sourceAttributeName}) && preg_match('~^(https?\://[^\s]+)(?:\s(\d+))?$~i', $owner->{$sourceAttributeName}, $match)) {
                    $url = $match[1];
                    $curl = Curl::init([
                        'url' => $url,
                        'isTempFilename' => true
                    ]);
                    $curl->execute();
                    $owner->{$sourceAttributeName} = $url . ' ' . $curl->getHttpCode();
                    $newAttributes[$sourceAttributeName] = $owner->{$sourceAttributeName};
                    $inputFilename = $curl->getFilename();
                    if (file_exists($inputFilename)) {
                        $contentType = mime_content_type($inputFilename);
                        $contentTypeFileExtensionMap = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/gif' => 'gif'
                        ];
                        $extension = array_key_exists($contentType, $contentTypeFileExtensionMap) ? $contentTypeFileExtensionMap[$contentType] : 'jpg';
                        $basename = $basenamePrefix . $primaryKey . '.' . $extension;
                        foreach ($destAttributes as $destAttributeName => $config) {
                            $outputFilename = Yii::getAlias($config['downloadDir'] . DIRECTORY_SEPARATOR . $owner::tableName() . DIRECTORY_SEPARATOR . $destAttributeName . DIRECTORY_SEPARATOR . $basename);
                            $dir = dirname($outputFilename);
                            if (!file_exists($dir)) {
                                mkdir($dir, $config['dirMode'], true);
                            }
                            $convert = Convert::init($config['convertConfig'])->setInputFilename($inputFilename)->setOutputFilename($outputFilename);
                            if ($convert->execute()) {
                                $owner->{$destAttributeName} = $basename;
                                $newAttributes[$destAttributeName] = $owner->{$destAttributeName};
                            }
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
            $owner = $this->owner;
            $basename = $owner->{$attributeName};
            if ($basename && is_string($basename)) {
                $filename = Yii::getAlias($attributeConfig['downloadDir'] . DIRECTORY_SEPARATOR . $owner::tableName() . DIRECTORY_SEPARATOR . $attributeName . DIRECTORY_SEPARATOR . $basename);
                if (file_exists($filename)) {
                    return $filename;
                }
            }
        }
        return false;
    }

    public function getUrl($attributeName)
    {
        $attributeConfig = $this->getAttributeConfig($attributeName);
        if ($attributeConfig && is_array($attributeConfig)) {
            $filename = $this->getFilename($attributeName);
            if ($filename && is_string($filename)) {
                $owner = $this->owner;
                $basename = $owner->{$attributeName};
                $url = Url::to($attributeConfig['downloadUrl'] . '/' . $owner::tableName() . '/' . $attributeName . '/' . $basename);
                // file.jpg?modified
                $modifyAttribute = $attributeConfig['modifyAttribute'];
                if ($modifyAttribute && is_string($modifyAttribute) && $owner->hasAttribute($modifyAttribute) && $owner->{$modifyAttribute} && is_int($owner->{$modifyAttribute})) {
                    $url .= '?' . $owner->{$modifyAttribute};
                }
                return $url;
            } else {
                $defaultImageUrl = $attributeConfig['defaultImageUrl'];
                if ($defaultImageUrl && is_string($defaultImageUrl)) {
                    return Url::to($defaultImageUrl);
                }
            }
        }
        return false;
    }

    public function getHtml($attributeName, array $htmlOptions = [])
    {
        $attributeConfig = $this->getAttributeConfig($attributeName);
        if ($attributeConfig && is_array($attributeConfig)) {
            $url = $this->getUrl($attributeName);
            if ($url && is_string($url)) {
                return Html::img($url, array_merge($attributeConfig['htmlOptions'], $htmlOptions));
            }
        }
        return false;
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert'
        ];
    }

    public function beforeUpdate($event)
    {
        $owner = $this->owner;
        if (($owner instanceof ActiveRecord) && $owner->getPrimaryKey()) {
$this->processImageDownload();
        }
    }

    public function afterInsert($event)
    {
        $owner = $this->owner;
        if ($owner instanceof ActiveRecord) {
$newAttributes = [];
$this->processImageDownload($newAttributes);
if ($newAttributes) {
//$owner->updateAll($newAttributes, $condition = '', $params = [])
}
        }
    }
}
