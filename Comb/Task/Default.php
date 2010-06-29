<?php
class Comb_Task_Default extends Comb_BaseTask
{
    protected $serverLists = array('web');

    public function run()
    {
        $this->exec('ls -la > /tmp/testje.txt');
    }

    public function undo()
    {
        $this->exec('rm /tmp/testje.txt');
    }
}
