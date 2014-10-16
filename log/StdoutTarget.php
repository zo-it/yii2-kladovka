<?php

namespace yii\kladovka\log;

use yii\log\Target,
    yii\log\Logger,
    yii\helpers\Console;


class StdoutTarget extends Target
{

    private $_stdoutIsTerminal = false;

    private $_stdoutSupportsColors = false;

    private $_stderrIsTerminal = false;

    private $_stderrSupportsColors = false;

    private $_levelFormatMap = [
        Logger::LEVEL_ERROR => [Console::BOLD, Console::FG_RED],
        Logger::LEVEL_WARNING => [Console::BOLD, Console::FG_YELLOW],
        Logger::LEVEL_INFO => [],
        Logger::LEVEL_TRACE => [Console::FG_CYAN],
        Logger::LEVEL_PROFILE => [Console::FG_PURPLE],
        Logger::LEVEL_PROFILE_BEGIN => [Console::FG_PURPLE],
        Logger::LEVEL_PROFILE_END => [Console::FG_PURPLE]
    ];

    public function init()
    {
        parent::init();
        $this->_stdoutIsTerminal = posix_isatty(\STDOUT);
        $this->_stdoutSupportsColors = Console::streamSupportsAnsiColors(\STDOUT);
        $this->_stderrIsTerminal = posix_isatty(\STDERR);
        $this->_stderrSupportsColors = Console::streamSupportsAnsiColors(\STDERR);
    }

    public function export()
    {
        foreach ($this->messages as $message) {
            $string = $this->formatMessage($message) . "\n";
            $level = $message[1];
            $format = array_key_exists($level, $this->_levelFormatMap) ? $this->_levelFormatMap[$level] : [];
            if ($this->_stdoutIsTerminal && $this->_stdoutSupportsColors) {
                Console::stdout(Console::ansiFormat($string, $format));
            } else {
                Console::stdout($string);
            }
            if (($level == Logger::LEVEL_ERROR || $level == Logger::LEVEL_WARNING) && !$this->_stdoutIsTerminal && $this->_stderrIsTerminal) {
                if ($this->_stderrSupportsColors) {
                    Console::stderr(Console::ansiFormat($string, $format));
                } else {
                    Console::stderr($string);
                }
            }
        }
    }
}
