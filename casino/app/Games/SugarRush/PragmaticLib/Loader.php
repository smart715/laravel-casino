<?php

namespace VanguardLTE\Games\SugarRush\PragmaticLib;

class Loader
{
    private $initFile, $log;

    public function __construct($initFile, $balance, $log)
    {
        $this->initFile = $initFile;
        $this->log = $log->getLog();
        $time = (int) round(microtime(true) * 1000);
        array_push($this->initFile, 'stime='.$time);
        array_push($this->initFile, 'balance=' . $balance, 'balance_cash=' . $balance);
        if (!$this->log){
            $def_set = $this->mergeLog();
            $this->initFile = array_merge($this->initFile, $def_set);
        }
        else array_push($this->initFile, implode('&', $this->log['ServerState']));
    }

    public function initStr()
    {
        return implode('&', $this->initFile);
    }

    private function mergeLog()
    {
        return [
            'def_s=3,8,4,8,1,10,6,10,5,7,8,9,6,9,8,7,4,5,3,4,3,8,4,8,1,10,6,10,5,7',
            's=3,8,4,8,1,10,6,10,5,7,8,9,6,9,8,7,4,5,3,4,3,8,4,8,1,10,6,10,5,7',
            'sa=8,3,4,3,11,3',
            'sb=5,10,11,8,1,7',
            'bl=0',
            'defc=0.10',
            'c=0.10',
            'l=20',
            ];
    }

}
