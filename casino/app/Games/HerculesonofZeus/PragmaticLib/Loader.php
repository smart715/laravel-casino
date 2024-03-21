<?php

namespace VanguardLTE\Games\HerculesonofZeus\PragmaticLib;

class Loader
{
    private $initFile, $log;

    public function __construct($initFile, $balance, $log)
    {
        $this->initFile = $initFile;
        $this->log = $log->getLog();
        $time = (int) round(microtime(true) * 1000);
        // array_push($this->initFile, 'stime='.$time);
        if ($this->log){
            $serverState[] = implode('&', $this->log['ServerState']);
            $this->initFile = array_merge($serverState, $this->initFile);
        }
        $this->initFile = array_merge(array('&balance='.$balance.'&balance_cash='.$balance.'&'), $this->initFile);
    }

    public function initStr()
    {
        return implode('&', $this->initFile);
    }

    private function mergeLog()
    {
        return [
            'def_s=5,8,7,9,8,8,7,3,4,4,11,6,8,11,10',
            's=5,8,7,9,8,8,7,3,4,4,11,6,8,11,10',
            'sa=11,12,10,8,9',
            'sb=5,3,4,6,7',
            'bl=0',
            'defc=0.10',
            'c=0.10',
            'l=10',
            ];
    }

}
