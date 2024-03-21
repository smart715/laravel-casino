<?php

namespace VanguardLTE\Games\WildWestGold\PragmaticLib;

class BuyFreeSpins
{
    public static function addWilds(&$slotArea, $gameSettings, $cnt, $max){
        $reels = [
            [0, 5, 10, 15],  [1, 6, 11, 16], [2, 7, 12, 17], [3, 8, 13, 18], [4, 9, 14, 19]
        ];
        $reelPosition = [-1, -1, -1, -1, -1];
        $reels = [1, 2, 3];
        if($cnt == 0)
            $cnt = self::getWildCnt();
        $cnt = $cnt > $max ? $max : $cnt;
        $wild = 2;
        $wildPositions = array_keys($slotArea, $wild);

        foreach($wildPositions as $value){
            $reelPosition[$value % 5] = round($value / 5, 0);
        }
        while(count($wildPositions) < $cnt){
            $index = rand(0, 2);
            $index = $reels[$index];
            if($reelPosition[$index] == -1){
                $reelPosition[$index] = rand(0, 3);
                $wildPositions[] = $index + $reelPosition[$index] * 5;
                $slotArea[$wildPositions[count($wildPositions) - 1]] = $wild;
            }
        }
        var_dump('1_0_cnt='.$cnt.'_sPos='.implode(',', $wildPositions).'_s='.implode(',', $slotArea));
        return $cnt;
    }

    public static function addScatters(&$slotArea, $gameSettings, $cnt, $max){
        $reels = [
            [0, 5, 10, 15],  [1, 6, 11, 16], [2, 7, 12, 17], [3, 8, 13, 18], [4, 9, 14, 19]
        ];
        $reelPosition = [-1, -1, -1, -1, -1];
        $reels = [0, 2, 4];
        if($cnt == 0)
            $cnt = self::getScatterCnt();
        $cnt = $cnt > $max ? $max : $cnt;
        $scatter = explode('~',$gameSettings['scatters'])[0];
        $scatterPositions = array_keys($slotArea, $scatter);

        foreach($scatterPositions as $value){
            $reelPosition[$value % 5] = round($value / 5, 0);
        }
        while(count($scatterPositions) < $cnt){
            $index = rand(0, 2);
            $index = $reels[$index];
            if($reelPosition[$index] == -1){
                $reelPosition[$index] = rand(0, 3);
                $scatterPositions[] = $index + $reelPosition[$index] * 5;
                $slotArea[$scatterPositions[count($scatterPositions) - 1]] = $scatter;
            }
        }
        var_dump('1_0_cnt='.$cnt.'_sPos='.implode(',', $scatterPositions).'_s='.implode(',', $slotArea));
        return $cnt;
    }

    private static function getWildCnt(){
        $rn = rand(1, 1000);
        var_dump('wildRand='.$rn);
        if($rn >= 51 && $rn <= 70)    return 2;
        if($rn >= 101 && $rn <= 340)    return 1;
        return 0;
    }

    private static function getScatterCnt(){
        $rn = rand(1, 1000);
        if($rn >= 21 && $rn <= 25)    return 3;
        if($rn >= 51 && $rn <= 70)    return 2;
        if($rn >= 101 && $rn <= 201)    return 1;
        return 0;
    }
}
