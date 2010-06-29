<?php
class Comb_Registry
{
    /**
     * Array containing the registry data
     * @var Array
     */
    protected static $data = array();

    /**
     * Set registry key to a certain value
     * @param string $key   the key
     * @param mixed $value  whatever we want to store in the registry
     */
    public static function set($key, $value)
    {
        self::$data[$key] = $value;
    }

    /**
     * Returns the value previously set for the key provided
     * @param string $key   the key
     * @return mixed        whatever we put in the registry before, null if
     *                      nothing was set.
     */
    public static function get($key)
    {
        if (array_key_exists($key, self::$data)) {
            return self::$data[$key];
        } else {
            return null;
        }
    }
}