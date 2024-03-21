<?php

namespace VanguardLTE\Games\BigBassSplash\PragmaticLib;

class SlotArea
{
    public static function getSlotArea($gameSettings, $reelset, $log){
        // parse from the reelset settings, specify 1 or 0 depending on the RTP and increase the chances for a large bet.
        var_dump('0_3_reelset='.$reelset);
        $reelset = explode('~', $gameSettings['reel_set'.$reelset]);
        foreach ($reelset as &$reel) { // convert the string to an array to make it more convenient to work
            $reel = explode(',', $reel);
        }
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
        
        // add all symbols into an array to calculate the number of wins & get the number of scatters
        $scatterTmp = explode('~',$gameSettings['scatters']);
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
            Redo:
            $slotArea[$scatterPositions[$i]] = ''.rand(3, 12);
            for($j = -1 * ($gameSettings['sh'] - 1); $j <= $gameSettings['sh'] - 1; $j ++)
                if($scatterPositions[$i] + $j * 5 >= 0 && $scatterPositions[$i] + $j * 5 < 15 && $j != 0)
                    if($slotArea[$scatterPositions[$i]] == $slotArea[$scatterPositions[$i] * 1 + $j * 5])
                        goto Redo;
            $scatterCount -= 1;
            $i += 1;
            // var_dump($scatterCount, $scatterPositions, $slotArea);
        }
        // Limit Wilds
        
        $wildPositions = array_keys($slotArea, 2);
        $wildCount = count($wildPositions);
        $i = 0;
        $limit = 1;
        if($log && array_key_exists('fs', $log))
            $limit = 0;
        while($wildCount > $limit){
            RedoWild:
            $slotArea[$wildPositions[$i]] = ''.rand(3, 12);
            for($j = -1 * ($gameSettings['sh'] - 1); $j <= $gameSettings['sh'] - 1; $j ++)
                if($wildPositions[$i] + $j * 5 >= 0 && $wildPositions[$i] + $j * 5 < 15 && $j != 0)
                    if($slotArea[$wildPositions[$i]] == $slotArea[$wildPositions[$i] * 1 + $j * 5])
                        goto RedoWild;
            $wildCount -= 1;
            $i += 1;
            // var_dump($scatterCount, $scatterPositions, $slotArea);
        }
        var_dump('0_10');

        return ['SlotArea' => $slotArea,
            'SymbolsAfter' => $symbolsAfter,
            'SymbolsBelow' => $symbolsBelow,
            'ScatterCount' => $scatterCount
        ];

        // if this is a respin, then load the past state of the playing field from the log, remove the winning symbols from there and lower the symbols from top to bottom
        //if ($log && in_array('rs=t', $log)) $slotArea = '';
        // if there is no respin, then we generate stop positions and collect the playing field from the dropped symbols, as well as symbols before and after

    }

    public static function getPsym($gameSettings, $slotarea, $bet, $lines){
        // var_dump('5_1_1_0');
        $scatterTmp = explode('~',$gameSettings['scatters']);
        $scatter = $scatterTmp[0];
        // var_dump('5_1_1_1');
        $sCounts = array_count_values($slotarea);
        $scatterPayTable = explode(',', $scatterTmp[1]);
        // var_dump('5_1_1_2_'.$scatterTmp[1].'_'.$sCounts[$scatter], $slotarea);
        $pay = round($scatterPayTable[$sCounts[$scatter]-1] * $bet * $lines, 2); // pay the number of times scattered

        return ['psym' => $scatter.'~'.$pay.'~'.implode(',', array_keys($slotarea, $scatter)),
            'psymwin' => $pay];
    }

    public static function getMO($gameSettings, &$SlotArea, $log){
        $slotArea = $SlotArea['SlotArea'];
        $moneySymbol = 7;
        $mo_v = explode(',', $gameSettings['mo_v']);
        var_dump('getMO_mo_v='.implode(',', $mo_v));
        
        $mo = [];
        $mo_t = [];
        $stf = [];
        $mo_wpos = [];
        
        $i = 0;
        while($i < $gameSettings['sh'] * 5){
            if($slotArea[$i] == 2 && array_key_exists('fs', $log)){
                $mo[] = 0;
                $mo_t[] = 'r';
                $mo_wpos[] = $i;
            }
            else if($slotArea[$i] == $moneySymbol){
                $index = 0; //24
                $random = rand(0, 999);
                if($random > 209)
                    $index ++;  //60    
                if($random > 749)
                    $index ++;  //120
                if($random > 849)
                    $index ++;  //180
                if($random > 929)
                    $index ++;  //240
                if($random > 969)
                    $index ++;  //300
                if($random > 989)
                    $index ++;  //600
                    
                var_dump('getMO_index='.$index);
                $mo[] = $mo_v[$index];
                $mo_wpos[] = $i;
                $mo_t[] = 'v';
            }
            else {
                $mo[] = 0;
                $mo_t[] = 'r';
            }

            $i ++;
        }
        $SlotArea['mo'] = $mo;
        $SlotArea['mo_t'] = $mo_t;
        if(count($mo_wpos) > 0)
            $SlotArea['mo_wpos'] = $mo_wpos;
    }
}
