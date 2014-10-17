<?php

namespace yii\kladovka\console;

use yii\console\Controller as YiiController,
    yii\helpers\Console,
    yii\helpers\VarDumper,
    Yii;


class Controller extends YiiController
{

    public function stdoutBegin($method)
    {
        return $this->stdout('BEGIN ' . $method . date(' c') . "\n", Console::BOLD, Console::FG_CYAN);
    }

    public function stdoutEnd($method)
    {
        return $this->stdout('END ' . $method . date(' c') . "\n", Console::FG_CYAN);
    }

    public function logError($message, $category = 'application')
    {
        if (!is_scalar($message)) {
            $message = VarDumper::dumpAsString($message);
        }
        Yii::error($message, $category);
    }
}
