<?php

namespace VanguardLTE\Games\CandyVillage\PragmaticLib;

class SlotBank
{
    public static function addBank($totalBet, $bank, $toJackpot, $toProfit, $toBonus){
        // расчитать сколько идет в банк
        $toBank = $totalBet - $toJackpot - $toProfit;
        if ($toBonus){
            $bank->increment('bonus',$toBank);
        }else{
            $bank->increment('slots',$toBank*0.1);
            $bank->increment('bonus',$toBank*0.9);
        }
        return $toBank;
    }
}
