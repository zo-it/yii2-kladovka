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

    private $_levelAnsiColorMap = [];

    public $errorAnsiColor = [Console::BOLD, Console::FG_RED];

    public $warningAnsiColor = [Console::BOLD, Console::FG_YELLOW];

    public $infoAnsiColor = [];

    public $traceAnsiColor = [Console::FG_CYAN];

    public $profileAnsiColor = [Console::FG_PURPLE];

    public $profileBeginAnsiColor = [Console::FG_PURPLE];

    public $profileEndAnsiColor = [Console::FG_PURPLE];

    public $defaultAnsiColor = [];

    public function init()
    {
        $this->_stdoutIsTerminal = posix_isatty(\STDOUT);
        $this->_stdoutSupportsAnsiColors = Console::streamSupportsAnsiColors(\STDOUT);
        $this->_stderrIsTerminal = posix_isatty(\STDERR);
        $this->_stderrSupportsAnsiColors = Console::streamSupportsAnsiColors(\STDERR);
        $this->_levelAnsiColorMap = [
            Logger::LEVEL_ERROR => $this->errorAnsiColor,
            Logger::LEVEL_WARNING => $this->warningAnsiColor,
            Logger::LEVEL_INFO => $this->infoAnsiColor,
            Logger::LEVEL_TRACE => $this->traceAnsiColor,
            Logger::LEVEL_PROFILE => $this->profileAnsiColor,
            Logger::LEVEL_PROFILE_BEGIN => $this->profileBeginAnsiColor,
            Logger::LEVEL_PROFILE_END => $this->profileEndAnsiColor
        ];
        parent::init();
    }

    public function export()
    {
        foreach ($this->messages as $message) {
            $string = $this->formatMessage($message) . "\n";
            $level = $message[1];
            if (array_key_exists($level, $this->_levelAnsiColorMap)) {
                $ansiColor = $this->_levelAnsiColorMap[$level];
            } else {
                $ansiColor = $this->defaultAnsiColor;
            }
            if ($this->_stdoutIsTerminal) {
                if ($this->_stdoutSupportsAnsiColors && $ansiColor) {
                    Console::stdout(Console::ansiFormat($string, $ansiColor));
                } else {
                    Console::stdout($string);
                }
            } else {
                Console::stdout($string);
                if ($this->_stderrIsTerminal && (($level == Logger::LEVEL_ERROR) || ($level == Logger::LEVEL_WARNING))) {
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
