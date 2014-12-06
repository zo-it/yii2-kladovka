<?php

namespace yii\kladovka\generators\model2;

use yii\gii\generators\crud\Generator as GiiCrudGenerator,
    yii\gii\CodeFile,
    yii\helpers\StringHelper,
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

    public function requiredTemplates()
    {
        return ['model2.php'];
    }

    public function generate()
    {
        $secondModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->secondModelClass, '\\') . '.php'));
        return [new CodeFile($secondModel, $this->render('model2.php'))];
    }

    public function getModelName()
    {
        return StringHelper::basename($this->modelClass);
    }

    public function getModelNamespace()
    {
        return StringHelper::dirname(ltrim($this->modelClass, '\\'));
    }

    public function getSecondModelName()
    {
        return StringHelper::basename($this->secondModelClass);
    }

    public function getSecondModelNamespace()
    {
        return StringHelper::dirname(ltrim($this->secondModelClass, '\\'));
    }

    public function getModelAlias()
    {
        $modelAlias = $this->getModelName();
        if ($this->getModelNamespace() != $this->getSecondModelNamespace()) {
            if ($modelAlias == $this->getSecondModelName()) {
                $modelAlias .= 'Model';
            }
        }
        return $modelAlias;
    }

    public function prepareUse(array $use = [])
    {
        $modelNamespace = $this->getModelNamespace();
        if ($modelNamespace != $this->getSecondModelNamespace()) {
            $modelName = $this->getModelName();
            if ($modelName == $this->getSecondModelName()) {
                $use[] = $modelNamespace . '\\' . $modelName . ' as ' . $modelName . 'Model';
            } else {
                $use[] = $modelNamespace . '\\' . $modelName;
            }
        }
        $use[] = Yii::$app->hasModule('mozayka') ? 'yii\mozayka\db\ActiveQuery' : 'yii\kladovka\db\ActiveQuery';
        $use[] = 'Yii';
        return $use;
    }

    public function prepareBehaviors(array $behaviors = [])
    {
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

    public function renderBehaviors(array $behaviors)
    {
        $s = '    public function behaviors()' . "\n";
        $s .= '    {' . "\n";
        $s .= '        return [' . "\n";
        foreach (array_values($behaviors) as $i => $behavior) {
            if (is_string($behavior)) {
                $s .= '            \'' . $behavior . '\'' . (($i < count($behaviors) - 1) ? ",\n" : "\n");
            } elseif (is_array($behavior)) {
                $behaviorKeys = array_keys($behavior);
                $behaviorValues = array_values($behavior);
                if (count($behavior) == 1) {
                    $s .= '            [\'' . $behaviorKeys[0] . '\' => \'' . $behaviorValues[0] . '\']' . (($i < count($behaviors) - 1) ? ",\n" : "\n");
                } else {
                    $s .= '            [' . "\n";
                    foreach ($behaviorValues as $j => $behaviorValue) {
                        if (is_string($behaviorValue)) {
                            $s .= '                \'' . $behaviorKeys[$j] . '\' => \'' . $behaviorValue . '\'' . (($j < count($behavior) - 1) ? ",\n" : "\n");
                        } elseif (is_array($behaviorValue)) {
                            $s .= '                \'' . $behaviorKeys[$j] . '\' => [\'' . implode('\', \'', $behaviorValue) . '\']' . (($j < count($behavior) - 1) ? ",\n" : "\n");
                        }
                    }
                    $s .= '            ]' . (($i < count($behaviors) - 1) ? ",\n" : "\n");
                }
            }
        }
        $s .= '        ];' . "\n";
        $s .= '    }' . "\n";
        return $s;
    }
}
