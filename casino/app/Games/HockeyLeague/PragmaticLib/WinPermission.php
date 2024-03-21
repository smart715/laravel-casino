<?php

namespace VanguardLTE\Games\HockeyLeague\PragmaticLib;

class WinPermission
{
    public static function winCheck($fswin, $pur, $bank, &$currentState, $win, $log, $changeBalance, $shop){
        if ($win + $fswin > $bank->slots)    return false;
        var_dump('winPermission win='.$win.' fswin='.$fswin);
        return ['win' => $win + $fswin , 'fswin' => 0];
    }

}
