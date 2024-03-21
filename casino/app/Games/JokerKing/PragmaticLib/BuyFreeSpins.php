<?php

namespace VanguardLTE\Games\JokerKing\PragmaticLib;

class BuyFreeSpins
{
    public static function addWilds(&$slotArea, $gameSettings, $cnt, $max){
        if($cnt == 0)
            $cnt = self::getWildCnt();
        $cnt = $cnt > $max ? $max : $cnt;
        $wild = explode('~',$gameSettings['wilds'])[0];
        $wildPositions = array_keys($slotArea, $wild);

        while(count($wildPositions) < $cnt){
            $index = rand(0, 23);
            var_dump('slotarea='.implode(',', $slotArea).'_wilds='.implode(',', $wildPositions));
            if($slotArea[$index] != $wild){
                $wildPositions[] = $index;
                $slotArea[$index] = $wild;
            }
        }
        var_dump('1_0_cnt='.$cnt.'_sPos='.implode(',', $wildPositions).'_s='.implode(',', $slotArea));
        return $cnt;
    }
    public static function getFreeSpin(&$slotArea, $gameSettings, $cnt, $max){
        $reels = [
            [0, 6, 12, 18],  [1, 7, 13, 19], [2, 8, 14, 20], [3, 9, 15, 21], [4, 10, 16, 22], [5, 11, 17, 23]
        ];
        $reelPosition = [-1, -1, -1, -1, -1, -1];
        if($cnt == 0)
            $cnt = self::getCnt();
        $cnt = $cnt > $max ? $max : $cnt;
        $scatterTmp = explode('~',$gameSettings['scatters']);
        $scatter = $scatterTmp[0];
        $scatterPositions = array_keys($slotArea, $scatter);

        foreach($scatterPositions as $value){
            $reelPosition[$value % 6] = round($value / 6, 0);
        }
        while(count($scatterPositions) < $cnt){
            $index = rand(0, 5);
            if($reelPosition[$index] == -1){
                $reelPosition[$index] = rand(0, 3);
                $scatterPositions[] = $index + $reelPosition[$index] * 5;
                $slotArea[$scatterPositions[count($scatterPositions) - 1]] = $scatter;
            }
        }
        var_dump('1_0_cnt='.$cnt.'_sPos='.implode(',', $scatterPositions).'_s='.implode(',', $slotArea));
        return $cnt;
    }

    private static function getCnt(){
        $rn = rand(1, 1000);
        if($rn >= 1 && $rn <= 1) return 5;
        if($rn >= 11 && $rn <= 12)    return 4;
        if($rn >= 16 && $rn <= 17)    return 3;
        if($rn >= 51 && $rn <= 101)    return 2;
        if($rn >= 151 && $rn <= 201)    return 1;
        return 0;
    }
    private static function getWildCnt(){
         $rn = rand(1, 1000);
         if($rn >= 381 && $rn <= 400)    return 2;
         if($rn >= 555 && $rn <= 625)    return 1;
         return 0;
     }
}
