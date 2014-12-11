<?php

namespace yii\kladovka\generators\search2;

use yii\kladovka\generators\model2\Generator as Model2Generator,
    yii\gii\CodeFile,
    Yii;


class Generator extends Model2Generator
{

    public $templates = [
        'log' => '@yii/kladovka/generators/search2/log',
        'user' => '@yii/kladovka/generators/search2/user'
    ];

    public function getName()
    {
        return 'Search Generator 2';
    }

    public function requiredTemplates()
    {
        return ['search2.php'];
    }

    public function generate()
    {
        $secondModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->secondModelClass, '\\') . '.php'));
        return [new CodeFile($secondModel, $this->render('search2.php'))];
    }

    public function prepareBehaviors(array $behaviors = [])
    {
        foreach ($this->getTableSchema()->columns as $columnSchema) {
            if (in_array($columnSchema->type, ['datetime', 'date', 'time'])) {
                if (!array_key_exists('datetime', $behaviors)) {
                    $behaviors['datetime'] = [
                        'class' => 'yii\kladovka\behaviors\DatetimeBehavior',
                        'attributes' => [$columnSchema->name]
                    ];
                } else {
                    $behaviors['datetime']['attributes'][] = $columnSchema->name;
                }
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
}
