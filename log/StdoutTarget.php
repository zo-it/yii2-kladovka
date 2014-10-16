<?php

namespace yii\kladovka\log;

use yii\log\Target,
    yii\log\Logger,
    yii\helpers\Console;


class StdoutTarget extends Target
{

    private $_stdoutIsTerminal = true;

    private $_stdoutSupportsAnsiColors = false;

    private $_stderrIsTerminal = true;

    private $_stderrSupportsAnsiColors = false;

    private $_levelAnsiColorMap = [
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
        $this->_stdoutSupportsAnsiColors = Console::streamSupportsAnsiColors(\STDOUT);
        $this->_stderrIsTerminal = posix_isatty(\STDERR);
        $this->_stderrSupportsAnsiColors = Console::streamSupportsAnsiColors(\STDERR);
    }

    public function export()
    {
        foreach ($this->messages as $message) {
            $string = $this->formatMessage($message) . "\n";
            $level = $message[1];
            $ansiColor = array_key_exists($level, $this->_levelAnsiFormatMap) ? $this->_levelAnsiFormatMap[$level] : [];
            if ($this->_stdoutIsTerminal) {
                if ($this->_stdoutSupportsAnsiColors) {
                    Console::stdout(Console::ansiFormat($string, $ansiColor));
                } else {
                    Console::stdout($string);
                }
            } else {
                Console::stdout($string);
                if ($this->_stderrIsTerminal && ($level == Logger::LEVEL_ERROR || $level == Logger::LEVEL_WARNING)) {
                    if ($this->_stderrSupportsAnsiColors) {
                        Console::stderr(Console::ansiFormat($string, $ansiColor));
                    } else {
                        Console::stderr($string);
                    }
                }
            }
        }
    }
}
