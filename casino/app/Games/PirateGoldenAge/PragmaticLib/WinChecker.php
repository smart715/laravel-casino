<?php

namespace VanguardLTE\Games\PirateGoldenAge\PragmaticLib;

class WinChecker
{
    private $paytable;
    private $paylines;
    private $multi;
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

        $this->multi = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
    }

    public function getWin($pur, $log, $bet, &$SlotArea){
        if(array_key_exists('multi', $SlotArea))
          $this->multi = $SlotArea['multi'];
        $slotArea = array_chunk($SlotArea['SlotArea'], 5);
        $lmi = [];
        $lmv = [];
        
        var_dump('1_1');
				$totalWin = 0;
        $winSymbols = [];
        foreach($this->paylines as $index => $payline){
            // fetch symbols which are in win line patterns from slot area
            $line = [];
            foreach($payline as $key => $value){
                $line[] = $slotArea[$value][$key];
            }
            // get the number of win symbols in the win line patterns
            $cnt = 1;
            $winSymbol = 0;
            $multiplier = 1;
            // if($log && array_key_exists('fsmul', $log))
            //   $multiplier = $log['fsmul'];
            foreach($line as $lineKey => $lineValue){
							if($lineKey == 0){
									$winSymbol = $lineValue;
							}
							else{
									if($lineValue != 2 && $winSymbol == 2)
											$winSymbol = $lineValue;
									if($winSymbol != 2 && $winSymbol != $lineValue && $lineValue != 2)
											break;
									$cnt ++;
							}
              $pos = $payline[$lineKey] * 5 + $lineKey;
              if($this->multi[$pos] > 1)
                $multiplier += $this->multi[$pos];
            }
            // calc the win amount according to the win symbol and number of symbols
            if($multiplier > 1)
              $multiplier -= 1;
						var_dump('winSymbol='.$winSymbol.' cnt='.$cnt.' multiplier='.$multiplier);
            $win = round($this->paytable[$winSymbol][count($this->paytable[$winSymbol]) - $cnt] * $bet * $multiplier, 2); // access the paytable element from the end
            // if the line is a win line then put the positions and win amount to an array
            if ($win > 0){
                $winPositions = [];
                foreach($line as $col => $lineValue){
                    if($col < $cnt)
                        $winPositions[] = $payline[$col] * 5 + $col;
                }
                $winSymbols[] = ['WinSymbol' => $winSymbol, 'CountSymbols' => $cnt, 'Pay' => number_format($win, 2, ".", ""), 'Positions' => $winPositions, 'l' => $index];
                if($multiplier > 1){
                  $lmi[] = $index;
                  $lmv[] = $multiplier;
                }
                $totalWin += $win;
            }
						var_dump('1_2_2_'.$index.'_'.$pur);
        }
				var_dump('1_3');
        if(count($lmi)){
          $SlotArea['lmi'] = implode(',', $lmi);
          $SlotArea['lmv'] = implode(',', $lmv);
        }

        // return an array with the total payout, the winning symbol, the positions of the winning symbol, the payout for that symbol
        return ['TotalWin' => number_format($totalWin, 2, ".", ""), 'WinLines' => $winSymbols];
		}

}
