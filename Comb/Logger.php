<?php
class Comb_Logger
{
    const LOGLEVEL_DEBUG = 1;
    const LOGLEVEL_INFO = 2;
    const LOGLEVEL_NOTICE = 3;
    const LOGLEVEL_WARNING = 4;
    const LOGLEVEL_ERROR = 5;
    const LOGLEVEL_CRITICAL = 6;

    /**
     * Wether or not to use colors in our output
     * @var boolean
     */
    protected $useColors = false;

    /**
     * Write the log message to the right channel
     * @param string $msg the message to display
     * @param int $level the loglevel
     */
    protected function log($msg, $level, $color=null)
    {
        if (true == $this->useColors && !is_null($color)) {
            $msg = $color . $msg . "\033[0;37m";
        }
        echo $msg . PHP_EOL;
    }

    /**
     * Debug message. Message won't be displayed during normal usage.
     * @param string $msg the message to display
     */
    public function debug($msg)
    {
        if (true == Comb_Registry::get('commandlineparams')->optionSelected('verbose')) {
            $msg = 'debug > ' . $msg;
            $this->log($msg, self::LOGLEVEL_DEBUG, "\033[0;36m");
        }
    }

    /**
     * Information message, simply to inform the user about what is going in.
     * @param string $msg     the message to display
     * @param boolean $bright displays the message bright (if useColors is set)
     */
    public function info($msg, $bright=false)
    {
        if (false == Comb_Registry::get('commandlineparams')->optionSelected('quiet')) {
            $this->log($msg, self::LOGLEVEL_INFO, ($bright ? "\033[1;37m" : null));
        }
    }

    /**
     * Normal behaviour but there's something wrong that might cause bigger
     * problems in the future (most probably: user is applying some bad
     * practices). Inform the user about the dangers and on how to fix this.
     * @param string $msg the message to display
     */
    public function notice($msg)
    {
        if (false == Comb_Registry::get('commandlineparams')->optionSelected('quiet')) {
            $msg = '[notice] ' . $msg;
            $this->log($msg, self::LOGLEVEL_NOTICE, "\033[0;33m");
        }
    }

    /**
     * Something went wrong or is likely to go wrong in the future, but for now
     * the application can continue to run without any serious consequences.
     * @param string $msg the message to display
     */
    public function warning($msg)
    {
        if (false == Comb_Registry::get('commandlineparams')->optionSelected('quiet')) {
            $msg = '[WARNING] ' . $msg;
            $this->log($msg, self::LOGLEVEL_WARNING, "\033[0;35m");
        }
    }

    /**
     * An error occurred! The application will continue, but something went
     * terribly wrong and the final result will not be as expected
     * @param string $msg the message to display
     */
    public function error($msg)
    {
        $msg = '[ERROR] ' . $msg;
        $this->log($msg, self::LOGLEVEL_ERROR, "\033[1;31m");
    }

    /**
     * Aaaah! an error occurred and even worse: all hope is lost. The only thing
     * we can do (after calling this method off course) is call exit(1), go home
     * and have a beer.
     * @param string $msg the message to display
     */
    public function critical($msg)
    {
        $msg = '[CRITICAL] ' . $msg;
        $this->log($msg, self::LOGLEVEL_CRITICAL, "\033[1;41;37m");
    }

    /**
     * Sets wether or not we're using colors in our output
     * @param boolean $useColors true to use colors, false to not use colors
     */
    public function setUseColors($useColors=true)
    {
        $this->useColors = (bool)$useColors;
    }
}