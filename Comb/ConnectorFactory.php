<?php
class Comb_ConnectorFactory
{
    public static function getConnector($type)
    {
        static $connectors=array();

        if(!array_key_exists($type, $connectors)) {
            $className = 'Comb_Connector_' . ucfirst($type);
            $connector = new $className();
            $connectors[$type] = $connector;
        }
        return $connectors[$type];
    }
}
