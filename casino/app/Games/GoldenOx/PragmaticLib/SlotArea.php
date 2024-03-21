<?php

namespace VanguardLTE\Games\GoldenOx\PragmaticLib;

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

    public static function getMsCnt($slotArea){
        return count(array_keys($slotArea, 11));
    }

    public static function makeFullStack(&$slotArea){
        $cnt = 6 - count(array_keys($slotArea['SlotArea'], 11));
        while($cnt){
            $pos = rand(0, 14);
            while($slotArea['SlotArea'][$pos] == 11)
                $pos = rand(0, 14);
            $slotArea['SlotArea'][$pos] = 11;
            $cnt --;
        }
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
            if($slotArea[$i] == 11){
                // set the price of the money symbol
                $index = 0; //40
                $random = rand(0, 999);
                if($random > 310)
                    $index ++;  //80   
                if($random > 640)
                    $index ++;  //120
                if($random > 790)
                    $index ++;  //160
                if($random > 880)
                    $index ++;  //200
                if($random > 930)
                    $index ++;  //240
                if($random > 945)
                    $index ++;  //280
                if($random > 960)
                    $index ++;  //320
                if($random > 970)
                    $index ++;  //400
                if($random > 975)
                    $index ++;  //560
                if($random > 980)
                    $index ++;  //640
                if($random > 985)
                    $index ++;  //720
                if($random > 990)
                    $index ++;  //800
                if($random > 995)
                    $index ++;  //800
                if($random > 998)
                    $index ++;  //800
                if($random > 999)
                    $index ++;  //800
                var_dump('getMO_index='.$index);
                $mo[] = $mo_v[$index];
                $mo_wpos[] = $i;
                if($index == 14)
                    $mo_t[] = 'jp3';
                else if($index == 15)
                    $mo_t[] = 'jp2';
                else $mo_t[] = 'v';
            }
            else {
                $mo[] = 0;
                $mo_t[] = 'r';
            }

            $i ++;
        }
        if(count(array_keys($mo, 0)) < 15){
            $SlotArea['mo'] = $mo;
            $SlotArea['mo_t'] = $mo_t;
        }
        var_dump('mo='.implode(',', $mo).' mo_t='.implode(',', $mo_t));
        if(count($mo_wpos) > 0)
            $SlotArea['mo_wpos'] = $mo_wpos;
    }

    public static function setGiantSymbol($gameSettings, &$slotArea, $log){
        $s_counts = array_count_values($slotArea['SlotArea']);
        $max = max($s_counts);
        $max_s = array_keys($s_counts, $max)[0];
        if($max_s == 11 || $max_s == 2)
            $max_s = rand(2, 10);
        foreach($slotArea['SlotArea'] as $ind => $val){
            $reel = $ind % 5;
            if($reel > 0 && $reel < 4)
                $slotArea['SlotArea'][$ind] = $max_s;
        }
    }
}
