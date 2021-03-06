<?php
class Comb_Connector_Ssh implements Comb_ConnectorInterface
{
    /**
     * Container containing the connection information loaded from the configuration
     * @var Comb_Connector_SshConnectionContainer
     */
    protected $connectionContainer;

    /**
     * Boolean flag keeping track of wether or not we had any errors while
     * executing the last command.
     */
    protected $lastCommandHadErrors = false;

    /**
     * Create and initialize the connector instance
     */
    public function __construct()
    {
        $this->loadConnectionContainer();
    }

    /**
     * Create the ConnectionContainer and add the configuration information to it
     */
    protected function loadConnectionContainer()
    {
        $this->connectionContainer = new Comb_Connector_SshConnectionContainer();
        $this->connectionContainer->loadConnectionsFromConfiguration();
    }

    /**
     * The connectioncontainer containing the various connections
     * @return Comb_Connector_SshConnectionContainer
     */
    protected function getConnectionContainer()
    {
        return $this->connectionContainer;
    }

    /**
     * Runs the command on the servers belonging to the serverLists provided
     * @param string $command the command to run
     * @param array $serverLists the serverslists where to run the command on
     * @return boolean true when the command executed successfully, false if
     *                 there has been some kind of error (requiring a rollback)
     */
    public function execCommand($command, Array $serverLists)
    {
        $this->lastCommandHadErrors = false;
        $servers = $this->getServersForServerLists($serverLists);
        $waitFor = array();

        foreach($servers as $server) {
            if (false === $server->isConnected() && false === $server->connect()) {
                Comb_Registry::get('logger')->warning('Skipping server');
                continue;
            }

            if (false == Comb_Registry::get('commandlineparams')->optionSelected('dry-run')) {
                $server->exec($command);
                $waitFor[] = $server;
            } else {
                Comb_Registry::get('logger')->info($server->getHostname() . ' (dry-run)');
            }
        }

        while(false === $this->allDone($waitFor)) {
            usleep('100000');
        }

        return (false === $this->lastCommandHadErrors);
    }

    /**
     * Asks the connection objects to update their status, and then checks if
     * all the servers are done executing the command
     * @param array $waitFor the servers to wait for
     * @return boolean true if everything is done and we can continue, false if not
     */
    protected function allDone(Array &$waitFor=array())
    {
        for ($i=count($waitFor)-1; $i >= 0; $i--) {
            $server = &$waitFor[$i];
            $server->updateLastRequestStatus();
            $requestStatus = $server->getLastRequestStatus();
            
            if ($requestStatus == Comb_Connector_SshConnection::STATUS_SUCCESS ||
                $requestStatus == Comb_Connector_SshConnection::STATUS_READY) {
                unset($waitFor[$i]);
                sort($waitFor);
                continue;
            }

            if ($requestStatus == Comb_Connector_SshConnection::STATUS_ERROR) {
                $this->lastCommandHadErrors = true;
                continue;
            }
        }
        return (count($waitFor) == 0);
    }

    /**
     * Returns the servers that belong to the serverlists provided. If a server
     * belongs to multiple serverLists we don't return doubles.
     * @param array $serverLists Array containing Comb_Connector_SshConnection objects
     * @return array An array with Comb_Connector_SshConnection objects
     * 
     * @todo replace dummy content with config reader loader super awesome thingy
     */
    protected function getServersForServerLists(Array $serverLists)
    {
        return $this->connectionContainer->getConnectionsForConnectionLists($serverLists);
    }
}