<?php
class Comb_Application
{
    /**
     * Creates and initializes the application instance.
     */
    public function __construct()
    {
        $this->loadCommandlineParams();
        $this->setupLogger();
        $this->displayApplicationVersion();
        $this->checkDependencies();
        $this->setupProjectConfig();
        $this->setupTaskRunner();
    }

    /**
     * Makes sure the commandline parameters are read, checked and stored
     */
    protected function loadCommandlineParams()
    {
        $commandlineParams = new Comb_CommandlineParams();

        if (!$commandlineParams->loadParams($_SERVER['argv'])) {
            exit(1);
        }

        if ($commandlineParams->optionSelected('help')) {
            echo $commandlineParams->getSyntaxExplain();
            exit(0);
        }
        
        Comb_Registry::set('commandlineparams', $commandlineParams);
    }

    /**
     * Creates an instance of the Comb_Logger object and registers it in the
     * registry
     */
    protected function setupLogger()
    {
        $logger = new Comb_Logger();
        if (Comb_Registry::get('commandlineparams')->optionSelected('color')) {
            $logger->setUseColors();
        }
        Comb_Registry::set('logger', $logger);
    }

    /**
     * Say hi to our user and show the Comb version
     */
    protected function displayApplicationVersion()
    {
        Comb_Registry::get('logger')->info('PHPComb - Version ' .
                COMB_VERSION . ' - http://phpcomb.net/');
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
     * Creates an instance of the Comb_ProjectConfig class and registers it in
     * the registry
     */
    protected function setupProjectConfig()
    {
        $config = new Comb_ProjectConfig(COMB_PROJECT_ROOT . 'comb/comb.php');
        Comb_Registry::set('projectconfig', $config);
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
     * Run the default task
     */
    public function run()
    {
        $taskRunner = Comb_Registry::get('taskrunner');
        $taskRunner->run();
    }
}