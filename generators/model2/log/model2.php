<?php
/**
 * @var yii\web\View $this
 * @var yii\kladovka\generators\model2\Generator $generator
 */

$use = $generator->prepareUse(['yii\log\Logger']);
$behaviors = $generator->prepareBehaviors();
$behaviors['datetime'] = [
    'class' => 'yii\kladovka\behaviors\DatetimeBehavior',
    'attributes' => ['log_time']
];

echo "<?php\n";
?>

namespace <?php echo $generator->getSecondModelNamespace(); ?>;
<?php echo $generator->renderUse($use); ?>


class <?php echo $generator->getSecondModelName(); ?> extends <?php echo $generator->getModelAlias(); ?>

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

    /**
     * @return <?php echo $generator->getSecondModelName(); ?>Query
     */
    public static function find()
    {
        return Yii::createObject(<?php echo $generator->getSecondModelName(); ?>Query::className(), [get_called_class()]);
    }

    public static function gridConfig()
    {
        return [
            'rowOptions' => function ($model, $key, $index, $grid) {
                switch ($model->level) {
                    case Logger::LEVEL_ERROR: return ['class' => 'danger'];
                    case Logger::LEVEL_WARNING: return ['class' => 'warning'];
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


class <?php echo $generator->getSecondModelName(); ?>Query extends ActiveQuery
{

    public function init()
    {
        parent::init();
<?php if (array_key_exists('softDelete', $behaviors)) { ?>
        $alias = $this->getAlias();
        $this->where($alias . '.`deleted` IS NULL OR ' . $alias . '.`deleted` = 0');
<?php } elseif (array_key_exists('timeDelete', $behaviors)) { ?>
        $this->where($this->getAlias() . '.`deleted_at` IS NULL');
<?php } ?>
    }
}
