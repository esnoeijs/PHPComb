<?php
class Comb_Application
{
    public function __construct()
    {
        $this->setupLogger();
        $this->displayApplicationVersion();
        $this->checkDependencies();
        $this->setupTaskRunner();
    }

    /**
     * Creates an instance of the Comb_Logger object and registers it in the
     * registry
     */
    protected function setupLogger()
    {
        $logger = new Comb_Logger();
        $logger->setUseColors();
        Comb_Registry::set('logger', $logger);
    }

    /**
     * Creates an instance of the Comb_TaskRunner and registers it in the
     * registry
     */
    protected function setupTaskRunner()
    {
        $taskRunner = new Comb_TaskRunner();
        Comb_Registry::set('taskrunner', $taskRunner);
    }

    /**
     * Say hi to our user and show the Comb version
     */
    protected function displayApplicationVersion()
    {
        Comb_Registry::get('logger')->info('PHP Comb - Version ' .
                COMB_VERSION . ' - http://phpcomb.net/', true);
    }

    /**
     * Check if all dependencies are here
     */
    protected function checkDependencies()
    {
        $logger = Comb_Registry::get('logger');
        if (!function_exists("ssh2_connect")) {
            $logger->critical(
                'SSH2 extension is not installed. See ' .
                'http://php.net/manual/en/book.ssh2.php for more information.');
            exit(1);
        } else {
            $logger->debug('SSH2 extension OK');
        }
    }

    /**
     * Run the default task
     */
    public function run()
    {
        $taskRunner = Comb_Registry::get('taskrunner');
        $taskRunner->run();
    }
}