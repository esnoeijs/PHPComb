<?php
abstract class Comb_BaseTask
{
    /**
     * @var Comb_ConnectorInterface
     */
    protected $connector;

    /**
     *
     * @var Comb_BaseTask
     */
    protected $creator;

    /**
     * The serverlists for this task
     * @var array
     */
    protected $serverLists;

    /**
     * Creates an instance of the task and sets the connector
     * @param Comb_ConnectorInterface $connector the connector we should use for
     *                                           this task
     */
    public function __construct(Comb_ConnectorInterface $connector, Comb_BaseTask $creator=null)
    {
        $this->setConnector($connector);
        
        if (!is_null($creator)) {
            $this->setCreator($creator);
        }
    }

    /**
     * Run should be implemented, calling the several tasks and commands
     * needed to do what we want to do.
     */
    abstract public function run();

    /**
     * Can be implemented, and should undo everything run() does
     */
    public function undo()
    {
    }

    /**
     * Sets the Comb_ConnectorInterface we should use
     * @param Comb_ConnectorInterface $connector the connector we're using for
     *                                           communication with our servers
     */
    public function setConnector(Comb_ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Returns the Comb_ConnectorInterface we're using
     * @return Comb_ConnectorInterface the connector we're currently using for
     *                                 communication with our servers
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * Sets the task that created us
     * @param Comb_BaseTask $creator the task that created us
     */
    public function setCreator(Comb_BaseTask $creator)
    {
        $this->creator = $creator;
    }

    /**
     * Returns the task that created us
     * @return Comb_BaseTask
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Checks if our creator is set
     * @return boolean true if creator was set, false if not
     */
    public function creatorIsSet()
    {
        return (isset($this->creator) && is_array($this->creator));
    }

    /**
     * Runs a task specified
     * @param string $taskName the task to execute
     */
    public function runTask($taskName)
    {
        $taskRunner = Comb_Registry::get('taskrunner');
        $taskRunner->run($taskName);
    }

    /**
     * Run the command on our servers
     * @param string $command the command to run on the remote server(s)
     * @return boolean true if successfull, false if something went wrong
     */
    protected function exec($command)
    {
        $serverLists = $this->getServerLists();
        if (is_null($serverLists)) {
            return false;
        }
        Comb_Registry::get('logger')
            ->info("Exec: '$command' [" . implode(', ', $serverLists) . ']');

        $connector = $this->getConnector();
        if ($connector->execCommand($command, $serverLists)) {
            return true;
        } else {
            throw new Comb_TaskExecutionException('Error while running command: ' . $command);
        }
    }

    /**
     * Run the command on our servers as sudo user
     * @param string $command the command to run on the remote server(s)
     * @return boolean true if successfull, false if not executed
     */
    protected function sudo($command)
    {
        $command = 'sudo -p __SUDOPASSWORD__ ' . $command;
        return $this->exec($command);
    }

    /**
     * Get the serverLists for this task. First, see if the task itself has
     * serverLists defined. If not, check if our creator has serverLists defined.
     * @return array containing serverlists to use, or null if none were found.
     */
    protected function getServerLists()
    {
        if (isset($this->serverLists) && is_array($this->serverLists)) {
            return $this->serverLists;
        } elseif (true === $this->creatorIsSet()) {
            return $this->getCreator()->getServerLists();
        } else {
            Comb_Registry::get('logger')
                ->error('No serverlist was set for task ' . __CLASS__ . '.');
            return null;
        }
    }
}