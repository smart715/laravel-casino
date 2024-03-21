<?php

namespace VanguardLTE\Games\FishinReels\PragmaticLib;

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
            $slotArea[$scatterPositions[$i]] = ''.rand(4, 12);
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
    public static function getMO($gameSettings, &$SlotArea, $log, $pur){
        // handle accv
        if($log && array_key_exists('fs', $log)){
            if(array_key_exists('accv', $log))
                $accv = $log['accv'];
            else $accv = '1~4';
            $accv = explode('~', $accv);
            if($pur == '1' && $accv[0] < 4)
                $accv[0] += 1;
            $accv = implode('~', $accv);
            $SlotArea['accv'] = $accv;
            $SlotArea['accm'] = 'cp~mp';
        }

        // format variables to handle mo
        $slotArea = $SlotArea['SlotArea'];
        $mo_v = explode(',', $gameSettings['mo_v']);
        var_dump('getMO_mo_v='.implode(',', $mo_v));
        
        $mo = [];
        $mo_t = [];
        $mo_wpos = [];
        
        // iterate slotarea positions, set up mo, mo_t
        $i = 0;
        while($i < $gameSettings['sh'] * 5){
            if($slotArea[$i] == 13){
                // set the price of the money symbol
                $index = 0; //0
                $random = rand(0, 999);
                if($random > 310)
                    $index ++;  //10    
                if($random > 640)
                    $index ++;  //20
                if($random > 790)
                    $index ++;  //30
                if($random > 890)
                    $index ++;  //40
                if($random > 940)
                    $index ++;  //50
                if($random > 960)
                    $index ++;  //80
                if($random > 975)
                    $index ++;  //100
                if($random > 985)
                    $index ++;  //150
                if($random > 990)
                    $index ++;  //200
                if($random > 993)
                    $index ++;  //250
                if($random > 996)
                    $index ++;  //500
                if($random > 998)
                    $index ++;  //750
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
        var_dump('mo='.implode(',', $mo).' mo_t='.implode(',', $mo_t));
        if(count($mo_wpos) > 0)
            $SlotArea['mo_wpos'] = $mo_wpos;
    }

    public static function big_catch($gameSettings, &$SlotArea, $log){
        $rand = rand(1, 1000);
        if($rand < 200)
            $rand = 0;
        else if($rand >= 200 && $rand < 600)
            $rand = 1;
        else if($rand >= 600 && $rand < 900)
            $rand = 2;
        else $rand = 3;
        var_dump('!!!1');
        // if there is big catch
        if($rand > 2){
            $SlotArea['fsmore'] = 2;
            $ind = rand(0, 6);
            $SlotArea['ra2_awd_id'] = $ind;
            $SlotArea['ra2_avl_awd'] = '2~coin_mul~5~b;4~coin_mul~20~f;6~coin_mul~40~f;8~coin_mul~60~f;10~coin_mul~100~f;13~coin_mul~200~f;16~coin_mul~400~f;18~coin_mul~1000~gf';
            $trail = explode(';', $SlotArea['ra2_avl_awd'])[$ind];
            $trail = explode('~', $trail);
            $trail = 'bch_f1~'.implode(',', $trail);
            $SlotArea['trail'] = $trail;
        }
        var_dump('!!!2');
        // set other variables to output to the respond
        if($rand){
            $dsa = [];
            $dsam = [];
            $ds = [];
            while($rand){
                $dsa[] = 1;
                $dsam[] = 'v';
                $pos = rand(0, 14);
                while(count(array_keys($ds, $pos)) > 0)
                    $pos = rand(0, 14);
                $ds[] = $pos;
                $rand --;
            }
            foreach($ds as $ind => $val)
                $ds[$ind] = '15~'.$val;
            $SlotArea['dsa'] = implode(';', $dsa);
            $SlotArea['dsam'] = implode(';', $dsam);
            $SlotArea['ds'] = implode(';', $ds);
        }
        var_dump('!!!3');
    }
}
