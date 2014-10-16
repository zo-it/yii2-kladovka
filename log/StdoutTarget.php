<?php

namespace yii\kladovka\log;

use yii\log\Target,
    yii\helpers\Console;


class StdoutTarget extends Target
{

    public function export()
    {
        foreach ($this->messages as $message) {
            Console::stdout($this->formatMessage($message) . "\n");
        }
    }
}
