<?php

namespace VanguardLTE\Games\PandasFortune\PragmaticLib;

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

    public function getWin($pur, $log, $bet,&$SlotArea, $lines){

        $slotArea = array_chunk($SlotArea['SlotArea'], 5);
        
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
            }
            // calc the win amount according to the win symbol and number of symbols
						var_dump('1_2_1_'.$index.'_0');
						var_dump($winSymbol, $cnt);
            $win = round($this->paytable[$winSymbol][count($this->paytable[$winSymbol]) - $cnt] * $bet * $multiplier, 2); // access the paytable element from the end
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

                // check jackpot
                if(array_key_exists('gsf', $SlotArea)){
                  $gsf = $SlotArea['gsf'];
                  $gPos = [];
                  var_dump('gsf', $gsf);
                  foreach($gsf as $val)
                    $gPos[] = (int)explode('~', $val)[1];
                  var_dump('gPos', $gPos);
                  // $gPos = explode('~', $SlotArea['gsf'])[1];
                  if($cnt == 5 && in_array($winPositions[4], $gPos)){
                    // minor
                    if(array_count_values($line)[$winSymbol] < 5){
                      if(array_key_exists('rw', $SlotArea))
                        continue;
                      $SlotArea['rw'] = $bet * $lines * 25;
                      $SlotArea['gsf_a'] = $line[4].'~25';
                      $SlotArea['bw'] = 1;
                      $SlotArea['wp'] = 25;
                      $SlotArea['end'] = 1;
                      $SlotArea['coef'] = $bet * $lines;
                    }
                    // major
                    else if($winSymbol > 7 && $winSymbol < 14){
                      if(array_key_exists('rw', $SlotArea)){
                        if($SlotArea['rw'] < $bet * $lines * 200)
                          continue;
                        $totalWin -= $SlotArea['rw'];
                      }
                      $SlotArea['rw'] = $bet * $lines * 200;
                      $SlotArea['gsf_a'] = $line[4].'~200';
                      $SlotArea['bw'] = 1;
                      $SlotArea['wp'] = 200;
                      $SlotArea['end'] = 1;
                      $SlotArea['coef'] = $bet * $lines;
                    }
                    else {
                      if(array_key_exists('rw', $SlotArea)){
                        if($SlotArea['rw'] < $bet * $lines * 800)
                          continue;
                        $totalWin -= $SlotArea['rw'];
                      }
                      $SlotArea['rw'] = $bet * $lines * 800;
                      $SlotArea['gsf_a'] = $line[4].'~800';
                      $SlotArea['bw'] = 1;
                      $SlotArea['wp'] = 800;
                      $SlotArea['end'] = 1;
                      $SlotArea['coef'] = $bet * $lines;
                    }
                    $totalWin += $SlotArea['rw'];
                  }
                }
            }
						var_dump('1_2_2_'.$index.'_'.$pur);
        }
				var_dump('1_3');

        // return an array with the total payout, the winning symbol, the positions of the winning symbol, the payout for that symbol
        return ['TotalWin' => number_format($totalWin, 2, ".", ""), 'WinLines' => $winSymbols];
		}

}
