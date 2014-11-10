<?php

namespace yii\kladovka\generators\controller2;

use yii\gii\generators\crud\Generator as GiiCrudGenerator,
    yii\gii\CodeFile,
    Yii;


class Generator extends GiiCrudGenerator
{

    public function getName()
    {
        return 'Controller Generator 2';
    }

    public function getDescription()
    {
        return 'This generator generates a controller class for the specified search model class.';
    }

    public function attributes()
    {
        return array_diff(parent::attributes(), ['template', 'searchModelClass', 'indexWidgetType']);
    }

    public function rules()
    {
        $rules = [];
        foreach (parent::rules() as $rule) {
            $rule[0] = array_diff($rule[0], ['template', 'searchModelClass', 'indexWidgetType']);
            if ($rule[0]) {
                $rules[] = $rule;
            }
        }
        return $rules;
    }

    public function requiredTemplates()
    {
        return ['controller2.php'];
    }

    public function validateModelClass()
    {
    }

    public function generate()
    {
        $controller = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\') . '.php'));
        $files = [
            new CodeFile($controller, $this->render('controller2.php'))
        ];
        return $files;
    }
}
