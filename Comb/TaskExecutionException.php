<?php
class Comb_TaskExecutionException extends Exception
{
    public function errorMessage()
    {
        $errorMsg = 'An error occurred when executing the task: ';
        $errorMsg .= $this->getMessage();
        return $errorMsg;
    }
}