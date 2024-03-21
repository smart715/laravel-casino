<?php

namespace VanguardLTE\Games\GatesofOlympus\PragmaticLib;

class Multiple
{
    public static function getBonanzaMultiple($slotArea, $gameSettings, $currentLog){
        $tmp = explode(';', $gameSettings['prm']);
        $tmp = explode('~', $tmp[0]);
        $prm = $tmp[0]; // символ множителя
        $prmMultipliers = explode(',',$tmp[1]); // массив возможных значений множителя

        // перестроить игровое поле на катушки
        $reels = 6;
        $tmpSlotArea = array_chunk($slotArea, $reels);
        $currentSlotArea = [];
        $k = 0;
        while ($k < $reels) { // перестроить со строк на ряды
            $i = 0;
            while ($i < $gameSettings['sh']) {
                $currentSlotArea[$k][] = $tmpSlotArea[$i][$k];
                $i++;
            }
            $k++;
        }
        $prmReady = [];
        // пройти по всем катушкам с конца, и присвоить множители, в лог писать какой множитель от какой катушки
        foreach ($currentSlotArea as $reelKey => $reel) {
            foreach (array_reverse($reel) as $symbolKey => $symbol) {
                if ($symbol == $prm){
                    $symbolsCount = $gameSettings['sh'] - 1; // массивы с 0 начинаются. Чтобы узнать сколько должно быть в катушке символов
                    $prmSymbol = $reelKey + ($reels * ($symbolsCount - $symbolKey)); // вычисляем поозицию. Номер катушки, прибавляем к позиции  катушке с начала
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
