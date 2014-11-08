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
if ($secondModelClass == 'User') {
    $use[] = 'yii\web\IdentityInterface';
}
$use[] = Yii::$app->hasModule('mozayka') ? 'yii\mozayka\db\ActiveQuery' : 'yii\kladovka\db\ActiveQuery';
$use[] = 'Yii';

$behaviors = [];
foreach ($generator->getTableSchema()->columns as $columnSchema) {
    if (in_array($columnSchema->type, ['datetime', 'date', 'time'])) {
        if (in_array($columnSchema->name, ['created_at', 'updated_at', 'timestamp'])) {
            $behaviors['timestamp'] = 'yii\kladovka\behaviors\TimestampBehavior';
            continue;
        } elseif ($columnSchema->allowNull && ($columnSchema->name == 'deleted_at')) {
            $behaviors['timeDelete'] = 'yii\kladovka\behaviors\TimeDeleteBehavior';
            continue;
        } elseif (!array_key_exists('datetime', $behaviors)) {
            $behaviors['datetime'] = [
                'class' => 'yii\kladovka\behaviors\DatetimeBehavior',
                'attributes' => [$columnSchema->name]
            ];
        } else {
            $behaviors['datetime']['attributes'][] = $columnSchema->name;
        }
    } elseif (($columnSchema->type == 'smallint') && ($columnSchema->size == 1) && $columnSchema->unsigned && !$columnSchema->allowNull && ($columnSchema->name == 'deleted')) {
        $behaviors['softDelete'] = 'yii\kladovka\behaviors\SoftDeleteBehavior';
        continue;
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


<?php if ($secondModelClass == 'User') { ?>
class <?php echo $secondModelClass; ?> extends <?php echo $modelAlias; ?> implements IdentityInterface
<?php } else { ?>
class <?php echo $secondModelClass; ?> extends <?php echo $modelAlias; ?>

<?php } ?>
{

    public static function find()
    {
        return Yii::createObject(<?php echo $secondModelClass; ?>Query::className(), [get_called_class()]);
    }
<?php if ($secondModelClass == 'User') { ?>

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($accessToken, $type = null)
    {
        return static::findOne(['access_token' => $accessToken]);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key == $authKey;
    }

    public function validatePassword($password)
    {
        return $this->password == $password;
    }
<?php } ?>
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
<?php if (array_key_exists('softDelete', $behaviors)) { ?>
        $this->where($this->getAlias() . '.`deleted` = 0');
<?php } elseif (array_key_exists('timeDelete', $behaviors)) { ?>
        $this->where($this->getAlias() . '.`deleted_at` IS NULL');
<?php } ?>
    }
}
