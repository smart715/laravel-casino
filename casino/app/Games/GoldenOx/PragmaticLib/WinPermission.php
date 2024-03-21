<?php

namespace VanguardLTE\Games\GoldenOx\PragmaticLib;

class WinPermission
{
    public static function winCheck($fswin, $pur, $bank, &$currentState, $win, $log, $changeBalance, $shop){
        if(array_key_exists('bpw', $currentState)){
            var_dump($currentState['bpw']);
            $win += $currentState['bpw'];
        }
        var_dump('win_permission win='.$win);
        if ($win + $fswin > $bank->slots - $changeBalance)    return false;
        if(array_key_exists('bpw', $currentState)){
            var_dump($currentState['bpw']);
            $win -= $currentState['bpw'];
        }

        return ['win' => $win + $fswin , 'fswin' => $fswin];
    }

}
