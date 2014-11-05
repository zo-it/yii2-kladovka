<?php
use yii\helpers\StringHelper;
/**
 * @var yii\web\View $this
 * @var yii\kladovka\generators\model2\Generator $generator
 */

$modelClass = StringHelper::basename($generator->modelClass);
$secondModelClass = StringHelper::basename($generator->secondModelClass);

$modelNamespace = StringHelper::dirname(ltrim($generator->modelClass, '\\'));
$secondModelNamespace = StringHelper::dirname(ltrim($generator->secondModelClass, '\\'));

$baseQueryClass = Yii::$app->hasModule('mozayka') ? 'yii\mozayka\db\ActiveQuery' : 'yii\kladovka\db\ActiveQuery';
$use = [$baseQueryClass];

$modelAlias = $modelClass;
if ($modelNamespace != $secondModelNamespace) {
    if ($modelClass == $secondModelClass) {
        $modelAlias .= 'Model';
        $use[] = $modelNamespace . '\\' . $modelClass . ' as ' . $modelAlias;
    } else {
        $use[] = $modelNamespace . '\\' . $modelClass;
    }
}

$use[] = 'Yii';

echo "<?php\n";
?>

namespace <?php echo $secondModelNamespace; ?>;

use <?php echo implode(",\n    ", $use); ?>;


class <?php echo $secondModelClass; ?> extends <?php echo $modelAlias; ?>

{

    public static function find()
    {
        return Yii::createObject(<?php echo $secondModelClass; ?>Query::className(), [get_called_class()]);
    }

    public function behaviors()
    {
        return [];
    }
}


class <?= $secondModelClass ?>Query extends ActiveQuery
{

    public function init()
    {
        // nothing to do
    }
}
