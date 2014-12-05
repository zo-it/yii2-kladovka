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
$use[] = 'yii\web\IdentityInterface';
$use[] = Yii::$app->hasModule('mozayka') ? 'yii\mozayka\db\ActiveQuery' : 'yii\kladovka\db\ActiveQuery';
$use[] = 'Yii';

$behaviors = $generator->prepareBehaviors();

echo "<?php\n";
?>

namespace <?php echo $secondModelNamespace; ?>;
<?php if ($use) { ?>

use <?php echo implode(",\n    ", $use); ?>;
<?php } ?>


class <?php echo $secondModelClass; ?> extends <?php echo $modelAlias; ?> implements IdentityInterface
{

    public static function find()
    {
        return Yii::createObject(<?php echo $secondModelClass; ?>Query::className(), [get_called_class()]);
    }

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
