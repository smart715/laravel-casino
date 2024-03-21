<?php

namespace VanguardLTE\Games\WildBeachParty\PragmaticLib;

class SlotArea
{
    public static function getSlotArea($gameSettings, $reelset, $log){
        // распарсить из настроек reelset, указать 1 или 0 в зависимости от RTP и повышения шансов за большую ставку.
        $reelset = explode('~', $gameSettings['reel_set'.$reelset]);
        foreach ($reelset as &$reel) { // переводим строку в массив чтобы было удобнее работать
            $reel = explode(',', $reel);
        }

        $positions = [];
        $nmvList = [];
        $nmvListTemp = [];
        // получить рандомно позиции катушек
        foreach ($reelset as $key => $value) {
            $positions[$key] = rand(0, count($reelset[$key]));
        }
        // заполнить игровое поле символами
        $reels = [];
        $symbolsAfter = [];
        $symbolsBelow = [];
        foreach ($positions as $key => $value) {
            // sh - количество видимых символов в одной катушке
            $reelsetCycled = array_merge($reelset[$key], array_slice($reelset[$key], 0, 10)); // зациклить катушки
            $reels[$key] = array_slice($reelsetCycled, $value, $gameSettings['sh']); // Заполняем катушки
            $symbolsAfter[$key] = implode('', array_slice($reelsetCycled, $value - 1, 1));
            $symbolsBelow[$key] = $reels[$key][array_key_last($reels[$key])];
        }
        $nmp = false;
        if($log && array_key_exists('trail', $log)){
            $trail = explode(';', $log['trail']);
            $nmp = explode('~', $trail[0])[1];
            $nmp = explode(',', $nmp);
            $nmv = explode('~', $trail[1])[1];
            $nmv = explode(',', $nmv); 
        }
        if ($log && ($log['State'] === 'Respin' || $log['State'] === 'FirstRespin')){
            // Если нужен респин - то работаем с предыдущей slotArea, смещая уже выигравшие символы
            //SlotArea переделать в reels ряды
            ///$currentSymbolsAfter = $log['SymbolsAfter'];
            $currentSymbolsAfter = $symbolsAfter;
            foreach ($reels as $key => &$reel) { // добавить к катушкам символ из SymbolsAfter
                array_push($reel, $currentSymbolsAfter[$key]);
            }
            $tmpSlotArea = array_chunk($log['SlotArea'], count($reels));
            $currentSlotArea = [];
            $k = 0;
            while ($k < count($reels)){ // перестроить со строк на ряды
                $i = 0;
                while ($i < $gameSettings['sh']){
                    $currentSlotArea[$k][] = $tmpSlotArea[$i][$k];
                    $i++;
                }
                $k++;
            }
            // получить в массив выигрышные символы
            $winPositions = [];
            foreach ($log['WinLines'] as $winLine) {
                $winPositions = array_merge($winPositions, $winLine['Positions']);
            }
            // удалить выигрышные символы и отсортировать массив чтобы ключи после удаления шли по порядку. Не 0,2,4 а 0,1,2
            $down = [];
            $sortSlotArea = [];
            foreach ($currentSlotArea as $sortReelKey => $sortReel) {
                $sortSlotArea[$sortReelKey] = [];
                $down[$sortReelKey] = [0,0,0,0,0,0,0];
                foreach ($sortReel as $valueKey => $value) {
                    if($nmp && in_array($valueKey * $gameSettings['sh'] + $sortReelKey, $nmp)){
                        $nmvIndex = array_keys($nmp, $valueKey * $gameSettings['sh'] + $sortReelKey)[0];
                        $nmvListTemp[$valueKey * $gameSettings['sh'] + $sortReelKey] = $nmv[$nmvIndex];
                        $sortSlotArea[$sortReelKey][] = 2;
                    }
                    else if (!in_array($valueKey * $gameSettings['sh'] + $sortReelKey, $winPositions) && $value != 2)
                        $sortSlotArea[$sortReelKey][] = $value; // поместить в новое игровое поле только не выигрышные символы
                    else {
                        $cnt = $valueKey - 1;
                        while($cnt >= 0){
                            $down[$sortReelKey][$cnt] ++;
                            $cnt --;
                        }
                    }
                }
            }
            // пройтись по новому игровому полю, и там где не хватает символов в ряду - добавить в начало символы из symbolsafter и reels
            foreach ($sortSlotArea as $reelKey => &$currentReel) {
                $reelCount = count($currentReel);
                if ($reelCount < $gameSettings['sh']) { // если в катушке меньше символов чем должно быть
                    $currentReel = array_merge( array_slice($reels[$reelKey], ($reelCount - $gameSettings['sh'])), $currentReel);
                }
                foreach($nmvListTemp as $pos => $val)
                    if($pos % 7 == $reelKey)
                        $nmvList[$pos + 7 * $down[$pos % 7][$pos / 7]] = $val;
            }
            // создать $symbolsBelow
            $symbolsBelow = [];
            foreach ($sortSlotArea as $item) {
                $symbolsBelow[] = $item[array_key_last($item)];
            }
            $symbolsAfter = [];
            foreach ($reels as $reelAndSymbolsAfter) {
                $symbolsAfter[] = $reelAndSymbolsAfter[array_key_first($reelAndSymbolsAfter)];
            }
            $reels = $sortSlotArea;
        }

        // сложить все символы в массив чтобы вычислить количество выигрышей
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
        $stf = '';
        if($log && array_key_exists('trail', $log)){
            foreach($nmp as $ind => $pos){
                // $slotArea[$pos] = 2;
                // $nmvList[$pos] = $nmv[$ind];
                if($stf != '')
                    $stf = $stf.';';
                $stf = $stf.''.$log['SlotArea'][$pos].'~2~'.$pos;
            }
        }

        if($stf == '')
            return ['SlotArea' => $slotArea,
                'SymbolsAfter' => $symbolsAfter,
                'SymbolsBelow' => $symbolsBelow,
                'nmvList' => $nmvList,
            ];
        else return ['SlotArea' => $slotArea,
                'SymbolsAfter' => $symbolsAfter,
                'SymbolsBelow' => $symbolsBelow,
                'nmvList' => $nmvList,
                'stf' => $stf
            ];
        if(count($nmvList)){
            var_dump('nmvListTemp='.implode(',', $nmvListTemp));
            var_dump('nmvList='.implode(',', $nmvList));
        }
        // если это респин - то подгрузить из лога прошлое состояние игрового поля, удалить оттуда выигрышные символы и опустить символы вверху на низ
        //if ($log && in_array('rs=t', $log)) $slotArea = '';
        // если респина нет - то генерируем позиции остановки и собираем игровое поле из выпавших символов, а так же символы до и после

    }

    public static function setMulti(&$slotArea){
        $multiCnt = 0;
        $rand = rand(0, 100);
        if($rand < 1)
            $multiCnt = rand(11, 15);
        else if($rand < 6)
            $multiCnt = rand(6, 10);
        else if($rand < 20)
            $multiCnt = rand(1, 5);
        $slm_mp = [];
        $slm_mv = [];
        while($multiCnt){
            $pos = rand(0, 48);
            while(count(array_keys($slm_mp, $pos)))
                $pos = rand(0, 48);
            $slm_mp[] = $pos;
            $slm_mv[] = 2;
            $multiCnt --;
        }
        if(count($slm_mp)){
            $slotArea['slm_mp'] = $slm_mp;
            $slotArea['slm_mv'] = $slm_mv;
        }
    }

}
