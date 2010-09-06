<?php
class Comb_TaskRunner
{
    /**
     * @var Comb_TaskLog our tasklog
     */
    protected $taskLog;

    /**
     * Include and run the task specified
     * @param string $task the task to run
     */
    public function run($task='Default')
    {
        $logger = Comb_Registry::get('logger');
        $taskObject = $this->getNewTaskObject($task);
        $this->addTaskToLog($taskObject);

        $logger->info('[task: ' . $task . ']');

        try {
            $taskObject->run();
        } catch(Comb_TaskExecutionException $e) {
            $this->rollback();   
        }
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
     * @return Comb_Connector_Ssh
     */
    protected function getConnector()
    {
        return new Comb_Connector_Ssh();
    }

    /**
     * Add task to our tasklog
     * @param Comb_BaseTask $task the task to add to our log
     */
    protected function addTaskToLog(Comb_BaseTask $task)
    {
        $log = $this->getTasklog();
        $log->addTask($task);
    }

    /**
     * Returns our instance of the task log
     * @return Comb_TaskLog the tasklog
     */
    protected function getTasklog()
    {
        if (!isset($this->taskLog)) {
            $this->taskLog = new Comb_TaskLog();
        }
        return $this->taskLog;
    }

    /**
     * Rollback all tasks we've executed so far
     */
    protected function rollback()
    {
        $logger = Comb_Registry::get('logger');
        $logger->warning('Rolling back');

        while($task = $this->taskLog->popTask()) {
            try {
                $task->undo();
            } catch(Comb_TaskExecutionException $task) {
                $logger->error('Rollback failed: ' . $task->getMessage());
            }
        }
    }
}