<?php

namespace VanguardLTE\Games\LuckyNewYearTigerTreasures\PragmaticLib;

class BuyFreeSpins
{
    public static function addWilds(&$slotArea, $gameSettings, $cnt, $max){
        if($cnt == 0)
            $cnt = self::getWildCnt();
        $cnt = $cnt > $max ? $max : $cnt;
        $wild = explode('~',$gameSettings['wilds'])[0];
        $wildPositions = array_keys($slotArea, $wild);

        while(count($wildPositions) < $cnt){
            $index = rand(0, 14);
            var_dump('slotarea='.implode(',', $slotArea).'_wilds='.implode(',', $wildPositions));
            if($slotArea[$index] != $wild){
                $wildPositions[] = $index;
                $slotArea[$index] = $wild;
            }
        }
        var_dump('1_0_cnt='.$cnt.'_sPos='.implode(',', $wildPositions).'_s='.implode(',', $slotArea));
        return $cnt;
    }

    public static function addScatters(&$slotArea, $gameSettings, $cnt, $max){
        $reels = [
            [0, 5, 10],  [1, 6, 11], [2, 7, 12], [3, 8, 13], [4, 9, 14]
        ];
        $reelPosition = [-1, 1, -1, 1, -1];
        if($cnt == 0)
            $cnt = self::getScatterCnt();
        $cnt = $cnt > $max ? $max : $cnt;
        $scatter = explode('~',$gameSettings['scatters'])[0];
        $scatterPositions = array_keys($slotArea, $scatter);

        foreach($scatterPositions as $value){
            $reelPosition[$value % 5] = round($value / 5, 0);
        }
        while(count($scatterPositions) < $cnt){
            $possibleReel = [0, 2, 4];
            $index = $possibleReel[rand(0, 2)];
            if($reelPosition[$index] == -1){
                $reelPosition[$index] = rand(0, 2);
                if($reelPosition[$index] == 1)
                $reelPosition[$index] -= 1;
                $scatterPositions[] = $index + $reelPosition[$index] * 5;
                $slotArea[$scatterPositions[count($scatterPositions) - 1]] = $scatter;
            }
        }
        var_dump('1_0_cnt='.$cnt.'_sPos='.implode(',', $scatterPositions).'_s='.implode(',', $slotArea));
        return $cnt;
    }

    private static function getWildCnt(){
       $rn = rand(1, 2000);
        if($rn >= 1 && $rn <= 1) return 5;
        if($rn >= 11 && $rn <= 12)    return 4;
        if($rn >= 16 && $rn <= 17)    return 3;
        if($rn >= 51 && $rn <= 101)    return 2;
        if($rn >= 151 && $rn <= 201)    return 1;
        return 0;
    }

    private static function getScatterCnt(){
       $rn = rand(1, 2000);
        if($rn >= 1 && $rn <= 1) return 5;
        if($rn >= 11 && $rn <= 12)    return 4;
        if($rn >= 16 && $rn <= 17)    return 3;
        if($rn >= 51 && $rn <= 101)    return 2;
        if($rn >= 151 && $rn <= 201)    return 1;
        return 0;
    }
}
