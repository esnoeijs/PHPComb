<?php
class Comb_TaskRunner
{
    /**
     * Include and run the task specified
     * @param string $task the task to run
     */
    public function run($task='Default')
    {
        $logger = Comb_Registry::get('logger');
        $taskObject = $this->getNewTaskObject($task);

        $logger->info('[task: ' . $task . ']');
        $taskObject->run();
    }

    /**
     * Gets an instance of the task specified
     * @param string $task the task to execute
     * @return Comb_BaseTask the task object
     */
    protected function getNewTaskObject($task)
    {
        $className = 'Comb_Task_' . ucfirst($task);
        $connector = $this->getConnector();
        $task = new $className($connector);
        return $task;
    }

    /**
     * Returns an instance for the connector we'll be using.
     * @param string $type the type of connector to use for communication
     */
    protected function getConnector($type='ssh')
    {
        return Comb_ConnectorFactory::getConnector($type);
    }
}