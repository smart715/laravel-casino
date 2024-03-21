<?php

namespace VanguardLTE\Games\FruitParty2\PragmaticLib;

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

    public function getWin($bet, &$slotArea, $log){
        // add payouts
        $nmp = [];
        $nmv = [];
        $wildPos = [];
        $winSymbols = [];
        $totalWin = 0;
        $isIncluded =  array_fill_keys(array_keys($slotArea['SlotArea']), 0);
        $groups = [];
        $slm_mv = [];
        $slm_mp = [];
        $slm_lmv = [];
        $slm_lmi = [];
        self::getGroup($slotArea['SlotArea'], $isIncluded, $groups, 0, 0, $slotArea['nmvList']);
        $isMulti = rand(0, 100);
        foreach ($groups as $group) {
            // if($isMulti < 40 || count($group['nmp'])){
                if(count($group['nmp'])){
                    foreach($group['nmp'] as $ind => $val){
                        if(!in_array($val, $slm_mp)){
                            $slm_mv[] = $group['nmv'][$ind];
                            $slm_mp[] = $val;
                        }
                        $group['multi'] += $group['nmv'][$ind];
                    }
                    if(count($group['pos']) > 4){
                        $slm_lmi[] = count($slm_lmi);
                        if($group['multi'] > 1)
                            $group['multi'] -= 1;
                        $slm_lmv[] = $group['multi'];
                    }
                }
            //     else if(count($group['pos']) > 4 ){
            //         foreach($group['pos'] as $pos)
            //             if(rand(0, 100) < 40){
            //                 $slm_mv[] = 2;
            //                 $slm_mp[] = $pos;
            //                 $group['multi'] *= 2;
            //             }
            //         if($group['multi'] > 1){
            //             $slm_lmv[] = $group['multi'];
            //             $slm_lmi[] = count($slm_lmi);
            //         }
            //     }
            // }
            $win = round($this->paytable[$group['symbol']][count($this->paytable[$group['symbol']]) - count($group['pos'])] * $bet * $group['multi'], 2); // access the paytable element from the end
            if ($win > 0){
                $winPositions = $group['pos'];
                // if there is wild in further spin
                $limit = 50;
                if(count($group['nmp']))
                    $limit = 80;
                if(rand(0, 100) < $limit){
                    $pos = $winPositions[rand(0, count($winPositions) - 1)];
                    $newV = 1;
                    if(count($group['nmp'])){
                        $newV = $group['nmv'][0];
                        $pos = $group['nmp'][0];
                    }
                    if(array_key_exists('FreeSpinNumber', $log))
                        $newV *= 3;
                    else    $newV *= 2;
                    $nmp[] = $pos;
                    $nmv[] = $newV;
                }
                $winSymbols[] = ['WinSymbol' => $group['symbol'], 'CountSymbols' => count($group['pos']), 'Pay' => $win, 'Positions' => $winPositions];
                $totalWin += $win;
            }
        }
        if(count($slm_mp)){
            $slotArea['slm_mp'] = $slm_mp;
            $slotArea['slm_mv'] = $slm_mv;
            $slotArea['slm_lmv'] = $slm_lmv;
            $slotArea['slm_lmi'] = $slm_lmi;
        }
        if(count($nmp)){
            $slotArea['trail'] = 'nmp~'.implode(',', $nmp).';nmv~'.implode(',', $nmv);
        }

        // return an array with the total payout, the winning symbol, the positions of the winning symbol, the payout for that symbol
        return ['TotalWin' => $totalWin, 'WinLines' => $winSymbols];
    }

    public static function getGroup($slotarea, &$isIncluded, &$group, $level, $pos, $nmvList){
        if($level == 0){
            while($pos < 49){
                if(!$isIncluded[$pos] && $slotarea[$pos] != 2){
                    $isIncluded[$pos] = 1;
                    $group[] = [
                        'symbol' => $slotarea[$pos],
                        'pos' => [$pos],
                        'multi' => 1,
                        'nmp' => [],
                        'nmv' => []
                    ];
                    self::getGroup($slotarea, $isIncluded, $group, $level + 1, $pos, $nmvList);
                }
                $pos += 1;
            }
        }
        else {
            $groupId = count($group) - 1;
            $offsets = [1, 7, -1, -7];
            if($pos % 7 == 0)
                $offsets = [1, 7, -7];
            else if($pos % 7 == 6)
                $offsets = [7, -1, -7];
            foreach($offsets as $offset){
                $newPos = $pos + $offset;
                if(($newPos >= 0 && $newPos < 49) && 
                (($slotarea[$newPos] == 2 && !in_array($newPos, $group[$groupId]['nmp'])) || (!$isIncluded[$newPos] && $slotarea[$newPos] == $group[$groupId]['symbol']))){
                    $isIncluded[$newPos] = 1;
                    $group[$groupId]['pos'][] = $newPos;
                    if($slotarea[$newPos] == 2){
                        $group[$groupId]['nmp'][] = $newPos;
                        $group[$groupId]['nmv'][] = $nmvList[$newPos];
                    }
                    self::getGroup($slotarea, $isIncluded, $group, $level + 1, $newPos, $nmvList);
                }
            }
        }
    }
}
