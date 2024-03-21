<?php

namespace VanguardLTE\Games\DayofDead\PragmaticLib;

class SlotArea
{
    public static function getSlotArea($gameSettings, $reelset, $log){

        // parse from the reelset settings, specify 1 or 0 depending on the RTP and increase the chances for a large bet.
        var_dump('0_3_'.$reelset);
        $reelset = explode('~', $gameSettings['reel_set'.$reelset]);
        foreach ($reelset as &$reel) { // convert the string to an array to make it more convenient to work
            $reel = explode(',', $reel);
        }
        var_dump('0_4');

        $positions = [];
        // get random coil positions
        SetReelAgain:
        foreach ($reelset as $key => $value) {
            $positions[$key] = rand(0, count($reelset[$key]));
        }
        var_dump('0_5');
        // fill the playing field with symbols
        $reels = [];
        $symbolsAfter = [];
        $symbolsBelow = [];
        var_dump('0_6');
        $wildCnt = 0;
        foreach ($positions as $key => $value) {
            // sh - number of visible symbols in one reel
            $reelsetCycled = array_merge($reelset[$key], array_slice($reelset[$key], 0, 10)); // loop coils
            $reels[$key] = array_slice($reelsetCycled, $value, $gameSettings['sh']); // Filling the Coils
            $wildCnt += count(array_keys($reels[$key], 14));
            if($wildCnt > 1)
                goto SetReelAgain;
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
        $limit = 0;
        while($scatterCount > $limit){
            Redo:
            $slotArea[$scatterPositions[$i]] = ''.rand(3, 7);
            for($j = -2; $j <= 2; $j ++)
                if($scatterPositions[$i] + $j * 5 >= 0 && $scatterPositions[$i] + $j * 5 < 15 && $j != 0)
                    if($slotArea[$scatterPositions[$i]] == $slotArea[$scatterPositions[$i] * 1 + $j * 5])
                        goto Redo;
            $scatterCount -= 1;
            $i += 1;
            // var_dump($scatterCount, $scatterPositions, $slotArea);
        }
        var_dump('0_10');

        // handle trail
        $rs = [];
        $trail = explode(';', $log['trail']);
        // if it is free spin now, delete all grave
        if($trail[0] != 'mode~base')
            foreach(array_keys($slotArea, 14) as $pos)
                $slotArea[$pos] = rand(2, 13);
        // if this is the fs
        if(count($trail) > 1 && $trail[1] == 'markers~fs_trig'){
            $rs['trail'] = 'mode~free;wild_bar~1;fs~1';
            $rs['wmt'] = 'pr2';
            $rs['wmv'] = 2;
            $rs['gwm'] = 2;
            $rs['puri'] = 0;
            if($wildCnt < 1)
                $slotArea[rand(0, $gameSettings['sh'] * 5)] = 14;
        }
        var_dump('!!!');
        // if this is the middle of the fs
        if(count($trail) > 1 && explode('~', $trail[1])[0] == 'wild_bar' && !(explode('~', $trail[1])[1] == 0 && (count(array_keys($log['s'], 14)) < 4))){
            $wild_bar = explode('~', $trail[1])[1];
            $fs = explode('~', $trail[2])[1];
            $fs += 1;
            var_dump('!!!');
            $rs['wmv'] = $log['wmv'];
            $rs['gwm'] = $log['gwm']; 
            // decide whether it's time to let the grave fall down
            if(($wild_bar > 0 && count(array_keys($log['s'], 14)) < 8) && 
            (count(array_keys($log['s'], 14)) > 3 && array_keys($log['s'], 14)[0] == 0 || rand(0, 1000) > 700)){
                $pos = rand(0, 4);
                if(count(array_keys($log['s'], 14)))
                    $pos = rand(array_keys($log['s'], 14)[0] + 1, 4);
                var_dump('downpos='.$pos);
                $slotArea[$pos] = 14;
                $rs['wmv'] = $log['wmv'] + 1;
                $rs['gwm'] = $log['gwm'] + 1; 
                $wild_bar -= 1;
            }
            var_dump('!!!');
            // decide whether it's time to add new grave
            foreach(array_keys($slotArea, 15) as $pos){
                if($pos % 5 == 4 || $pos % 5 < 4 && $log['s'][$pos + 1] != 14)
                    $wild_bar += 1;
            }
            var_dump('!!!');
            $rs['trail'] = implode(';', ['mode~free', 'wild_bar~'.$wild_bar, 'fs~'.$fs]);
            $rs['wmt'] = 'pr2';
            $rs['puri'] = 0;
        }

        // while you're on rs or fs, push those expanding wild reel to the left
        if($log && array_key_exists('fs', $log) || $log && array_key_exists('rs', $log)){
            // first, find out the expanding wild reel
            $reel = -1;
            $wildPos = array_keys($log['s'], 14);

            // and then push each expanding wild symbol to the left
            foreach($wildPos as $pos){
                if($pos % 5 != 0)
                    $slotArea[$pos - 1] = 14;
            }

            // set rs variables
            if(array_key_exists('rs', $log)){
                // check if it is the end of the rs
                if(count(array_keys($slotArea, 14)) < 4)
                    $rs['rs_t'] = $log['rs_p'] + 1;
                else {
                    $rs['rs'] = $log['rs'];
                    $rs['rs_p'] = $log['rs_p'] + 1;
                    $rs['rs_c'] = $log['rs_c'];
                    $rs['rs_m'] = $log['rs_m'];
                }
                if(array_key_exists('rs_win', $log))
                    $rs['rs_win'] = $log['rs_win'];
                else $rs['rs_win'] = 0;
            }
        }

        return array_merge(['SlotArea' => $slotArea,
            'SymbolsAfter' => $symbolsAfter,
            'SymbolsBelow' => $symbolsBelow,
            'ScatterCount' => $scatterCount
        ],
        $rs);

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

    public static function expandWilds($gameSettings, &$slotArea, $log){
        foreach($slotArea['SlotArea'] as $pos => $value){
            if($value == 14 && self::checkNewEW($gameSettings, $slotArea['SlotArea'], $pos)){
                // set is
                $slotArea['is'] = $slotArea['SlotArea'];
                
                // set ep
                $ep = [14, $pos, []];
                $pos %= 5;
                while($pos < $gameSettings['sh'] * 5){
                    $slotArea['SlotArea'][$pos] = 14;
                    $ep[2][] = $pos;
                    $pos += 5;
                }
                $ep[2] = implode(',', $ep[2]);
                $ep = implode('~', $ep);
                $slotArea['ep'] = $ep;
                var_dump('ep='.$slotArea['ep']);
                // check if it is rs and set rs
                if(!array_key_exists('rs', $slotArea)){
                    $slotArea['rs'] = 'mc';
                    $slotArea['rs_p'] = 0;
                    $slotArea['rs_c'] = 1;
                    $slotArea['rs_m'] = 1;
                    unset($slotArea['rs_t']);
                }
            }    
        }
    }

    private static function checkNewEW($gameSettings, $slotArea, $pos){
        var_dump('wildPos=', $pos);
        if($pos + 5 >= $gameSettings['sh'] * 5)
            $pos -= 5;
        else $pos += 5;
        var_dump('newPos=', $pos);
        return $slotArea[$pos] != 14;
    }
}
