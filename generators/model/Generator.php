<?php

namespace yii\kladovka\generators\model;

use yii\gii\generators\model\Generator as GiiModelGenerator,
    ReflectionClass;


class Generator extends GiiModelGenerator
{

    public $use = 'Yii';

    public function defaultTemplate()
    {
        $class = new ReflectionClass(get_parent_class($this));
        return dirname($class->getFileName()) . '/default';
    }

    public function render($template, $params = [])
    {
        $operatorUse = 'use ' . implode(",\n    ", array_map('trim', explode(',', $this->use))) . ';';
        return str_replace('use Yii;', $operatorUse, parent::render($template, $params));
    }
}
