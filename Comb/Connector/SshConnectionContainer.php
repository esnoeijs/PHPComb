<?php
class Comb_Connector_SshConnectionContainer
{
    /**
     * Array containing our servers
     * @var array with Comb_Connector_SshConnection objects
     */
    protected $servers = array();

    /**
     * Array with serverlists, pointing to the servers that belong to the list
     * @var array
     */
    protected $serverLists = array();

    /**
     * Loads the connections from the project configuration file
     */
    public function loadConnectionsFromConfiguration()
    {
        $config = Comb_Registry::get('projectconfig');
        $serverConfig = $config->servers;

        foreach($serverConfig as $listName => $listConfig)
        {
            $this->loadServersForServerlist($listName, $listConfig);
        }
    }

    /**
     * Loads the connections from the project configuration for the serverlist provided
     * @param string $listName the list name we're currently loading
     * @param array $listConfig the configuration from the project configuration file
     */
    protected function loadServersForServerlist($listName, $listConfig)
    {
        $listUsername = null;
        if (array_key_exists('username', $listConfig)) {
            $listUsername = $listConfig['username'];
        }

        $listPassword = null;
        if (array_key_exists('password', $listConfig)) {
            $listPassword = $listConfig['password'];
        }

        foreach($listConfig['serverlist'] as $serverConfig) {
            $connection = $this->createConnectionInstance($serverConfig, $listUsername, $listPassword);

            $uid = md5($connection->getHostname() . ':' . $connection->getPort());
            if (array_key_exists($uid, $this->servers)) {
                continue;
            }

            $this->servers[$uid] = $connection;
            $this->serverLists[$listName][$uid] = &$this->servers[$uid];
        }
    }

    /**
     * Creates a server connection instance
     * @param array $serverConfig the server configuration
     * @param string $listUsername the username for all the servers in this list
     * @param string $listPassword the password for all the servers in this list
     * @return Comb_Connector_SshConnection the connection
     */
    protected function createConnectionInstance($serverConfig, $listUsername=null, $listPassword=null)
    {
        $port = 22;
        if (is_string($serverConfig)) {
            $hostname = $serverConfig;
            $username = $listUsername;
            $password = $listPassword;
        } else {
            $hostname = $serverConfig['host'];
            $username = $serverConfig['username'];
            $password = $serverConfig['password'];

            if (array_key_exists('port', $serverConfig)) {
                $port = $serverConfig['port'];
            }
        }

        if (is_null($port) && strstr($hostname, ':')) {
            $port = substr($hostname, strrpos($hostname));
        }
        return new Comb_Connector_SshConnection($hostname, $username, $password, $port);
    }

    /**
     * Returns connections for the serverlists provided
     * @param array $serverLists the serverlists we want to get the connections for
     * @return array containing Comb_Connector_SshConnection objects
     */
    public function getConnectionsForConnectionLists($serverLists)
    {
        $connections = array();
        foreach ($serverLists as $listName) {
            foreach ($this->serverLists[$listName] as $serverUid => $conn) {
                if (!in_array($serverUid, $connections)) {
                    $connections[$serverUid] = $conn;
                }
            }
        }
        return $connections;
    }
}