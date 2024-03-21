<?php

namespace VanguardLTE\Games\AncientEgyptClassic\PragmaticLib;

class WinPermission
{
    public static function winCheck($fswin, $pur, $bank, &$currentState, $win, $log, $changeBalance, $shop){
        if ($win + $fswin > $bank->slots - $changeBalance)    return false;

        return ['win' => $win + $fswin , 'fswin' => $fswin];
    }

}
