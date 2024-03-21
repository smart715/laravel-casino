<?php

namespace VanguardLTE\Games\SugarRush\PragmaticLib;

class GameSettings
{
    public $all;

    public function __construct($init){
        foreach ($init as $value) {
            $tmp = explode('=',$value);
            $this->all[$tmp[0]] = $tmp[1];
        }
    }

}
