<?php

namespace yii\kladovka\generators\model2;

use yii\gii\generators\crud\Generator as GiiCrudGenerator,
    yii\gii\CodeFile,
    yii\helpers\StringHelper,
    yii\helpers\VarDumper,
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
            $rule[0] = array_diff((array)$rule[0], ['controllerClass', 'baseControllerClass', 'indexWidgetType']);
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
usort($use, function ($use1, $use2) {
    if (preg_match('~^[^\s]+\s+as\s+([^\s]+)$~i', $use1, $match)) {
        $use1 = $match[1];
    } elseif (preg_match('~^[^\s]+\\\\([^\\\\\s]+)$~i', $use1, $match)) {
        $use1 = $match[1];
    }
    if (preg_match('~^[^\s]+\s+as\s+([^\s]+)$~i', $use2, $match)) {
        $use2 = $match[1];
    } elseif (preg_match('~^[^\s]+\\\\([^\\\\\s]+)$~i', $use2, $match)) {
        $use2 = $match[1];
    }
    $use1 = strtolower($use1);
    $use2 = strtolower($use2);
    return ($use1 > $use2) ? 1 : (($use1 < $use2) ? -1 : 0);
});
        return $use;
    }

    public function renderUse(array $use)
    {
        if ($use) {
            return "\n" . 'use ' . implode(",\n    ", $use) . ';' . "\n";
        }
        return '';
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
            } elseif (($columnSchema->name == 'deleted') && ($columnSchema->type == 'smallint') && ($columnSchema->size == 1) && $columnSchema->unsigned && !$columnSchema->allowNull) {
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

    public static function arrayExport(array $var, $tab = 3)
    {
        $s = '[';
        if ((count($var) > 0) && (count(array_filter(array_map('is_int', array_keys($var)))) == count($var)) && (count(array_filter(array_map('is_scalar', $var))) == count($var))) {
            if (count($var) > 5) {
                $s .= "\n";
                $chunks = array_chunk($var, 5);
                $comma = count($chunks);
                foreach ($chunks as $chunk) {
                    $comma --;
                    $s .= str_repeat('    ', $tab);
                    $s .= '\'' . implode('\', \'', $chunk) . '\'';
                    $s .= ($comma ? ",\n" : "\n");
                }
                $s .= str_repeat('    ', $tab - 1);
            } else {
                $s .= '\'' . implode('\', \'', $var) . '\'';
            }
        } elseif (count($var) == 1) {
            $key = array_keys($var)[0];
            $value = array_values($var)[0];
            if (is_array($value)) {
                $arrayExport = static::arrayExport($value, $tab + 1);
                if (is_int($key)) {
                    if (strpos($arrayExport, "\n") === false) {
                        $s .= $arrayExport;
                    } else {
                        $s .= "\n";
                        $s .= str_repeat('    ', $tab) . $arrayExport . "\n";
                        $s .= str_repeat('    ', $tab - 1);
                    }
                } elseif (is_string($key)) {
                    if (strpos($arrayExport, "\n") === false) {
                        $s .= '\'' . $key . '\' => ' . $arrayExport;
                    } else {
                        $s .= "\n";
                        $s .= str_repeat('    ', $tab) . '\'' . $key . '\' => ' . $arrayExport . "\n";
                        $s .= str_repeat('    ', $tab - 1);
                    }
                }
            } elseif (is_scalar($value)) {
                $s .= '\'' . $key . '\' => \'' . $value . '\'';
            }
        } elseif (count($var) > 1) {
            $s .= "\n";
            $comma = count($var);
            foreach ($var as $key => $value) {
                $comma --;
                $s .= str_repeat('    ', $tab);
                if (is_string($key)) {
                    $s .= '\'' . $key . '\' => ';
                }
                if (is_scalar($value)) {
                    $s .= '\'' . $value . '\'';
                } elseif (is_array($value)) {
                    $s .= static::arrayExport($value, $tab + 1);
                }
                $s .= ($comma ? ",\n" : "\n");
            }
            $s .= str_repeat('    ', $tab - 1);
        }
        $s .= ']';
        return $s;
    }

    public function renderBehaviors(array $behaviors)
    {
        $s = "\n" . '    public function behaviors()' . "\n";
        $s .= '    {' . "\n";
        $s .= '        return ' . static::arrayExport(array_values($behaviors), 3) . ';' . "\n";
        $s .= '    }' . "\n";
        return $s;
    }
}
