<?php
/**
 * @var yii\web\View $this
 * @var yii\kladovka\generators\search2\Generator $generator
 */

$use = $generator->prepareUse(['yii\base\Model']);
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
<?php echo $generator->renderBehaviors($behaviors); ?>

    public function beforeValidate()
    {
        return Model::beforeValidate();
    }

    public function afterValidate()
    {
        Model::afterValidate();
    }
}
