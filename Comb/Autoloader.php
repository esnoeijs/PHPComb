<?php
class Comb_Autoloader
{
    static protected $includePaths = array();

    /**
     * Adds a path from which the autoloader will attempt to load classes.
     * Priority gets determined by reverse insertion, so the last path to
     * be added will be the first to be checked.
     *
     * @todo implement different method for prioritizing include paths
     *
     * @param String $path
     */
    public static function setIncludePaths($path)
    {
        if (substr($path, -1) === DIRECTORY_SEPARATOR)
            array_unshift(self::$includePaths, substr($path, 0, -1));
        else
            array_unshift(self::$includePaths, $path);
    }

    /**
     * Autoloader method called. We'll try to include the file based on the classname
     * @param string $className the class we're looking for
     * @return boolean true if class was successfully included, false if not
     */
    public static function load($className)
    {
        switch (true)
        {
            case (substr($className, 0, 10) == 'Comb_Task_'):
                return self::loadTask($className);
                break;
            case (substr($className, 0, 5) == 'Comb_'):
                return self::loadCombClass($className);
                break;
        }

        return false;
    }


    /**
     * Load a 'normal' Comb-class
     * @param string $className the class to include
     * @return boolean true if succeeded
     */
    protected static function loadCombClass($className)
    {
        foreach (self::$includePaths as $path) {
            $filepath = $path . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
            if (file_exists($filepath)) {
                return (require_once($filepath));
            }
        }

        return false;
    }

    /**
     * Searches for the Task class file to include from the defined include paths.
     * 
     * @param string $className the classname of the task we're trying to find
     * @return boolean true if we included the task successfull, false if not
     */
    protected static function loadTask($className)
    {
        $taskName = strtolower(str_replace('Comb_Task_', '', $className));

        foreach (self::$includePaths as $path)
        {
            /**
             * @todo Remove the necessity for this.
             */
           foreach (array('Comb','comb') as $combDir)
           {
               $filePath = $path . DIRECTORY_SEPARATOR . $combDir . DIRECTORY_SEPARATOR . 'Task'  . DIRECTORY_SEPARATOR . $taskName . '.php';
               if (file_exists($filePath)) {
                    Comb_Registry::get('logger')->debug("Loading task $taskName from applicationfolder: $filePath");
                    return (require_once($filePath));
               }
           }
        }

        return false;
    }
}
