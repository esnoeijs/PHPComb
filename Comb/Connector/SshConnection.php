<?php
class Comb_Connector_SshConnection
{
    const STATUS_READY      = 1;
    const STATUS_BUSY       = 2;
    const STATUS_SUCCESS    = 3;
    const STATUS_ERROR      = 4;

    /**
     * The host we're connecting to
     * @var string
     */
    protected $hostname;

    /**
     * The destination port we're connecting to
     * @var int
     */
    protected $port;

    /**
     * The username we're using to login
     * @var string
     */
    protected $username;

    /**
     * The password we're using to login (if any)
     * @var string
     */
    protected $password;

    /**
     * The SSH2 resource for our connection
     * @var resource
     */
    protected $resource;

    /**
     * String containing the last response contents
     * @var string
     */
    protected $lastResponse = '';

    /**
     * The error we received from the remote server caused by the last request
     * @var string
     */
    protected $lastResponseError = '';

    /**
     * The stdio stream
     * @var stream
     */
    protected $stream;

    /**
     * The stderr stream
     * @var stream
     */
    protected $streamStdError;

    /**
     * The request status (see class constants)
     * @var int the current request status
     */
    protected $requestStatus;

    /**
     * Creates a new SSH connection object
     * @param string $hostname the hostname of the SSH server
     * @param string $username the user we're using to connect with
     * @param string $password the password we're using to establish the connection
     * @param int $port the port to connect to
     */
    public function __construct($hostname, $username, $password=null, $port=22)
    {
        $this->setHostname($hostname);
        $this->setPort($port);
        $this->setUsername($username);

        if (!is_null($password)) {
            $this->setPassword($password);
        }
    }

    /**
     * Set the hostname to connect to
     * @param string $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = (string)$hostname;
    }

    /**
     * Returns the hostname for this connection
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Sets the port on the remote host to connect to
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = (int)$port;
    }

    /**
     * Returns the port we're connecting to on the remote host
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }


    /**
     * Set the username to connect with
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = (string)$username;
    }

    /**
     * Returns the username for this connection
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the password to use when connecting
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Returns the password
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the SSH2-resource
     * @param Resource $resource 
     */
    protected function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns the SSH2-resource
     * @return Resource, or null if not connected
     */
    protected function getResource()
    {
        if (isset($this->resource)) {
            return $this->resource;
        } else {
            return null;
        }
    }

    /**
     * Establishes the connection using the credentials supplied
     * @return boolean true on success, false on failure
     */
    public function connect()
    {
        if (true === $this->isConnected()) {
            return true;
        }

        $hostname = $this->getHostname();
        $port = $this->getPort();

        $connection = @ssh2_connect($hostname, $port);
        if (false === $connection) {
            Comb_Registry::get('logger')->error('Could not connect to host ' . $this->getHostname() . ' on port ' . $this->getPort());
            return false;
        } else {
            Comb_Registry::get('logger')->debug('Connected: ' . $hostname . ':' . $port);
        }

        if (false === @ssh2_auth_password($connection, $this->getUsername(), $this->getPassword())) {
            Comb_Registry::get('logger')->error('Could not connect to ' . $this->getUsername() . '@' . $this->getHostname() . ':' . $this->getPort() . ' - invalid credentials');
            return false;
        }

        $this->setResource($connection);
        Comb_Registry::get('logger')->debug('Logged in: ' . $this->getUsername() . '@' . $hostname . ':' . $port);
    }

    /**
     * Closes the connection
     */
    public function disconnect()
    {
        // the SSH2-extension doesn't support disconnecting before version 2.1.1
        if (function_exists('ssh2_disconnect')) {
            ssh2_disconnect($this->resource);
        }
        unset($this->resource);
    }

    /**
     * Returns wether or not this connection is currently open
     * @return boolean true if connected, false if not
     */
    public function isConnected()
    {
        return !is_null($this->getResource());
    }

    /**
     * Runs a command on the remote server
     * @param string $command the command to run
     */
    public function exec($command)
    {
        if ($this->getLastRequestStatus() == self::STATUS_BUSY) {
            Comb_Registry::get('logger')->warning('a command was executed on ' .
                    $this->getHostname() . ' while the last command wasn\'t ' .
                    'finnished yet. Waiting for the connection to clear before ' .
                    'executing the next command...');

            while($this->getLastRequestStatus() == self::STATUS_BUSY) {
                sleep(1);
            }
        }

        $this->requestStatus = self::STATUS_BUSY;
        $this->lastResponse = '';
        $this->lastResponseError = '';
        $this->stream = ssh2_exec($this->getResource(), $command);
        $this->streamStdError = ssh2_fetch_stream($this->stream, SSH2_STREAM_STDERR);
    }

    /**
     * Checks the various streams and sets the current request status based
     * on this information
     */
    public function updateLastRequestStatus()
    {
        $resp = stream_get_contents($this->stream);
        if (!empty($resp)) {
            $this->lastResponse .= $resp;
        }

        $errorMessage = stream_get_contents($this->streamStdError);
        if (!empty($errorMessage)) {
            $this->_handleErrorStream($errorMessage);
            return;
            
        }

        if ($this->getLastRequestStatus() == self::STATUS_BUSY && feof($this->stream)) {
            Comb_Registry::get('logger')->info($this->getHostname() . ': Done');
            Comb_Registry::get('logger')->debug('Response: '. $this->lastResponse);
            $this->requestStatus = self::STATUS_SUCCESS;
            return;
        }

        $this->requestStatus = self::STATUS_BUSY;
    }

    /**
     * StdErr isn't empty, so we know something bad happened. Set the status
     * to error and set the last errormessage so the user can figure out what
     * went wrong.
     * @param string $errorMessage the part of the errormessage we got so far.
     *                             There might be some more so let's find out.
     */
    protected function _handleErrorStream($errorMessage)
    {
        $this->requestStatus = self::STATUS_ERROR;

        while(!feof($this->streamStdError)) {
            $errorMessage .= stream_get_contents($this->streamStdError);
            usleep(10000);
        }
        $this->lastResponseError = $errorMessage;

        Comb_Registry::get('logger')->error('on ' . $this->getHostname() . ' the ' .
                'following error occurred: ' . $errorMessage);
    }

    /**
     * Returns the status of the last request
     * @return int the request status
     */
    public function getLastRequestStatus()
    {
        return $this->requestStatus;
    }
}