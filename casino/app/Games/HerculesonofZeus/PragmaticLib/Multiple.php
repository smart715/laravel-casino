<?php

namespace VanguardLTE\Games\HerculesonofZeus\PragmaticLib;

class Multiple
{
    public static function getBonanzaMultiple($slotArea, $gameSettings, $currentLog){
        $tmp = explode(';', $gameSettings['prm']);
        $tmp = explode('~', $tmp[0]);
        $prm = $tmp[0]; // multiplier symbol
        $prmMultipliers = explode(',',$tmp[1]); // array of possible multiplier values

        // rebuild the playing field on reels
        $reels = 6;
        $tmpSlotArea = array_chunk($slotArea, $reels);
        $currentSlotArea = [];
        $k = 0;
        while ($k < $reels) { // rearrange from rows to rows
            $i = 0;
            while ($i < $gameSettings['sh']) {
                $currentSlotArea[$k][] = $tmpSlotArea[$i][$k];
                $i++;
            }
            $k++;
        }
        $prmReady = [];
        // go through all the coils from the end, and assign multipliers, write in the log which multiplier from which coil
        foreach ($currentSlotArea as $reelKey => $reel) {
            foreach (array_reverse($reel) as $symbolKey => $symbol) {
                if ($symbol == $prm){
                    $symbolsCount = $gameSettings['sh'] - 1; // arrays start at 0. To find out how many symbols should be in the reel
                    $prmSymbol = $reelKey + ($reels * ($symbolsCount - $symbolKey)); // calculate position. Coil number, add to the coil position from the beginning
                    $prmReady[] = [
                        'Symbol' => $prm,
                        'Position' => $prmSymbol,
                        'Multiplier' => self::getMultiplier($currentLog,$prmMultipliers,$reelKey),
                        'Reel' => $reelKey
                    ];
                }
            }
        }
        if ($prmReady) return $prmReady;
        else return false;
    }
    private static function getMultiplier($currentLog, $prmMultipliers, $reelKey){
        if ($currentLog && array_key_exists('Multipliers', $currentLog)){
            foreach ($currentLog['Multipliers'] as $logMultiplier) {
                if ($logMultiplier['Reel'] == $reelKey){
                    $multiplier = $logMultiplier['Multiplier'];
                }
            }
        }
        if (isset($multiplier)) return $multiplier;
        else $multiplier = $prmMultipliers[array_rand($prmMultipliers)];
        return $multiplier;
    }

}
