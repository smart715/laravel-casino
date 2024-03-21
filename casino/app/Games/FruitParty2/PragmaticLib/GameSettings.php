<?php

namespace VanguardLTE\Games\FruitParty2\PragmaticLib;

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
