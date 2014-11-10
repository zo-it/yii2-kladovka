<?php

namespace yii\kladovka\generators\search;

use yii\gii\generators\crud\Generator as GiiCrudGenerator,
    yii\gii\CodeFile,
    ReflectionClass,
    Yii;


class Generator extends GiiCrudGenerator
{

    public $searchModelClass;

    public function getName()
    {
        return 'Search Generator';
    }

    public function getDescription()
    {
        return 'This generator generates a search class for the specified model class.';
    }

    public function attributes()
    {
        return array_diff(parent::attributes(), ['template', 'controllerClass', 'baseControllerClass', 'indexWidgetType']);
    }

    public function rules()
    {
        $rules = [];
        foreach (parent::rules() as $rule) {
            $rule[0] = array_diff($rule[0], ['template', 'controllerClass', 'baseControllerClass', 'indexWidgetType']);
            if ($rule[0]) {
                $rules[] = $rule;
            }
        }
        $rules[] = [['searchModelClass'], 'required'];
        return $rules;
    }

    public function requiredTemplates()
    {
        return ['search.php'];
    }

    public function validateModelClass()
    {
    }

    public function generate()
    {
        $searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\') . '.php'));
        $files = [
            new CodeFile($searchModel, $this->render('search.php'))
        ];
        return $files;
    }

    public function defaultTemplate()
    {
        $class = new ReflectionClass(get_parent_class($this));
        return dirname($class->getFileName()) . '/default';
    }
}
