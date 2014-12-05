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

$use = [];
$modelAlias = $modelClass;
if ($modelNamespace != $secondModelNamespace) {
    if ($modelClass == $secondModelClass) {
        $modelAlias .= 'Model';
        $use[] = $modelNamespace . '\\' . $modelClass . ' as ' . $modelAlias;
    } else {
        $use[] = $modelNamespace . '\\' . $modelClass;
    }
}
$use[] = 'yii\log\Logger';
$use[] = Yii::$app->hasModule('mozayka') ? 'yii\mozayka\db\ActiveQuery' : 'yii\kladovka\db\ActiveQuery';
$use[] = 'Yii';

$behaviors = $generator->prepareBehaviors();
$behaviors['datetime'] = [
    'class' => 'yii\kladovka\behaviors\DatetimeBehavior',
    'attributes' => ['log_time']
];

echo "<?php\n";
?>

namespace <?php echo $secondModelNamespace; ?>;
<?php if ($use) { ?>

use <?php echo implode(",\n    ", $use); ?>;
<?php } ?>


class <?php echo $secondModelClass; ?> extends <?php echo $modelAlias; ?>

{

    const LEVEL_ERROR = Logger::LEVEL_ERROR;
    const LEVEL_WARNING = Logger::LEVEL_WARNING;
    const LEVEL_INFO = Logger::LEVEL_INFO;
    const LEVEL_TRACE = Logger::LEVEL_TRACE;
    const LEVEL_PROFILE = Logger::LEVEL_PROFILE;
    const LEVEL_PROFILE_BEGIN = Logger::LEVEL_PROFILE_BEGIN;
    const LEVEL_PROFILE_END = Logger::LEVEL_PROFILE_END;

    public static function levelListItems()
    {
        return [
            self::LEVEL_ERROR => 'ERROR',
            self::LEVEL_WARNING => 'WARNING',
            self::LEVEL_INFO => 'INFO',
            self::LEVEL_TRACE => 'TRACE',
            self::LEVEL_PROFILE => 'PROFILE',
            self::LEVEL_PROFILE_BEGIN => 'PROFILE_BEGIN',
            self::LEVEL_PROFILE_END => 'PROFILE_END'
        ];
    }

    public static function find()
    {
        return Yii::createObject(<?php echo $secondModelClass; ?>Query::className(), [get_called_class()]);
    }

    public static function gridConfig()
    {
        return [
            'rowOptions' => function ($model, $key, $index, $grid) {
                switch ($model->level) {
                    case $model::LEVEL_ERROR: return ['class' => 'danger'];
                    case $model::LEVEL_WARNING: return ['class' => 'warning'];
                }
                return [];
            }
        ];
    }

<?php echo $generator->renderBehaviors($behaviors); ?>
<?php if (array_key_exists('softDelete', $behaviors)) { ?>

    public function delete()
    {
        return $this->softDelete();
    }
<?php } elseif (array_key_exists('timeDelete', $behaviors)) { ?>

    public function delete()
    {
        return $this->timeDelete();
    }
<?php } ?>
}


class <?php echo $secondModelClass; ?>Query extends ActiveQuery
{

    public function init()
    {
        parent::init();
<?php if (array_key_exists('softDelete', $behaviors)) { ?>
        $this->where($this->getAlias() . '.`deleted` = 0');
<?php } elseif (array_key_exists('timeDelete', $behaviors)) { ?>
        $this->where($this->getAlias() . '.`deleted_at` IS NULL');
<?php } ?>
    }
}
