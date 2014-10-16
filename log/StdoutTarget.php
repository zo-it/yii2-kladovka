<?php

namespace yii\kladovka\log;

use yii\log\Target;


class StdoutTarget extends Target
{

    public function export()
    {
        $fh = fopen('php://stdout', 'w');
        if ($fh) {
            foreach ($this->messages as $message) {
                fwrite($fh, $this->formatMessage($message) . "\n");
            }
            fclose($fh);
        }
    }
}
