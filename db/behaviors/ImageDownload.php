<?php

namespace yii\kladovka\db\behaviors;

use Yii,
    yii\base\Behavior,
    yii\db\ActiveRecord,
    yii\helpers\Url,
    yii\helpers\Html,
    yii\kladovka\net\Curl,
    yii\kladovka\image\magick\Convert;


class ImageDownload extends Behavior
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

    private $_baseDir = '@app/web/uploads';

    public function setBaseDir($baseDir)
    {
        $this->_baseDir = $baseDir;
    }

    public function getBaseDir()
    {
        return $this->_baseDir;
    }

    private $_dirMode = 0775;

    public function setDirMode($dirMode)
    {
        $this->_dirMode = $dirMode;
    }

    public function getDirMode()
    {
        return $this->_dirMode;
    }

    private $_convertConfig = [];

    public function setConvertConfig(array $convertConfig)
    {
        $this->_convertConfig = $convertConfig;
    }

    public function getConvertConfig()
    {
        return $this->_convertConfig;
    }

    private $_baseUrl = '@web/uploads';

    public function setBaseUrl($baseUrl)
    {
        $this->_baseUrl = $baseUrl;
    }

    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    private $_modifyAttribute = 'modified';

    public function setModifyAttribute($modifyAttribute)
    {
        $this->_modifyAttribute = $modifyAttribute;
    }

    public function getModifyAttribute()
    {
        return $this->_modifyAttribute;
    }

    private $_noImageUrl = false;

    public function setNoImageUrl($noImageUrl)
    {
        $this->_noImageUrl = $noImageUrl;
    }

    public function getNoImageUrl()
    {
        return $this->_noImageUrl;
    }

    private $_htmlOptions = [];

    public function setHtmlOptions(array $htmlOptions)
    {
        $this->_htmlOptions = $htmlOptions;
    }

    public function getHtmlOptions()
    {
        return $this->_htmlOptions;
    }

    private $_attributes = [];

    public function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    protected function getAttributeDefaultConfig()
    {
        return [
            'baseDir' => $this->getBaseDir(),
            'dirMode' => $this->getDirMode(),
            'convertConfig' => $this->getConvertConfig(),
            'baseUrl' => $this->getBaseUrl(),
            'modifyAttribute' => $this->getModifyAttribute(),
            'noImageUrl' => $this->getNoImageUrl(),
            'htmlOptions' => $this->getHtmlOptions()
        ];
    }

    protected function buildAttributes()
    {
        $defaultConfig = $this->getAttributeDefaultConfig();
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
            $basenamePrefix = substr(sprintf('%u', crc32($primaryKey)), 0, 2);
            if (strlen($basenamePrefix) == 1) {
                $basenamePrefix .= $basenamePrefix;
            }
            foreach ($this->buildAttributes() as $sourceAttributeName => $destAttributes) {
                if ($owner->{$sourceAttributeName} && is_string($owner->{$sourceAttributeName}) && preg_match('~^(https?\://[^\s]+)\s(\d+)$~i', $owner->{$sourceAttributeName}, $match) && ($match[2] != 200)) {
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
                        $basename = $basenamePrefix . DIRECTORY_SEPARATOR . $primaryKey . '.' . $extension;
                        foreach ($destAttributes as $destAttributeName => $config) {
                            $outputFilename = Yii::getAlias($config['baseDir'] . DIRECTORY_SEPARATOR . $owner::tableName() . DIRECTORY_SEPARATOR . $destAttributeName . DIRECTORY_SEPARATOR . $basename);
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
                $tableName = preg_replace('~^.+\.([^\.]+)$~', '$1', $owner::tableName());
                $filename = Yii::getAlias($attributeConfig['baseDir'] . DIRECTORY_SEPARATOR . $tableName . DIRECTORY_SEPARATOR . $attributeName . DIRECTORY_SEPARATOR . $basename);
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
                $tableName = preg_replace('~^.+\.([^\.]+)$~', '$1', $owner::tableName());
                $url = Url::to($attributeConfig['baseUrl'] . '/' . $tableName . '/' . $attributeName . '/' . $basename);
                // file.jpg?modified
                $modifyAttribute = $attributeConfig['modifyAttribute'];
                if ($modifyAttribute && is_string($modifyAttribute) && $owner->hasAttribute($modifyAttribute) && $owner->{$modifyAttribute} && is_int($owner->{$modifyAttribute})) {
                    $url .= '?' . $owner->{$modifyAttribute};
                }
                return $url;
            } else {
                $noImageUrl = $attributeConfig['noImageUrl'];
                if ($noImageUrl && is_string($noImageUrl)) {
                    return Url::to($noImageUrl);
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
                $owner->update(false, array_keys($newAttributes));
            }
        }
    }
}
