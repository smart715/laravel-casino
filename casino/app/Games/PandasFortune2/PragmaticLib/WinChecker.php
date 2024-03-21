<?php

namespace VanguardLTE\Games\PandasFortune2\PragmaticLib;

class WinChecker
{
    private $paytable;
    private $paylines;
    private $gpaytable;
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

        $this->gpaytable = [5,10,15,20,25,50,75,100,150,200,250,500,1000,2500,4998];
    }

    public function getWin($pur, $log, $bet,&$SlotArea, $lines){
        // check jackpot
        if(array_key_exists('gsf', $SlotArea)){
          $gsf = $SlotArea['gsf'];
          $gPos = [];
          var_dump('gsf', $gsf);
          foreach($gsf as $val)
            $gPos[] = (int)explode('~', $val)[1];
          var_dump('gPos', $gPos);
          // $gPos = explode('~', $SlotArea['gsf'])[1];
        }

        $slotArea = array_chunk($SlotArea['SlotArea'], 5);
        $rw = 0;
        $gsf_a = [];
        $wp = 0;
        
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
                $wg = [];
                $winPositions = [];
                foreach($line as $col => $lineValue){
                    if($col < $cnt){
                      $winPositions[] = $payline[$col] * 5 + $col;
                      if(in_array($payline[$col] * 5 + $col, $gPos))
                        $wg[] = $SlotArea['SlotArea'][$payline[$col] * 5 + $col];
                    }
                }
                var_dump('1_2_1_'.$index.'_2');
                $winSymbols[] = ['WinSymbol' => $winSymbol, 'CountSymbols' => $cnt, 'Pay' => number_format($win, 2, ".", ""), 'Positions' => $winPositions, 'l' => $index];
                var_dump('1_2_1_'.$index.'_3');
                $totalWin += $win;
                
                // check the win for golden symbols
                if(count($wg)){
                  if($cnt == 5){
                    $multiIndex = self::getMultiIndex();
                    $rw += $bet * $lines * $this->gpaytable[$multiIndex];
                    $gsf_a[] =  $wg[0].'~'.$this->gpaytable[$multiIndex];
                    $wp += $this->gpaytable[$multiIndex];
                  }
                  else if($cnt == 3 || $cnt == 4){
                    if(in_array(2, $wg)){
                      $rw += $bet * $lines * 3;
                      $gsf_a[] =  $wg[0].'~3';
                      $wp += 3;
                    }
                    else if($winSymbol < 8){
                      $rw += $bet * $lines * 2;
                      $gsf_a[] =  $wg[0].'~2';
                      $wp += 2;
                    }
                    else if($winSymbol > 7){
                      $rw += $bet * $lines * 1;
                      $gsf_a[] =  $wg[0].'~1';
                      $wp += 1;
                    }
                  }
                }
            }
						var_dump('1_2_2_'.$index.'_'.$pur);
        }
				var_dump('1_3');
        // set golden win
        if($rw){
          $SlotArea['rw'] = $rw;
          $SlotArea['gsf_a'] = $gsf_a;
          $SlotArea['bw'] = 1;
          $SlotArea['wp'] = $wp;
          $SlotArea['end'] = 1;
          $SlotArea['coef'] = $bet * $lines;
          $totalWin += $rw;
        }
        // return an array with the total payout, the winning symbol, the positions of the winning symbol, the payout for that symbol
        return ['TotalWin' => number_format($totalWin, 2, ".", ""), 'WinLines' => $winSymbols];
		}

    public static function getMultiIndex(){
      // set the price of the money symbol
      $index = 0; //5
      $random = rand(0, 999);
      if($random > 310)
          $index ++;  //10   
      if($random > 640)
          $index ++;  //15
      if($random > 790)
          $index ++;  //20
      if($random > 880)
          $index ++;  //25
      if($random > 930)
          $index ++;  //50
      if($random > 945)
          $index ++;  //75
      if($random > 960)
          $index ++;  //100
      if($random > 970)
          $index ++;  //150
      if($random > 975)
          $index ++;  //200
      if($random > 980)
          $index ++;  //250
      if($random > 985)
          $index ++;  //500
      if($random > 990)
          $index ++;  //1000
      if($random > 995)
          $index ++;  //2500
      if($random > 998)
          $index ++;  //4998
      return $index;
    }
}
