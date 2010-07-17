<?php
class Comb_Connector_Ssh implements Comb_ConnectorInterface
{
    /**
     * Runs the command on the servers belonging to the serverLists provided
     * @param string $command the command to run
     * @param array $serverLists the serverslists where to run the command on
     */
    public function execCommand($command, Array $serverLists)
    {
        $servers = $this->getServersForServerLists($serverLists);

        $waitFor = array();

        foreach($servers as $server) {
            if (false === $server->isConnected() && false === $server->connect()) {
                Comb_Registry::get('logger')->warning('Skipping server');
                continue;
            }
            
            $server->exec($command);
            $waitFor[] = $server;
        }

        while(false === $this->allDone($waitFor)) {
            usleep('100000');
        }
    }

    /**
     * Checks all the servers to see if they are done
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
                Comb_Registry::get('logger')->notice('To be implemented: rollback');
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
        static $testServers = array();
        if (empty($testServers)) {
            $obj = new Comb_Connector_SshConnection('server1', 'user', 'password');
            $obj2 = new Comb_Connector_SshConnection('server2', 'user', 'password');
            $testServers = array($obj, $obj2);            
        }
        return $testServers;
    }
}