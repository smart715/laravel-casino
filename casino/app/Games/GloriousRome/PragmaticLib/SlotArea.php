<?php

namespace VanguardLTE\Games\GloriousRome\PragmaticLib;

class SlotArea
{
    public static function getSlotArea($gameSettings, $reelset, $log){
        // parse from the reelset settings, specify 1 or 0 depending on the RTP and increase the chances for a large bet.
        var_dump('0_3');
        $reelset = [];
        $reelset[] = $gameSettings['reel0'];
        $reelset[] = $gameSettings['reel1'];
        $reelset[] = $gameSettings['reel2'];
        $reelset[] = $gameSettings['reel3'];
        $reelset[] = $gameSettings['reel4'];
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
        // Limit wilds
        $wildPositions = array_keys($slotArea, $wild);
        $wildCount = count($wildPositions);
        $i = 0;
        $limit = 1;
        while($wildCount > $limit){
            $slotArea[$wildPositions[$i]] = ''.rand(4, 11);
            $wildCount -= 1;
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
}
