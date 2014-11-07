<?php
use yii\helpers\StringHelper;
/**
 * @var yii\web\View $this
 * @var yii\kladovka\generators\controller2\Generator $generator
 */

$baseControllerClass = StringHelper::basename($generator->baseControllerClass);
$controllerClass = StringHelper::basename($generator->controllerClass);

$baseControllerNamespace = StringHelper::dirname(ltrim($generator->baseControllerClass, '\\'));
$controllerNamespace = StringHelper::dirname(ltrim($generator->controllerClass, '\\'));

$use = [];
$baseControllerAlias = $baseControllerClass;
if ($baseControllerNamespace != $controllerNamespace) {
    if ($baseControllerClass == $controllerClass) {
        $baseControllerAlias = 'Base' . $baseControllerAlias;
        $use[] = $baseControllerNamespace . '\\' . $baseControllerClass . ' as ' . $baseControllerAlias;
    } else {
        $use[] = $baseControllerNamespace . '\\' . $baseControllerClass;
    }
}
$use[] = $generator->modelClass;

echo "<?php\n";
?>

namespace <?php echo $controllerNamespace; ?>;
<?php if ($use) { ?>

use <?php echo implode(",\n    ", $use); ?>;
<?php } ?>


class <?php echo $controllerClass; ?> extends <?php echo $baseControllerAlias; ?>

{

}
