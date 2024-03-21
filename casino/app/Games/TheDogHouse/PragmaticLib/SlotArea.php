<?php

namespace VanguardLTE\Games\TheDogHouse\PragmaticLib;

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
        if($log && array_key_exists('fs', $log))
            $limit = 0;
        while($scatterCount > $limit){
            Redo:
            $slotArea[$scatterPositions[$i]] = ''.rand(4, 13);
            for($j = -2; $j <= 2; $j ++)
                if($scatterPositions[$i] + $j * 5 >= 0 && $scatterPositions[$i] + $j * 5 < 15 && $j != 0)
                    if($slotArea[$scatterPositions[$i]] == $slotArea[$scatterPositions[$i] * 1 + $j * 5])
                        goto Redo;
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

    public static function setMB(&$SlotArea, $log){
        $posset = [[1, 3, 4, 6, 8, 9, 11, 13, 14],
            [2, 3, 4, 7, 8, 9, 12, 13, 14]];
        $mbri = [1, 2, 3];
        $mbv = [];
        $mbp = [];
        $mbr = [];
        $i = 0;

        $wildPositions = array_keys($SlotArea['SlotArea'], 2);
        $isonreel = [0, 0];
        foreach($wildPositions as $pos){
            if($pos % 5 == 1)
                $isonreel[0] = 1;
            if($pos % 5 == 2)
                $isonreel[1] = 1;
        }
        if($isonreel[0] * $isonreel[1]){
            foreach($wildPositions as $pos){
                if($pos % 5 == 2){
                    RedoA:
                    $SlotArea['SlotArea'][$pos] = ''.rand(4, 13);
                    for($j = -2; $j <= 2; $j ++)
                        if($pos + $j * 5 >= 0 && $pos + $j * 5 < 15 && $j != 0)
                            if($SlotArea['SlotArea'][$pos] == $SlotArea['SlotArea'][$pos * 1 + $j * 5])
                                goto RedoA;
                }
            }
        }
        // Remove wilds which are not in suitable position
        if($log && array_key_exists('fs', $log) && $log['mbp'] != ''){
            $mbp = explode(',', $log['mbp']);
            $wildPositions = array_keys($SlotArea['SlotArea'], 2);
            $posset_index = 0;
            foreach($mbp as $pos){
                if(in_array($pos, [2, 7, 12])){
                    $posset_index = 2;
                    break;
                }
                if(in_array($pos, [1, 6, 11])){
                    $posset_index = 1;
                    break;
                }
            }
            if($posset_index){
                $posset = $posset[$posset_index - 1];
                foreach($wildPositions as $pos){
                    if(!in_array($pos, $posset)){
                        RedoB:
                        $SlotArea['SlotArea'][$pos] = ''.rand(4, 13);
                        for($j = -2; $j <= 2; $j ++)
                            if($pos + $j * 5 >= 0 && $pos + $j * 5 < 15 && $j != 0)
                                if($SlotArea['SlotArea'][$pos] == $SlotArea['SlotArea'][$pos * 1 + $j * 5])
                                    goto RedoB;
                    }
                }
            }
        }
        $mbp = [];

        if($log && array_key_exists('fs', $log))
            $SlotArea['ISlotArea'] = $SlotArea['SlotArea'];
        if($log && array_key_exists('fs', $log) && $log['mbp'] != ''){
            $lmbp = explode(',', $log['mbp']);
            $lmbv = explode(',', $log['mbv']);
            foreach($lmbp as $key => $value){
                $SlotArea['SlotArea'][$value] = 2;
                $mbp[] = $value;
                $mbv[] = $lmbv[$key];
            }
        }
        while($i < 3){
            $mbr[] = rand(2, 3);
            $i ++;
        }
        $i = 0;
        
        $slotArea = $SlotArea['SlotArea'];
        while($i < 15){
            if($slotArea[$i] == 2 && count(array_keys($mbp, $i)) == 0){
                $mbp[] = $i;
                $mbv[] = $mbr[$i % 5 - 1];
            }
            $i ++;
        }
        $SlotArea['mbri'] = $mbri;
        $SlotArea['mbv'] = $mbv;
        $SlotArea['mbp'] = $mbp;
        $SlotArea['mbr'] = $mbr;
    }

    public static function checkReels($slotArea){
        $slotArea = $slotArea['SlotArea'];
        $i = 0;
        while($i < 5){
            $one = $slotArea[$i];
            $two = $slotArea[$i + 5];
            $three = $slotArea[$i + 10];
            if($one == $two || $two == $three || $one == $three)
                return 0;
            $i ++;
        }
        return 1;
    }
}
