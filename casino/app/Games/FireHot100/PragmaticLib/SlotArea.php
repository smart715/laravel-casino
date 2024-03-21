<?php

namespace VanguardLTE\Games\FireHot100\PragmaticLib;

class SlotArea
{
    public static function getSlotArea($gameSettings, $reelset, $log){
        // parse from the reelset settings, specify 1 or 0 depending on the RTP and increase the chances for a large bet.
        var_dump('0_3');
        $reelset = explode('~', $gameSettings['reel_set'.$reelset]);
        foreach ($reelset as &$reel) { // convert the string to an array to make it more convenient to work
            $reel = explode(',', $reel);
        }
        $sh = $gameSettings['sh'];
        var_dump('0_4');

        $positions = [];
        // get random coil positions
        foreach ($reelset as $key => $value) {
            $positions[$key] = rand(0, count($reelset[$key]) - 5);
        }
        var_dump('0_5');
        // fill the playing field with symbols
        $reels = [];
        $symbolsAfter = [];
        $symbolsBelow = [];
        var_dump('0_6');
        foreach ($positions as $key => $value) {
            // sh - number of visible symbols in one reel
            $reelsetCycled = array_merge($reelset[$key], array_slice($reelset[$key], 0, 10)); // loop coils
            $reels[$key] = array_slice($reelsetCycled, $value, $gameSettings['sh']); // Filling the Coils
            $symbolsAfter[$key] = implode('', array_slice($reelsetCycled, $value - 1, 1));
            $symbolsBelow[$key] = $reels[$key][array_key_last($reels[$key])];
        }
        
        var_dump('0_7');

        // add all symbols into an array to calculate the number of wins & get the number of scatters & wilds
        $scatterTmp = explode('~',$gameSettings['scatters']);
        $wild = explode('~',$gameSettings['wilds'])[0];
        $specialScatter = 11;
        $scatter = $scatterTmp[0];
        $scatterCount = 0;
        $slotArea = [];
        $i = 0;
        while ($i < $gameSettings['sh']) {
            $k = 0;
            while ($k < count($reels)) {
                $slotArea[] = $reels[$k][$i];
                $k++;
            }
            $i++;
        }
        $scatterPositions = array_keys($slotArea, $scatter);
        $scatterCount = count($scatterPositions);
        $i = 0;
        $limit = 1;
        while($scatterCount > $limit){
            $slotArea[$scatterPositions[$i]] = ''.rand(4, 10);
            $scatterCount -= 1;
            $i += 1;
            // var_dump($scatterCount, $scatterPositions, $slotArea);
        }
        // Limit wilds
        $wildPositions = array_keys($slotArea, $wild);
        $wildCount = count($wildPositions);
        $i = 0;
        $limit = 1;
        while($wildCount > $limit){
            $slotArea[$wildPositions[$i]] = ''.rand(4, 10);
            $wildCount -= 1;
            $i += 1;
            // var_dump($scatterCount, $scatterPositions, $slotArea);
        }
        // Limit Special Scatters
        $specialScatterPositions = array_keys($slotArea, $specialScatter);
        $specialScatterCount = count($specialScatterPositions);
        $i = 0;
        $limit = 2;
        while($specialScatterCount > $limit){
            $slotArea[$specialScatterPositions[$i]] = ''.rand(4, 10);
            $specialScatterCount -= 1;
            $i += 1;
            // var_dump($scatterCount, $scatterPositions, $slotArea);
        }
        var_dump('0_10');

        $return = ['SlotArea' => $slotArea,
            'SymbolsAfter' => $symbolsAfter,
            'SymbolsBelow' => $symbolsBelow,
            'ScatterCount' => $scatterCount,
            'WildCount' => $wildCount
            ];   
        
        return $return;

        // if this is a respin, then load the past state of the playing field from the log, remove the winning symbols from there and lower the symbols from top to bottom
        //if ($log && in_array('rs=t', $log)) $slotArea = '';
        // if there is no respin, then we generate stop positions and collect the playing field from the dropped symbols, as well as symbols before and after

    }

    public static function getPsym($gameSettings, $slotarea, $bet, $lines){
        // var_dump('5_1_1_0');
        $scatterTmp = explode('~',$gameSettings['scatters']);
        $scatter = $scatterTmp[0];
        // var_dump('5_1_1_1_scatterTmp='.$gameSettings['scatters']);
        $sCounts = array_count_values($slotarea);
        $scatterPayTable = explode(',', $scatterTmp[1]);
        $cnt = array_key_exists($scatter, $sCounts) ? $sCounts[$scatter] : 1;
        // var_dump('5_1_1_2_'.$scatterTmp[1].'_'.$cnt, $slotarea);
        $pay = round($scatterPayTable[count($scatterPayTable) - $cnt] * $bet * $lines, 2); // pay the number of times scattered

        return ['psym' => $scatter.'~'.$pay.'~'.implode(',', array_keys($slotarea, $scatter)),
            'psymwin' => $pay];
    }

    public static function handleExpanding($slotArea, $gameSettings){
        // expand wilds
        $wild = explode('~',$gameSettings['wilds'])[0];
        $isExpanding = 0;
        $symbolsAfterExpanding = $slotArea;
        $w_exp = '';
        $i = 0;
        while($i < 5){
            $j = 0;
            while($j < $gameSettings['sh']){
                if($slotArea[$j * 5 + $i] == $wild){
                    $isExpanding = 1;
                    $k = 0;
                    while($k < $gameSettings['sh']){
                        $symbolsAfterExpanding[$k * 5 + $i] = $wild;
                        $w_exp = $w_exp.$slotArea[$k * 5 + $i].'~'.$wild.'~'.($k * 5 + $i).';';
                        $k ++;
                    }
                    $j = $gameSettings['sh'];
                }
                $j ++;
            }
            $i ++;
        }

        $return = [];
        // if there is expanding
        if($isExpanding){
            $return['SymbolsAfterExpanding'] = $symbolsAfterExpanding;
            $return['W_exp'] = $w_exp;
        }
        return $return;
    }

    public static function checkWildScatter($slotArea){
        $i = 0;
        while($i < 5) {
            $wildCnt = 0;
            $scatterCnt = 0;
            $j = 0;
            while($j < count($slotArea) / 5){
                if($slotArea[$i + $j * 5] == 2)
                    $wildCnt = 1;
                if($slotArea[$i + $j * 5] == 1)
                    $scatterCnt = 1;
                $j ++;
            }
            $i ++;
            if($wildCnt > 0 && $scatterCnt > 0)
                return 0;
        }
        return 1;
    }
}
