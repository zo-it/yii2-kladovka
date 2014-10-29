<?php

namespace yii\kladovka;

use yii\base\Module as YiiModule,
    yii\base\BootstrapInterface,
    yii\web\Application as YiiWebApplication;


class Module extends YiiModule implements BootstrapInterface
{

    public function bootstrap($app)
    {
        $app->getI18n()->translations['kladovka'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@yii/kladovka/messages'
        ];
        if ($app instanceof YiiWebApplication) {

        }
    }
}
