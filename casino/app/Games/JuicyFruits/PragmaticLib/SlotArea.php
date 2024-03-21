<?php

namespace VanguardLTE\Games\JuicyFruits\PragmaticLib;

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
        if (false && $log && array_key_exists('state', $log) &&($log['state'] === 'respin' || $log['state'] === 'firstRespin')){
            // If you need a respin, then we work with the previous slotArea, shifting the symbols that have already won
            //Convert SlotArea to reels rows
            ///$currentSymbolsAfter = $log['SymbolsAfter'];
            var_dump('0_8');
            $currentSymbolsAfter = $symbolsAfter;
            foreach ($reels as $key => &$reel) { // add symbol from SymbolsAfter to coils
                array_push($reel, $currentSymbolsAfter[$key]);
            }
            $tmpSlotArea = array_chunk($log['s'], count($reels));
            $currentSlotArea = [];
            $k = 0;
            while ($k < count($reels)){ // rearrange from rows to rows
                $i = 0;
                while ($i < $gameSettings['sh']){
                    $currentSlotArea[$k][] = $tmpSlotArea[$i][$k];
                    $i++;
                }
                $k++;
            }
            var_dump('0_8_1');
            // get winning symbols into an array
            $winSymbols = [];
            if(array_key_exists('WinLines', $log))
                foreach ($log['WinLines'] as $winLine) {
                    $winSymbols[] = $winLine['WinSymbol'];
                }
            var_dump('0_8_2');
            // remove the winning symbols and sort the array so that the keys are in order after removal. Not 0,2,4 а 0,1,2
            $sortSlotArea = [];
            foreach ($currentSlotArea as $sortReelKey => $sortReel) {
                $sortSlotArea[$sortReelKey] = [];
                foreach ($sortReel as $value) {
                    if (!in_array($value, $winSymbols)) $sortSlotArea[$sortReelKey][] = $value; // place only non-winning symbols in the new playing field
                }
            }
            var_dump('0_8_3');
            // walk around the new playing field, and where there are not enough symbols in the row - add symbols from symbolsafter and reels to the beginning
            foreach ($sortSlotArea as $reelKey => &$currentReel) {
                $reelCount = count($currentReel);
                if ($reelCount < $gameSettings['sh']) { // if there are fewer symbols in the reel than it should be
                    $currentReel = array_merge( array_slice($reels[$reelKey], ($reelCount - $gameSettings['sh'])), $currentReel);
                }
            }
            
            var_dump('0_8_4');
            // create $symbolsBelow
            $symbolsBelow = [];
            foreach ($sortSlotArea as $item) {
                $symbolsBelow[] = $item[array_key_last($item)];
            }
            $symbolsAfter = [];
            foreach ($reels as $reelAndSymbolsAfter) {
                $symbolsAfter[] = $reelAndSymbolsAfter[array_key_first($reelAndSymbolsAfter)];
            }
            $reels = $sortSlotArea;
            var_dump('0_9');
        }

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

    public static function setWild($gameSettings, &$slotArea, $log){
        // set the variables for wild
        $is = $slotArea['SlotArea'];
        $accm = ['cp', 'tp', 'lvl', 'sc'];
        $acci = 0;
        $srf = [];
        $fsmore = 0;
        $trail = 0;
        if($log && array_key_exists('fs', $log) && array_key_exists('accv', $log)){
            $accv = $log['accv'];
            if(count($accm) > 4){
                unset($accm[4]);
                unset($accv[4]);
            }
        }
        else $accv = [0, 3, 1, 0];
        var_dump('!!!');
        $size = $accv[2];
        // if you're on free spin the probability of wild is 100% and the size is set according to the current lvl
        if($log && array_key_exists('fs', $log)){
            var_dump('!!!1');
            $rate = 1;
        }
        else if($log && !array_key_exists('fs', $log) && count(array_keys($slotArea['SlotArea'], 1)) >= 3) {
            var_dump('!!!2');
            $rate = 201;
        }
            // set the rate of the probability of wild 20%, size is 1 if you're on normal spin
        else {
            var_dump('!!!3');
            $rate = rand(1, 1000);
        }
        // let the wild heat
        if($rate <= 200){
            var_dump('!!!4');
            $pos = (rand(0, $gameSettings['sh'] - $size) * 5 + rand(0, 5 - $size));
            $i = 0;
            // change the slot area
            var_dump('size='.$size);
            do{
                $j = 0;
                do{
                    $elPos = $pos + $i * 5 + $j;
                    $srf[] = [$slotArea['SlotArea'][$elPos], 2, $elPos];   
                    $slotArea['SlotArea'][$elPos] = 2;
                    var_dump('elPos='.$elPos);
                    $j ++;
                }while($j < $size);
                $i ++;
            }while($i < $size);
        }
        // if scatter symbol comes out 
        if($log && array_key_exists('fs', $log) && count(array_keys($slotArea['SlotArea'], 1))){
            $accv[3] = count(array_keys($slotArea['SlotArea'], 1));
            $accv[0] += $accv[3];
            // when lvl up
            if($accv[0] > 2){
                $accv[0] -= 3;
                $accv[2] += 1;
                $accv[] = $accv[2] - 1;
                $accm[] = 'cl';
                $fsmore = rand(1, 3);
                $trail = [$accv[2], 5 - $accv[2]];
            }
        }
        $slotArea['fsmore'] = $fsmore;
        // set the is and srf
        var_dump('!!!5', $srf);
        if(count($srf)){
            $slotArea['srf'] = $srf;
            $slotArea['is'] = $is;
        }
        // set accm, acci, accv, fsmore
        if($log && array_key_exists('fs', $log)){
            $slotArea['accm'] = $accm;
            $slotArea['acci'] = $acci;
            $slotArea['accv'] = $accv;
            if($trail)
                $slotArea['trail'] = $trail;
        }
    }
}
