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
        return '';
    }

    public function attributes()
    {
        return array_diff(parent::attributes(), ['controllerClass', 'baseControllerClass', 'indexWidgetType']);
    }

    public function rules()
    {
        $rules = [];
        foreach (parent::rules() as $rule) {
            $rule[0] = array_diff((array)$rule[0], ['controllerClass', 'baseControllerClass', 'indexWidgetType']);
            if ($rule[0]) {
                $rules[] = $rule;
            }
        }
        $rules[] = [['searchModelClass'], 'required'];
        return $rules;
    }

    public function validateModelClass()
    {
    }

    public function defaultTemplate()
    {
        $class = new ReflectionClass(get_parent_class($this));
        return dirname($class->getFileName()) . '/default';
    }

    public function requiredTemplates()
    {
        return ['search.php'];
    }

    public function generate()
    {
        $searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\') . '.php'));
        return [new CodeFile($searchModel, $this->render('search.php'))];
    }
}
