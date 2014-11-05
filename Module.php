<?php

namespace yii\kladovka;

use yii\base\Module as YiiModule,
    yii\base\BootstrapInterface,
    yii\web\Application as YiiWebApplication,
    yii\console\Application as YiiConsoleApplication;


class Module extends YiiModule implements BootstrapInterface
{

    public function bootstrap($app)
    {
        if ($app instanceof YiiWebApplication) {
            $app->getI18n()->translations['kladovka'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@yii/kladovka/messages'
            ];
        } elseif ($app instanceof YiiConsoleApplication) {
            //$app->controllerMap[$this->id] = [];
        }
    }
}
