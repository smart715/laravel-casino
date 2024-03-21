<?php

namespace VanguardLTE\Games\VampiresvsWolves\PragmaticLib;

class BuyFreeSpins
{
    public static function getFreeSpin(&$slotArea, $gameSettings, $cnt, $max){
        $reels = [
            [0, 5, 10],  [1, 6, 11], [2, 7, 12], [3, 8, 13], [4, 9, 14]
        ];
        $reelPosition = [0, 4];
        if($cnt == 0)
            $cnt = self::getCnt();
        $cnt = $cnt > $max ? $max : $cnt;
        $scatterTmp = explode('~',$gameSettings['scatters']);
        $scatter = $scatterTmp[0];
        $scatterPositions = array_keys($slotArea, $scatter);

        foreach($scatterPositions as $value){
            $reelPosition[$value % 5] = round($value / 5, 0);
        }
        while(count($scatterPositions) < $cnt){
            $index = $reelPosition[rand(0, 1)];
            $sh = rand(0, 2);
            $pos = $index + $sh * 5;
            if($slotArea[$pos] != $scatter){
                $scatterPositions[] = $pos;
                $slotArea[$pos] = $scatter;
                if($sh == 1){
                    $pos += 5;
                    if($slotArea[$pos] != $scatter){
                        $scatterPositions[] = $pos;
                        $slotArea[$pos] = $scatter;
                    }
                }
            }
        }
        var_dump('1_0_cnt='.$cnt.'_sPos='.implode(',', $scatterPositions).'_s='.implode(',', $slotArea));
        return $cnt;
    }

    private static function getCnt(){
        $rn = rand(1, 1000);
        if($rn >= 16 && $rn <= 17)    return 6;
        if($rn >= 23 && $rn <= 24)    return 5;
        if($rn >= 51 && $rn <= 70)    return 4;
        if($rn >= 91 && $rn <= 130)    return 3;
        if($rn >= 151 && $rn <= 200)    return 2;
        if($rn >= 221 && $rn <= 280)    return 1;
        return 0;
    }
}
