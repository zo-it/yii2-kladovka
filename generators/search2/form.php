<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\kladovka\generators\search2\Generator $generator
 */

echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'secondModelClass');
echo $form->field($generator, 'moduleID');
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');
