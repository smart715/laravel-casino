<?php

namespace VanguardLTE\Games\GloriousRome\PragmaticLib;

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
            'def_s=5,7,4,2,7,4,8,3,3,6,3,9,5,1,11',
            's=5,7,4,2,7,4,8,3,3,6,3,9,5,1,11',
            'sa=3,10,4,5,11',
            'sb=5,6,11,2,8',
            'bl=0',
            'defc=0.10',
            'c=0.10',
            'l=5',
            ];
    }

}
