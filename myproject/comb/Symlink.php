<?php
class Comb_Task_Symlink extends Comb_BaseTask
{
    protected $serverLists = array('web');

    public function run()
    {
        $this->exec('ln -s myfile2.txt myfile2-symlink.txt');
    }

    public function undo()
    {
        $this->exec('rm myfile2-symlink.txt');
    }
}
