<?php
class Comb_CommandlineParams
{
    protected $possibleOptions = array(
        'color' => array(
            'alias' => 'c',
            'description' => 'Use colorized output',
        ),
        'dry-run' => array(
            'alias' => 'd',
            'description' => 'show command that would be executed without actually executing'
        ),
        'quiet' => array(
            'alias' => 'q',
            'description' => 'only display errors and fatal errors'
        ),
        'help' => array(
            'alias' => 'h',
            'description' => 'display help (this)'
        ),
        'verbose' => array(
            'alias' => 'v',
            'description' => 'show debug information'
        ),
    );

    protected $enabledOptions = array();

    /**
     * Loads the commandline params provided
     * @param array $params the commandline parameters array to parse
     * @return boolean true if successfull, false if failed
     */
    public function loadParams(Array $params=array())
    {
        $paramCount = count($params);

        if ($paramCount == 1) {
            return true;
        }

        try {
            for($i=1; $i<$paramCount; $i++) {
                $param = $params[$i];
                if (substr($param, 0, 2) == '--') {
                    $this->setOptionByFullName(substr($param,2));
                }
                elseif (substr($param, 0, 1) == '-') {
                    $this->setOptionByAlias(substr($param,1));
                }
            }

            $this->checkCombinations();
        }
        catch(Comb_InvalidCommandlineException $exception) {
            echo "Error parsing commandline: " . $exception->getMessage() . PHP_EOL;
            return false;
        }

        return true;
    }

    protected function checkCombinations()
    {
        if (in_array('verbose', $this->enabledOptions) && in_array('quiet', $this->enabledOptions)) {
            throw new Comb_InvalidCommandlineException('It\'s not possible to be quiet and verbose at the same time');
        }
    }

    protected function setOptionByFullName($option)
    {
        if (!array_key_exists($option, $this->possibleOptions)) {
            throw new Comb_InvalidCommandlineException('--' . $option . ' is not a valid commandline option');
        }
        $this->enabledOptions[] = $option;
    }

    protected function setOptionByAlias($alias)
    {
        foreach($this->possibleOptions as $optionName => $optionDetails) {
            if ($optionDetails['alias'] == $alias) {
                $this->setOptionByFullName($optionName);
                return;
            }
        }
        throw new Comb_InvalidCommandlineException('-' . $alias . ' is not a valid commandline option');
    }

    public function optionSelected($optionname)
    {
        if (!array_key_exists($optionname, $this->possibleOptions)) {
            throw new Exception('Option ' . $optionname . ' is invalid');
        }
        return in_array($optionname, $this->enabledOptions);
    }

    public function getSyntaxExplain()
    {
        $explain =  "Usage: comb [OPTION...] [TASK]" . PHP_EOL;
        $explain .= "Runs the PHPComb task specified" . PHP_EOL . PHP_EOL;
        

        foreach($this->possibleOptions as $option => $details) {
            $explain .= sprintf("  % 3s--% -12s %s",
                (isset($details['alias']) ? '-' . $details['alias'] . ', ' : ''),
                $option,
                (isset($details['description']) ? $details['description'] : '')
            );

            $explain .= PHP_EOL;
        }

        $explain .= PHP_EOL;

        $explain .= "For the complete documentation, license and bugreports check http://phpcomb.net/" . PHP_EOL;
        return $explain;
    }
}