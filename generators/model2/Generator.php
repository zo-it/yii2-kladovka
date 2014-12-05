<?php

namespace yii\kladovka\generators\model2;

use yii\gii\generators\crud\Generator as GiiCrudGenerator,
    yii\gii\CodeFile,
    Yii;


class Generator extends GiiCrudGenerator
{

    public $templates = [
        'log' => '@yii/kladovka/generators/model2/log',
        'user' => '@yii/kladovka/generators/model2/user'
    ];

    public $secondModelClass;

    public function getName()
    {
        return 'Model Generator 2';
    }

    public function getDescription()
    {
        return '';
    }

    public function attributes()
    {
        $attributes = array_diff(parent::attributes(), ['controllerClass', 'baseControllerClass', 'indexWidgetType']);
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
            $rule[0] = array_diff($rule[0], ['controllerClass', 'baseControllerClass', 'indexWidgetType']);
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

    public function validateModelClass()
    {
    }

    public function prepareBehaviors()
    {
        $behaviors = [];
        foreach ($this->getTableSchema()->columns as $columnSchema) {
            if (in_array($columnSchema->type, ['datetime', 'date', 'time'])) {
                if (in_array($columnSchema->name, ['created_at', 'updated_at', 'timestamp'])) {
                    $behaviors['timestamp'] = 'yii\kladovka\behaviors\TimestampBehavior';
                    continue;
                } elseif ($columnSchema->allowNull && ($columnSchema->name == 'deleted_at')) {
                    $behaviors['timeDelete'] = 'yii\kladovka\behaviors\TimeDeleteBehavior';
                    continue;
                } elseif (!array_key_exists('datetime', $behaviors)) {
                    $behaviors['datetime'] = [
                        'class' => 'yii\kladovka\behaviors\DatetimeBehavior',
                        'attributes' => [$columnSchema->name]
                    ];
                } else {
                    $behaviors['datetime']['attributes'][] = $columnSchema->name;
                }
            } elseif (($columnSchema->type == 'smallint') && ($columnSchema->size == 1) && $columnSchema->unsigned && !$columnSchema->allowNull && ($columnSchema->name == 'deleted')) {
                $behaviors['softDelete'] = 'yii\kladovka\behaviors\SoftDeleteBehavior';
                continue;
            }
            if ($columnSchema->allowNull) {
                if (!array_key_exists('nullable', $behaviors)) {
                    $behaviors['nullable'] = [
                        'class' => 'yii\kladovka\behaviors\NullableBehavior',
                        'attributes' => [$columnSchema->name]
                    ];
                } else {
                    $behaviors['nullable']['attributes'][] = $columnSchema->name;
                }
            }
        }
        return $behaviors;
    }

    public function requiredTemplates()
    {
        return ['model2.php'];
    }

    public function generate()
    {
        $secondModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->secondModelClass, '\\') . '.php'));
        return [new CodeFile($secondModel, $this->render('model2.php'))];
    }
}
