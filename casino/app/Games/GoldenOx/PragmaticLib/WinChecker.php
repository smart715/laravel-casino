<?php

namespace VanguardLTE\Games\GoldenOx\PragmaticLib;

class WinChecker
{
    private $paytable;
    private $paylines;
    private $wild;
    private $scatter;
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
        $this->scatter = explode('~',$gameSettings['scatters'])[0];
    }

    public function getWin($pur, $log, $bet,$slotArea){
        var_dump('1_1_SA='.implode(',',$slotArea['SlotArea']));
        if($pur == '2')
          $bet = 0;
        foreach($slotArea['SlotArea'] as $pos => $val)
          if($val == 15 || $val == 16)
            $slotArea['SlotArea'][$pos] = 2;
        $slotArea = array_chunk($slotArea['SlotArea'], 5);
        // =================== Free Spin Slot Area Begin ===================
        $fsSlotArea = $slotArea;
				$fswin = 0;
				$fss = [];
				$msPositions = [];
				$rmsPositions = [];
				var_dump('1_0');
        if($log && array_key_exists('fs', $log) && array_key_exists('ms', $log)){
					$fss = array_merge($fsSlotArea[0], $fsSlotArea[1]);
					$fss = array_merge($fss, $fsSlotArea[2]);
					// var_dump('1_0_1', $log);
					// Get the mSymbol positions
					foreach($fss as $key => $value){
						// var_dump($key, $log['ms']);
						if($value == $log['ms']){
							array_push($msPositions, $key);
							for($i = -2; $i < 2; $i ++){
								if($key + $i * 5 >= 0 && $key + $i * 5 < 15 && $i != 0)
									array_push($rmsPositions, $key + $i * 5);
							}
						}
					}
					var_dump('1_0_2');

					// replace the symbols with MSymbol
					for($i = 0; $i < 3; $i ++)
						for($j = 0; $j < 5; $j ++)
							if($fsSlotArea[$i][$j] == $log['ms']){
								$fsSlotArea[0][$j] = $log['ms'];
								$fsSlotArea[1][$j] = $log['ms'];
								$fsSlotArea[2][$j] = $log['ms'];
							}
					var_dump('1_0_3');
					
					$fss = array_merge($fsSlotArea[0], $fsSlotArea[1]);
					$fss = array_merge($fss, $fsSlotArea[2]);
				}
        // =================== Free Spin Slot Area End ===================
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
						var_dump($winSymbol, $cnt, $bet);
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
            // ===================  FS win calculation Begin ===================
            if($log && array_key_exists('fs', $log) && array_key_exists('ms', $log)){
							var_dump('1_2_3_'.$index);
							$fsLine = [];
							foreach($payline as $key1 => $value1){
								$fsLine[] = $fss[$value1 * 5 + $key1];
							}
							// var_dump($fsLine);
							$fsWinSymbols = array_count_values($fsLine);
							$cnt = array_key_exists($log['ms'], $fsWinSymbols) ? $fsWinSymbols[$log['ms']] : 1;	
							$win = round($this->paytable[$log['ms']][count($this->paytable[$log['ms']]) - $cnt] * $bet, 2);
							var_dump($fswin, $win);
							$fswin += $win;
            }
            // ===================  FS win calculation End ===================
        }
				var_dump('1_3');

        // return an array with the total payout, the winning symbol, the positions of the winning symbol, the payout for that symbol
        if(!$fswin)
					return ['TotalWin' => number_format($totalWin, 2, ".", ""), 'WinLines' => $winSymbols];
				else 
					return ['TotalWin' => number_format($totalWin, 2, ".", ""), 
						'WinLines' => $winSymbols,
						'fswin' => $fswin,
						'msPositions' => $msPositions,
						'rmsPositions' => $rmsPositions,
						'mes' => $fss];
		}

}
