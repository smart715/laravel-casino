<?php

namespace VanguardLTE\Games\BookofTutPM\PragmaticLib;

class WinPermission
{
    public static function winCheck($fswin, $pur, $bank, &$currentState, $win, $log, $changeBalance, $shop){
        // var_dump('7_0_win_'.$win.'_slots_'.$bank->slots.'_fswin_'.$fswin.'_bonus_'.$bank->bonus);
        // If Bonus is less then FS win return false
        if($pur == '0')
            $FSBET = $changeBalance * (($shop->percent - 1) / 100);
        else $FSBET = 0;

        // if there are no free spins now - check if there is an amount in the bank for payment
        if ($win + $fswin > $bank->slots - $changeBalance)    return false;
        return ['win' => $win + $fswin, 'fswin' => 0];
    }

}
