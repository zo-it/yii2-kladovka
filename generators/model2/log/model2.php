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
if ($secondModelClass == 'Log') {
    $use[] = 'yii\log\Logger';
}
if ($secondModelClass == 'User') {
    $use[] = 'yii\web\IdentityInterface';
}
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


<?php if ($secondModelClass == 'User') { ?>
class <?php echo $secondModelClass; ?> extends <?php echo $modelAlias; ?> implements IdentityInterface
<?php } else { ?>
class <?php echo $secondModelClass; ?> extends <?php echo $modelAlias; ?>

<?php } ?>
{
<?php if ($secondModelClass == 'Log') { ?>

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
<?php } ?>

    public static function find()
    {
        return Yii::createObject(<?php echo $secondModelClass; ?>Query::className(), [get_called_class()]);
    }
<?php if ($secondModelClass == 'Log') { ?>

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
<?php } elseif ($secondModelClass == 'User') { ?>

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
        parent::init();
<?php if (array_key_exists('softDelete', $behaviors)) { ?>
        $this->where($this->getAlias() . '.`deleted` = 0');
<?php } elseif (array_key_exists('timeDelete', $behaviors)) { ?>
        $this->where($this->getAlias() . '.`deleted_at` IS NULL');
<?php } ?>
    }
}
