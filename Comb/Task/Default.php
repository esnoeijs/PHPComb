<?php
class Comb_Task_Default extends Comb_BaseTask
{
    protected $serverLists = array('web');

    public function run()
    {
        $this->exec('ls -la > myfile.txt');
        $this->exec('cat myfile.txt');
        $this->exec('mv myfile.txt /root/');
        $this->exec('date');
    }

    public function undo()
    {
        $this->exec('rm /root/myfile.txt');
    }
}
