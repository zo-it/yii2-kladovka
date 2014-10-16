<?php

namespace yii\kladovka\console;

use yii\console\Controller as ConsoleController,
    yii\helpers\Console;


class Controller extends ConsoleController
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
