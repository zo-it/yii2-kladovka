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
}
