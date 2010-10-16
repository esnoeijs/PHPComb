<?php
include_once(COMB_APPLICATION_ROOT . 'Comb' . DIRECTORY_SEPARATOR . 'CommandlineParams.php');
include_once(COMB_APPLICATION_ROOT . 'Comb' . DIRECTORY_SEPARATOR . 'Logger.php');

class LoggerTest extends PHPUnit_Framework_TestCase
{
    public function testDebug_verbose()
    {
        $commandlineParamsMock = $this->getMock('Comb_CommandlineParams');
        $commandlineParamsMock->expects($this->once())
                              ->method('optionSelected')
                              ->with($this->equalTo('verbose'))
                              ->will($this->returnValue(true));

        $logger = $this->getMock(
            'Comb_Logger',
            array('log'),
            array($commandlineParamsMock)
        );

        $logger->expects($this->once())
               ->method('log')
               ->with($this->equalTo('debug > Test debug message'));

        $logger->debug('Test debug message');
    }

    public function testDebug_nonVerbose()
    {
        $commandlineParamsMock = $this->getMock('Comb_CommandlineParams');
        $commandlineParamsMock->expects($this->once())
                              ->method('optionSelected')
                              ->with($this->equalTo('verbose'))
                              ->will($this->returnValue(false));

        $logger = $this->getMock(
            'Comb_Logger',
            array('log'),
            array($commandlineParamsMock)
        );

        $logger->expects($this->never())
               ->method('log');

        $logger->debug('Test debug message');
    }
}