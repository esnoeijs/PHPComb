<?php
class Comb_ProjectConfig
{
    /**
     * The location of our project configfile
     * @var string
     */
    protected $configLocation;

    /**
     * The configuration values
     * @var array
     */
    protected $configVars = array();

    /**
     * Creates a new instance of the class and loads the configuration
     * @param string $configLocation the location of the configfile we're loading
     */
    public function __construct($configLocation)
    {
        Comb_Registry::get('logger')->info('Project configuration: ' . $configLocation);
        $this->setConfigLocation($configLocation);
        $this->checkConfigExists();
        $this->loadConfiguration();
        Comb_Registry::get('logger')->debug('Project configuration successfully loaded');
    }

    /**
     * Checks if the provided configfile exists
     */
    protected function checkConfigExists()
    {
        $configLocation = $this->getConfigLocation();
        if (!file_exists($configLocation)) {
            Comb_Registry::get('logger')->critical('Can\'t find the project configfile at ' . $configLocation);
            exit(1);
        }
    }

    /**
     * Load the actual configuration from the configfile and save the settings
     */
    protected function loadConfiguration()
    {
        require_once $this->getConfigLocation();
        $this->setConfigurationVars($config);
    }

    /**
     * Set the configurationvars to the values in the array provided
     * @param array $configVars the configuration variables
     */
    protected function setConfigurationVars(Array $configVars=array())
    {
        $this->clearConfiguration();
        foreach($configVars as $key => $value) {
            $this->addConfigVar($key, $value);
        }
    }

    /**
     * Clear the configuration vars
     */
    protected function clearConfiguration()
    {
        $this->configVars = array();
    }

    /**
     * Adds a configuration variable to the configVars array
     * @param string $key
     * @param mixed $value
     */
    protected function addConfigVar($key, $value)
    {
        $this->configVars[(string)$key] = $value;
    }

    /**
     * Returns the configuration variables
     * @return array the variables
     */
    protected function getConfigVars()
    {
        return $this->configVars;
    }

    /**
     * Returns a value from the loaded configuration
     * @param string $key the variable key
     * @return mixed|null the value from the configuration, or null if not found
     */
    public function getConfigVar($key)
    {
        $vars = $this->getConfigVars();
        if (array_key_exists($key, $vars)) {
            return $vars[$key];
        } else {
            return null;
        }
    }

    /**
     * Sets the config location
     * @param string $configLocation the location of our project config
     */
    protected function setConfigLocation($configLocation)
    {
        $this->configLocation = $configLocation;
    }

    /**
     * Returns the config location
     * @return string the location of our project config
     */
    public function getConfigLocation()
    {
        return $this->configLocation;
    }

    /**
     * Allows user to use $projectConfig->setting to fetch a projectconfig setting
     * @param string $name the variable we're trying to get
     * @return mixed|null the value from the configuration, or null if not found
     */
    public function __get($name) {
        return $this->getConfigVar($name);
    }
}