<?php

namespace VanguardLTE\Games\BookofTutRespin\PragmaticLib;

class SlotArea
{
    public static function getSlotArea($gameSettings, $reelset, $log){
        // parse from the reelset settings, specify 1 or 0 depending on the RTP and increase the chances for a large bet.
        var_dump('0_3');
        $reelset = explode('~', $gameSettings['reel_set'.$reelset]);
        foreach ($reelset as &$reel) { // convert the string to an array to make it more convenient to work
            $reel = explode(',', $reel);
        }
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
        $limit = 0;
        while($scatterCount > $limit){
            Redo:
            $slotArea[$scatterPositions[$i]] = ''.rand(7, 11);
            for($j = -2; $j <= 2; $j ++)
                if($scatterPositions[$i] + $j * 5 >= 0 && $scatterPositions[$i] + $j * 5 < 15 && $j != 0)
                    if($slotArea[$scatterPositions[$i]] == $slotArea[$scatterPositions[$i] * 1 + $j * 5])
                        goto Redo;
            var_dump('replaced_item_pos='.$scatterPositions[$i].'_value='.$slotArea[$scatterPositions[$i]]);
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

    public static function setStf(&$slotArea, $gameSettings, $log){
        // if we're currently on respin
        if($log && array_key_exists('rs_c', $log)){
            // fetch ms from previous log
            $ms = $log['trail'];
            $slotArea['trail'] = $ms;
            var_dump('ms='.$ms);

            // first assign the previous ms area of previous log to the current slot area
            $prevMsPos = [];
            foreach($log['stf'] as $stf){
                $slotArea['SlotArea'][$stf[2]] = $ms;
                $prevMsPos[] = $stf[2];
            }
            var_dump('prevMsPos='.implode(',', $prevMsPos));

            $newMsCnt = 0;
            // if there are more ms on current slot area
            if(count(array_keys($slotArea['SlotArea'], $ms)) > count($log['stf'])){
                var_dump('msCnt='.count(array_keys($slotArea['SlotArea'], $ms)));
                $slotArea['is'] = $slotArea['SlotArea'];
                $stf = [];
                $msPos = array_keys($slotArea['SlotArea'], $ms);
                foreach($msPos as $pos){
                    // set assign mystery symbol to the the symbols of the reels which contain new mystery symbol 
                    if(!count(array_keys($prevMsPos, $pos))){
                        var_dump('pos='.$pos);
                        $newMsCnt ++;
                        $reel = $pos % 5;
                        $i = 0;
                        while($i < $gameSettings['sh']){
                            $stf[] = [$slotArea['SlotArea'][$reel + $i * 5], $ms, $reel + $i * 5];
                            $slotArea['SlotArea'][$reel + $i * 5] = $ms;
                            $i ++;
                        }
                    }
                }
                $slotArea['stf'] = $stf;
            }
            // if the slot area is full of ms 
            if(count(array_keys($slotArea['SlotArea'], $ms)) == 5 * $gameSettings['sh'] || !$newMsCnt){
                $slotArea['rs_t'] = $log['rs_m'];
            }
            else {
                $slotArea['rs_c'] = $log['rs_c'] + 1;
                $slotArea['rs_m'] = $log['rs_m'] + 1;
                $slotArea['rs_p'] = $log['rs_p'] + 1;
            }
        }
        // set rtf at the ratio of 20%
        else if(rand(1, 1000) <= 200 || $log && array_key_exists('fs', $log)){
            // select mystery symbol randomly
            var_dump('RS_1');
            if($log && array_key_exists('fs', $log))
                $ms = $log['trail'];
            else $ms = rand(3, 11);
            var_dump('RS_2');
            $slotArea['trail'] = $ms;
            var_dump('RS_3');
            
            // see if there is win
            $msCnt = 0;
            if(array_key_exists($ms, array_count_values($slotArea['SlotArea'])))
            $msCnt = array_count_values($slotArea['SlotArea'])[$ms];
            var_dump('RS_4');
            
            // if there is win
            if($msCnt > 2){
                $slotArea['is'] = $slotArea['SlotArea'];
                $stf = [];
                $msPos = array_keys($slotArea['SlotArea'], $ms);
                var_dump('RS_5');
                if($msCnt == 5)
                $slotArea['rs_t'] = 1;
                else {
                    $slotArea['rs_c'] = 1;
                    $slotArea['rs_m'] = 1;
                    $slotArea['rs_p'] = 0;
                }
                var_dump('RS_6');
                // set assign mystery symbol to the the symbols of the reels which contain mystery symbol 
                foreach($msPos as $pos){
                    $reel = $pos % 5;
                    $i = 0;
                    var_dump('RS_7');
                    while($i < $gameSettings['sh']){
                        $stf[] = [$slotArea['SlotArea'][$reel + $i * 5], $ms, $reel + $i * 5];
                        $slotArea['SlotArea'][$reel + $i * 5] = $ms;
                        $i ++;
                    }
                    var_dump('RS_8');
                }
                $slotArea['stf'] = $stf;
                var_dump('RS_9');
            }
        }
    }
}
