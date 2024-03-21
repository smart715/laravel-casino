<?php

namespace VanguardLTE\Games\FireHot5\PragmaticLib;

class WinChecker
{
    private $paytable;
    private $paylines;
    private $wild;
    public function __construct($gameSettings){
        $paytable = explode(';', $gameSettings['paytable']);
        $paylines = explode(';', $gameSettings['payline']);

        $this->paytable = [];
        foreach ($paytable as $item) {
            $this->paytable[] = explode(',', $item);
        }

        $this->paylines = [];
        foreach($paylines as $payline){
            $this->paylines[] = explode(',', $payline);
        }

        $this->wild = explode('~',$gameSettings['wilds'])[0];
    }

    public function getWin($pur, $log, $bet,$SlotArea){

        $slotArea = array_chunk($SlotArea['SlotArea'], 5);
        if(array_key_exists('SymbolsAfterExpanding', $SlotArea))
          $slotArea = array_chunk($SlotArea['SymbolsAfterExpanding'], 5);
        var_dump('1_1');
				$totalWin = 0;
        $winSymbols = [];
        foreach($this->paylines as $index => $payline){
						var_dump('1_2_1_'.$index);
            // fetch symbols which are in win line patterns from slot area
            $line = [];
            foreach($payline as $key => $value){
                $line[] = $slotArea[$value][$key];
            }
            // get the number of win symbols in the win line patterns
            $cnt = 1;
            $winSymbol = 0;
            foreach($line as $lineKey => $lineValue){
                if($lineKey == 0){
                    $winSymbol = $lineValue;
                }
                else{
                    if($lineValue != $this->wild && $winSymbol == $this->wild)
                        $winSymbol = $lineValue;
                    if($winSymbol != $this->wild && $winSymbol != $lineValue && $lineValue != $this->wild)
                        break;
                    $cnt ++;
                }
            }
            // calc the win amount according to the win symbol and number of symbols
						var_dump('1_2_1_'.$index.'_0');
						var_dump($winSymbol, $cnt);
            $win = round($this->paytable[$winSymbol][count($this->paytable[$winSymbol]) - $cnt] * $bet, 2); // access the paytable element from the end
						var_dump('1_2_1_'.$index.'_1');
            // if the line is a win line then put the positions and win amount to an array
            if ($win > 0){
                $winPositions = [];
                foreach($line as $col => $lineValue){
                    if($col < $cnt)
                        $winPositions[] = $payline[$col] * 5 + $col;
                }
								var_dump('1_2_1_'.$index.'_2');
                $winSymbols[] = ['WinSymbol' => $winSymbol, 'CountSymbols' => $cnt, 'Pay' => number_format($win, 2, ".", ""), 'Positions' => $winPositions, 'l' => $index];
								var_dump('1_2_1_'.$index.'_3');
                $totalWin += $win;
            }
						var_dump('1_2_2_'.$index.'_'.$pur);
        }
				var_dump('1_3');

        // return an array with the total payout, the winning symbol, the positions of the winning symbol, the payout for that symbol
        return ['TotalWin' => number_format($totalWin, 2, ".", ""), 'WinLines' => $winSymbols];
		}

}
