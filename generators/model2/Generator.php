<?php

namespace yii\kladovka\generators\model2;

use yii\gii\generators\crud\Generator as GiiCrudGenerator,
    yii\gii\CodeFile,
    Yii;


class Generator extends GiiCrudGenerator
{

    public $secondModelClass;

    public function getName()
    {
        return 'Model Generator 2';
    }

    public function getDescription()
    {
        return 'This generator generates a second model class for the specified model class.';
    }

    public function attributes()
    {
        $attributes = array_diff(parent::attributes(), ['template', 'controllerClass', 'baseControllerClass', 'indexWidgetType']);
        $key = array_search('searchModelClass', $attributes);
        if ($key !== false) {
            $attributes[$key] = 'secondModelClass';
        }
        return $attributes;
    }

    public function rules()
    {
        $rules = [];
        foreach (parent::rules() as $rule) {
            $rule[0] = array_diff($rule[0], ['template', 'controllerClass', 'baseControllerClass', 'indexWidgetType']);
            if ($rule[0]) {
                $key = array_search('searchModelClass', $rule[0]);
                if ($key !== false) {
                    $rule[0][$key] = 'secondModelClass';
                }
                $rules[] = $rule;
            }
        }
        $rules[] = [['secondModelClass'], 'required'];
        return $rules;
    }

    public function requiredTemplates()
    {
        return ['model2.php'];
    }

    public function generate()
    {
        $secondModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->secondModelClass, '\\') . '.php'));
        $files = [
            new CodeFile($secondModel, $this->render('model2.php'))
        ];
        return $files;
    }
}
