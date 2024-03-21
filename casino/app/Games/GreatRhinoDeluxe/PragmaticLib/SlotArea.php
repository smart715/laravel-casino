<?php

namespace VanguardLTE\Games\GreatRhinoDeluxe\PragmaticLib;

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
            $positions[$key] = rand(0, count($reelset[$key]));
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
            $slotArea[$scatterPositions[$i]] = ''.rand(4, 11);
            $scatterCount -= 1;
            $i += 1;
            // var_dump($scatterCount, $scatterPositions, $slotArea);
        }
        var_dump('0_10');

        $return = ['SlotArea' => $slotArea,
            'SymbolsAfter' => $symbolsAfter,
            'SymbolsBelow' => $symbolsBelow,
            'ScatterCount' => $scatterCount
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

    public static function getFullStackCnt($slotArea){
        $cnt = 0;
        $i = 0;
        while($i < 5){
            if($slotArea[$i] == 3 && $slotArea[$i] == $slotArea[$i + 5] && $slotArea[$i] == $slotArea[$i + 10])  
                $cnt ++;
            $i ++;
        }
        return $cnt;
    }

    public static function makeFullStack(&$slotArea){
        $cnt = 2;
        while($cnt){
            $index = rand(0, 4);
            if($slotArea['SlotArea'][$index] != 3){
                $slotArea['SlotArea'][$index] = 3;
                $slotArea['SlotArea'][$index + 5] = 3;
                $slotArea['SlotArea'][$index + 10] = 3;
                $cnt --;
            }
        }
    }
}
