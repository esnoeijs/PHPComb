<?php
class Comb_InvalidCommandlineException extends Exception
{
    public function errorMessage()
    {
        $errorMsg = $this->getMessage();
        return $errorMsg;
    }
}