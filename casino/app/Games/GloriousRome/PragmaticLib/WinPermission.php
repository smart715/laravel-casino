<?php

namespace VanguardLTE\Games\GloriousRome\PragmaticLib;

class WinPermission
{
    public static function winCheck($fswin, $pur, $bank, &$currentState, $win, $log, $changeBalance, $shop){
        // var_dump('7_0_win_'.$win.'_slots_'.$bank->slots.'_fswin_'.$fswin.'_bonus_'.$bank->bonus);
        if ($win + $fswin > $bank->slots - $changeBalance)    return false;

        return ['win' => $win + $fswin, 'fswin' => 0];
    }

}
