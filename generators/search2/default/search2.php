<?php
use yii\helpers\StringHelper;
/**
 * @var yii\web\View $this
 * @var yii\kladovka\generators\search2\Generator $generator
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

$behaviors = [];
foreach ($generator->getTableSchema()->columns as $columnSchema) {
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

echo "<?php\n";
?>

namespace <?php echo $secondModelNamespace; ?>;
<?php if ($use) { ?>

use <?php echo implode(",\n    ", $use); ?>;
<?php } ?>


class <?php echo $secondModelClass; ?> extends <?php echo $modelAlias; ?>

{
<?php /*if ($behaviors) {*/ ?>

    public function behaviors()
    {
        return [
<?php
foreach (array_values($behaviors) as $i => $behavior) {
    if (is_string($behavior)) {
        echo '            \'' . $behavior . '\'' . (($i < count($behaviors) - 1) ? ",\n" : "\n");
    } elseif (is_array($behavior)) {
        $behaviorKeys = array_keys($behavior);
        $behaviorValues = array_values($behavior);
        if (count($behavior) == 1) {
            echo '            [\'' . $behaviorKeys[0] . '\' => \'' . $behaviorValues[0] . '\']' . (($i < count($behaviors) - 1) ? ",\n" : "\n");
        } else {
            echo '            [' . "\n";
            foreach ($behaviorValues as $j => $behaviorValue) {
                if (is_string($behaviorValue)) {
                    echo '                \'' . $behaviorKeys[$j] . '\' => \'' . $behaviorValue . '\'' . (($j < count($behavior) - 1) ? ",\n" : "\n");
                } elseif (is_array($behaviorValue)) {
                    echo '                \'' . $behaviorKeys[$j] . '\' => [\'' . implode('\', \'', $behaviorValue) . '\']' . (($j < count($behavior) - 1) ? ",\n" : "\n");
                }
            }
            echo '            ]' . (($i < count($behaviors) - 1) ? ",\n" : "\n");
        }
    }
}
?>
        ];
    }
<?php /*}*/ ?>
}
