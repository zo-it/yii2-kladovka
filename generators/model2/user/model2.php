<?php
/**
 * @var yii\web\View $this
 * @var yii\kladovka\generators\model2\Generator $generator
 */

$use = $generator->prepareUse(['yii\web\IdentityInterface']);
$behaviors = $generator->prepareBehaviors();

echo "<?php\n";
?>

namespace <?php echo $generator->getSecondModelNamespace(); ?>;
<?php echo $generator->renderUse($use); ?>


class <?php echo $generator->getSecondModelName(); ?> extends <?php echo $generator->getModelAlias(); ?> implements IdentityInterface
{

    /**
     * @return <?php echo $generator->getSecondModelName(); ?>Query
     */
    public static function find()
    {
        return Yii::createObject(<?php echo $generator->getSecondModelName(); ?>Query::className(), [get_called_class()]);
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
