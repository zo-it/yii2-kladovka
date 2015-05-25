<?php
/**
 * @var yii\web\View $this
 * @var yii\kladovka\generators\model2\Generator $generator
 */

$use = $generator->prepareUse();
$behaviors = $generator->prepareBehaviors();

echo "<?php\n";
?>

namespace <?php echo $generator->getSecondModelNamespace(); ?>;
<?php echo $generator->renderUse($use); ?>


class <?php echo $generator->getSecondModelName(); ?> extends <?php echo $generator->getModelAlias(); ?>

{

    /**
     * @return <?php echo $generator->getSecondModelName(); ?>Query
     */
    public static function find()
    {
        return Yii::createObject(<?php echo $generator->getSecondModelName(); ?>Query::className(), [get_called_class()]);
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
