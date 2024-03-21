<?php

namespace VanguardLTE\Games\HerculesonofZeus\PragmaticLib;

class FreeSpin
{
    public static function check($slotArea, $log, $gameSettings, $bet){
        // check how many scatters are on the field
        $freeSpins = false;
        $addFreeSpins = false;

        $scatterTmp = explode('~',$gameSettings['scatters']);
        $scatter = $scatterTmp[0];
        $scatterPayTable = array_reverse(explode(',', $scatterTmp[1]));
        $scatterPositions = array_keys($slotArea, $scatter);
        $symbols = array_count_values($slotArea); // keys - characters / values - number of characters
        if (array_key_exists($scatter, $symbols)){ // if there are scatters in the field
            if ($log && array_key_exists('FreeSpinNumber', $log) && $log['FreeState'] != 'LastFreeSpin'){ // if there are already free spins
                if ($symbols[$scatter] >= $gameSettings['settings_needaddfs']){ // If there is enough scatter to add free spins
                    $addFreeSpins = $gameSettings['settings_addfs'];
                }
            }else{
                $pay = $scatterPayTable[$symbols[$scatter]-1]; // pay the number of times scattered
                $win = round($pay * $bet, 2);
                if ($win > 0){
                    $freeSpins = $gameSettings['settings_fs'];
                }
            }
        }
        if ($freeSpins) return ['FreeSpins' => $freeSpins, 'Pay' => $win, 'ScatterPositions' => $scatterPositions, 'Scatter' => $scatter];
        if ($addFreeSpins) return ['AddFreeSpins' => $addFreeSpins];
        return false;
    }

}
