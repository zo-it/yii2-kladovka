<?php

namespace yii\kladovka\generators\model;

use yii\gii\generators\model\Generator as GiiModelGenerator,
    ReflectionClass;


class Generator extends GiiModelGenerator
{

    public function defaultTemplate()
    {
        $class = new ReflectionClass(get_parent_class($this));
        return dirname($class->getFileName()) . '/default';
    }
}
