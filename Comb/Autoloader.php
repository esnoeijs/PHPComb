<?php
class Comb_Autoloader
{
    /**
     * Autoloader method called. We'll try to include the file based on the classname
     * @param string $className the class we're looking for
     * @return boolean true if class was successfully included, false if not
     */
    public static function load($className)
    {
        if (substr($className, 0, 10) == 'Comb_Task_') {
            return self::loadTask($className);
        }

        if (substr($className, 0, 5) == 'Comb_') {
            return self::loadCombClass($className);
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
        $filepath = COMB_APPLICATION_ROOT . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        try {
            require_once($filepath);
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * Loads the task. We can't rely on the autoloader now, because we want to
     * search the folders in a very specific way. The method will first look at
     * the project folder to see if the file is there, then it will check the
     * application tasks for inclusion. In the future it's probably a good idea to
     * add a third option in the middle, being a companies central tasks
     * repository.
     * @param string $className the classname of the task we're trying to find
     * @return boolean true if we included the task successfull, false if not
     */
    protected static function loadTask($className)
    {
        $taskName = str_replace('Comb_Task_', '', $className);
        if (self::loadTaskFromProjectfolder($taskName)) {
            return true;
        } elseif(self::loadTaskFromApplicationfolder($taskName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Loads the task from the project folder, in the dir comb/{$task}.php
     * @param string $task the task to include
     * @return boolean true if succeeded, false if not
     */
    protected static function loadTaskFromProjectfolder($task)
    {
        $tasksDir = COMB_PROJECT_ROOT . 'comb' . DIRECTORY_SEPARATOR;

        $filePath = $tasksDir . strtolower($task) . '.php';
        if (file_exists($filePath)) {
            require_once $filePath;
            Comb_Registry::get('logger')->debug("Loaded task $task from projectfolder: $filePath");
            return true;
        }
        return false;
    }

    /**
     * Loads the task from the application folder, in the dir Comb/Task/{$task}.php
     * @param string $task the task to include
     * @return boolean true if succeeded, false if not
     */
    protected function loadTaskFromApplicationfolder($task)
    {
        $tasksDir = COMB_APPLICATION_ROOT . 'Comb' . DIRECTORY_SEPARATOR . 'Task' . DIRECTORY_SEPARATOR;

        $filePath = $tasksDir . strtolower($task) . '.php';
        if (file_exists($filePath)) {
            require_once $filePath;
            Comb_Registry::get('logger')->debug("Loaded task $task from applicationfolder: $filePath");
            return true;
        }
        return false;
    }
}