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

    protected function allDone(Array $waitFor=array())
    {
        $done = true;
        foreach($waitFor as $server) {
            if (false == $server->lastRequestFinnished()) {
                $done = false;
            }
        }
        return $done;
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
        $obj = new Comb_Connector_SshConnection('www1', 'testuser', 'testpasswd');
        $obj2 = new Comb_Connector_SshConnection('www2', 'testuser', 'testpasswd');
        return array($obj, $obj2);
    }
}