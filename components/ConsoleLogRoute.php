<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

class ConsoleLogRoute extends CLogRoute
{
    protected function processLogs($logs)
    {
        foreach($logs as $log)
            echo "[$log[1]] [$log[2]] $log[0]\n";
    }
}