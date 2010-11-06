<?php
class Comb_Task_Default extends Comb_BaseTask
{
    protected $serverLists = array('web');

    public function run()
    {
        $this->sudo('touch myfile2.txt');
        $this->exec('ls -lah myfile2.txt');
        $this->runTask('Symlink');
    }

    public function undo()
    {
        $this->exec('rm myfile2.txt');
    }
}
