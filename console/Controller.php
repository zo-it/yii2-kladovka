<?php

namespace yii\kladovka\console;

use yii\console\Controller as YiiController,
    yii\helpers\Console;


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
}
