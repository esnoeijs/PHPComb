<?php
interface Comb_ConnectorInterface
{
    public function execCommand($command, Array $serverLists);
}