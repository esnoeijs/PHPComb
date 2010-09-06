<?php
class Comb_TaskLog
{
    /**
     * Array containing the tasks we've executed
     * @var array with Comb_BaseTask instances
     */
    protected $tasks = array();

    /**
     * Adds a task to the tasklog
     * @param Comb_BaseTask $task the task to remember
     */
    public function addTask(Comb_BaseTask $task)
    {
        $this->tasks[] = $task;
    }

    /**
     * Pops the last added element out of the log
     * @return Comb_BaseTask the task that was added last, or null of no tasks left
     */
    public function popTask()
    {
        return array_pop($this->tasks);
    }
}