<?php

namespace VanguardLTE\Games\GatesofOlympus\PragmaticLib;

class WinChecker
{
    private $paytable;
    public function __construct($gameSettings){
        $paytable = explode(';', $gameSettings['paytable']);
        $this->paytable = [];
        foreach ($paytable as $item) {
            $this->paytable[] = explode(',', $item);
        }
    }

    public function getWin($bet,$slotArea){
        $allWinSymbols = array_count_values($slotArea['SlotArea']); // поместить в ключи количество символов одинаковых в поле

// добавить выплаты
        $winSymbols = [];
        $totalWin = 0;
        foreach ($allWinSymbols as $key => $value) {
            $win = round($this->paytable[$key][count($this->paytable[$key]) - $value] * $bet, 2); // получить доступ к элементу платежной таблицы с конца
            if ($win > 0){
                $winPositions = array_keys($slotArea['SlotArea'], $key);
                $winSymbols[] = ['WinSymbol' => $key, 'CountSymbols' => $value, 'Pay' => $win, 'Positions' => $winPositions];
                $totalWin += $win;
            }
        }

        // вернуть массив с общей суммой выплаты, выигрышным символом, положениями выигрышного символа, оплатой за этот символ
        return ['TotalWin' => $totalWin, 'WinLines' => $winSymbols];
    }

}
