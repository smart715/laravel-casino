<?php

namespace VanguardLTE\Games\SweetBonanza\PragmaticLib;

class SlotArea
{
    public static function getSlotArea($gameSettings, $reelset, $log){
        // распарсить из настроек reelset, указать 1 или 0 в зависимости от RTP и повышения шансов за большую ставку.
        $reelset = explode('~', $gameSettings['reel_set'.$reelset]);
        foreach ($reelset as &$reel) { // переводим строку в массив чтобы было удобнее работать
            $reel = explode(',', $reel);
        }

        $positions = [];
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
            $winSymbols = [];
            foreach ($log['WinLines'] as $winLine) {
                $winSymbols[] = $winLine['WinSymbol'];
            }
            // удалить выигрышные символы и отсортировать массив чтобы ключи после удаления шли по порядку. Не 0,2,4 а 0,1,2
            $sortSlotArea = [];
            foreach ($currentSlotArea as $sortReelKey => $sortReel) {
                $sortSlotArea[$sortReelKey] = [];
                foreach ($sortReel as $value) {
                    if (!in_array($value, $winSymbols)) $sortSlotArea[$sortReelKey][] = $value; // поместить в новое игровое поле только не выигрышные символы
                }
            }
            // пройтись по новому игровому полю, и там где не хватает символов в ряду - добавить в начало символы из symbolsafter и reels
            foreach ($sortSlotArea as $reelKey => &$currentReel) {
                $reelCount = count($currentReel);
                if ($reelCount < $gameSettings['sh']) { // если в катушке меньше символов чем должно быть
                    $currentReel = array_merge( array_slice($reels[$reelKey], ($reelCount - $gameSettings['sh'])), $currentReel);
                }
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

        return ['SlotArea' => $slotArea,
            'SymbolsAfter' => $symbolsAfter,
            'SymbolsBelow' => $symbolsBelow
        ];

        // если это респин - то подгрузить из лога прошлое состояние игрового поля, удалить оттуда выигрышные символы и опустить символы вверху на низ
        //if ($log && in_array('rs=t', $log)) $slotArea = '';
        // если респина нет - то генерируем позиции остановки и собираем игровое поле из выпавших символов, а так же символы до и после

    }

}
