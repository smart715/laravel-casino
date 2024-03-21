<?php

namespace VanguardLTE\Games\ExtraJuicy\PragmaticLib;

class WinChecker
{
    private $paytable;
    private $paylines;
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
    }

    public function getWin($pur, $log, $bet,$slotArea){

        $slotArea = array_chunk($slotArea['SlotArea'], 5);
        
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
            $multiplier = 1;
            $winSymbol = 0;
            $winCnt = 0;
            $win = 0;
            $winStPos = 0;
            $currentSymbol = 0;
            $currentWin = 0;
            $currentStPos = 0;
            if($log && array_key_exists('prg', $log))
                $multiplier = $log['prg'][0] + 1;
            foreach($line as $lineKey => $lineValue){
							if($lineKey == 0){
									$currentSymbol = $lineValue;
							}
							else{
									if($currentSymbol != $lineValue){
                    $currentWin = round($this->paytable[$currentSymbol][count($this->paytable[$currentSymbol]) - $cnt] * $bet * $multiplier, 2); // access the paytable element from the end
                    if($currentWin > $win){
                      $winSymbol = $currentSymbol;
                      $win = $currentWin;
                      $winCnt = $cnt;
                      $winStPos = $currentStPos;
                    }
                    $cnt = 0;
                    $currentSymbol = $lineValue;
                    $currentWin = 0;
                    $currentStPos = $lineKey;
                  }
									$cnt ++;
							}
            }
            // calc the win amount according to the win symbol and number of symbols
						var_dump('1_2_1_'.$index.'_0');
						var_dump($winSymbol, $winCnt);
						var_dump('1_2_1_'.$index.'_1');
            // if the line is a win line then put the positions and win amount to an array
            if ($win > 0){
                $winPositions = [];
                foreach($line as $col => $lineValue){
                    if($col < $winCnt)
                        $winPositions[] = $payline[$col + $winStPos] * 5 + $col + $winStPos;
                }
								var_dump('1_2_1_'.$index.'_2');
                $winSymbols[] = ['WinSymbol' => $winSymbol, 'CountSymbols' => $winCnt, 'Pay' => number_format($win, 2, ".", ""), 'Positions' => $winPositions, 'l' => $index];
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
