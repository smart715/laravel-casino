<?php

namespace VanguardLTE\Games\SugarRush\PragmaticLib;

class BuyFreeSpins
{
    public static function getFreeSpin(&$slotArea, $gameSettings){
        $scatterTmp = explode('~',$gameSettings['scatters']);
        $scatter = $scatterTmp[0];
        $scatterPositions = array_keys($slotArea, $scatter);

        if (count($scatterPositions) < $gameSettings['settings_needfs']){ // Если скаттеров меньше чем нужно - то генерим еще скаттеры
            newRand:
            $rand_keys = (array)array_rand($slotArea, ($gameSettings['settings_needfs'] - count($scatterPositions))); // получаем рандомные позиции
            // если вернулся массив а не одно число
            if (array_intersect($scatterPositions, $rand_keys)) goto newRand; // если есть пересечения позиций символов - то делаем новое рандомное размещение

            foreach ($rand_keys as $rand_key) {
                $slotArea[$rand_key] = $scatter; // присваиваем рандомно скаттеры
            }
        }
    }

}
