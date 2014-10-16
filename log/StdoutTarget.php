<?php

namespace yii\kladovka\log;

use yii\log\Target,
    yii\helpers\Console,
    yii\log\Logger;


class StdoutTarget extends Target
{

    private $_stdoutIsTerminal = true;

    private $_stdoutSupportsAnsiColors = false;

    private $_stderrIsTerminal = true;

    private $_stderrSupportsAnsiColors = false;

    private $_errorAnsiColor = [Console::BOLD, Console::FG_RED];

    private $_warningAnsiColor = [Console::BOLD, Console::FG_YELLOW];

    private $_infoAnsiColor = [];

    private $_traceAnsiColor = [Console::FG_CYAN];

    private $_profileAnsiColor = [Console::FG_PURPLE];

    private $_profileBeginAnsiColor = [Console::FG_PURPLE];

    private $_profileEndAnsiColor = [Console::FG_PURPLE];

    private $_levelAnsiColorMap = [];

    public function init()
    {
        parent::init();
        $this->_stdoutIsTerminal = posix_isatty(\STDOUT);
        $this->_stdoutSupportsAnsiColors = Console::streamSupportsAnsiColors(\STDOUT);
        $this->_stderrIsTerminal = posix_isatty(\STDERR);
        $this->_stderrSupportsAnsiColors = Console::streamSupportsAnsiColors(\STDERR);
        $this->_levelAnsiColorMap = [
            Logger::LEVEL_ERROR => $this->_errorAnsiColor,
            Logger::LEVEL_WARNING => $this->_warningAnsiColor,
            Logger::LEVEL_INFO => $this->_infoAnsiColor,
            Logger::LEVEL_TRACE => $this->_traceAnsiColor,
            Logger::LEVEL_PROFILE => $this->_profileAnsiColor,
            Logger::LEVEL_PROFILE_BEGIN => $this->_profileBeginAnsiColor,
            Logger::LEVEL_PROFILE_END => $this->_profileEndAnsiColor
        ];
    }

    public function export()
    {
        foreach ($this->messages as $message) {
            $string = $this->formatMessage($message) . "\n";
            $level = $message[1];
            $ansiColor = array_key_exists($level, $this->_levelAnsiColorMap) ? $this->_levelAnsiColorMap[$level] : [];
            if ($this->_stdoutIsTerminal) {
                if ($this->_stdoutSupportsAnsiColors && $ansiColor) {
                    Console::stdout(Console::ansiFormat($string, $ansiColor));
                } else {
                    Console::stdout($string);
                }
            } else {
                Console::stdout($string);
                if ($this->_stderrIsTerminal && ($level == Logger::LEVEL_ERROR || $level == Logger::LEVEL_WARNING)) {
                    if ($this->_stderrSupportsAnsiColors && $ansiColor) {
                        Console::stderr(Console::ansiFormat($string, $ansiColor));
                    } else {
                        Console::stderr($string);
                    }
                }
            }
        }
    }
}
