<?php

namespace yii\kladovka\log;

use yii\log\Target,
    yii\log\Logger,
    yii\helpers\Console;


class StdoutTarget extends Target
{

    private $_stdoutSupportsAnsiColors = false;

    public function init()
    {
        parent::init();
        $this->_stdoutSupportsAnsiColors = Console::streamSupportsAnsiColors(\STDOUT);
    }

    public function export()
    {
        static $levelFormatMap = [
            Logger::LEVEL_ERROR => [Console::FG_RED],
            Logger::LEVEL_WARNING => [Console::FG_YELLOW],
            Logger::LEVEL_INFO => [],
            Logger::LEVEL_TRACE => [Console::FG_CYAN],
            Logger::LEVEL_PROFILE => [Console::FG_PURPLE],
            Logger::LEVEL_PROFILE_BEGIN => [Console::FG_PURPLE],
            Logger::LEVEL_PROFILE_END => [Console::FG_PURPLE]
        ];
        foreach ($this->messages as $message) {
            $string = $this->formatMessage($message) . "\n";
            if ($this->_stdoutSupportsAnsiColors && array_key_exists($message[1], $levelFormatMap)) {
                $string = Console::ansiFormat($string, $levelFormatMap[$message[1]]);
            }
            Console::stdout($string);
        }
    }
}
