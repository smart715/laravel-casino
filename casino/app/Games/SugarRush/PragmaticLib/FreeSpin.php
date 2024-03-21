<?php

namespace VanguardLTE\Games\SugarRush\PragmaticLib;

class FreeSpin
{
    public static function check($slotArea, $log, $gameSettings, $bet){
        // проверить сколько скаттеров на поле
        $freeSpins = false;
        $addFreeSpins = false;

        $scatterTmp = explode('~',$gameSettings['scatters']);
        $scatter = $scatterTmp[0];
        $scatterPayTable = array_reverse(explode(',', $scatterTmp[1]));
        $scatterPositions = array_keys($slotArea, $scatter);
        $symbols = array_count_values($slotArea); // ключи - символы / значения - количество символов
        if (array_key_exists($scatter, $symbols)){ // если есть в поле скаттеры
            if ($log && array_key_exists('FreeSpinNumber', $log) && $log['FreeState'] != 'LastFreeSpin'){ // если уже есть фриспины
                if ($symbols[$scatter] >= $gameSettings['settings_needaddfs']){ // если скаттеров набирается нужное количество для добавления фриспинов
                    $addFreeSpins = $gameSettings['settings_addfs'] + $symbols[$scatter] - 3;
                }
            }else{
                // $pay = $scatterPayTable[$symbols[$scatter]-1]; // положить в pay сумму оплаты за количество скаттеров
                $win = 0;
                if ($symbols[$scatter] >= $gameSettings['settings_needfs']){
                    $freeSpins = $gameSettings['settings_fs'];
                }
            }
        }
        if ($freeSpins) return ['FreeSpins' => $freeSpins, 'Pay' => $win, 'ScatterPositions' => $scatterPositions, 'Scatter' => $scatter];
        if ($addFreeSpins) return ['AddFreeSpins' => $addFreeSpins];
        return false;
    }

}
