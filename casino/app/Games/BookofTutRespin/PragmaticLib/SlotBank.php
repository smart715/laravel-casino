<?php

namespace VanguardLTE\Games\BookofTutRespin\PragmaticLib;

class SlotBank
{
    public static function addBank($totalBet, $bank, $toJackpot, $toProfit, $toBonus){
        // calculate how much goes to the bank
        $toBank = $totalBet - $toJackpot - $toProfit;
        if ($toBonus){
            $bank->increment('bonus',$toBank);
        }else{
            $bank->increment('slots',$toBank);
        }
        return $toBank;
    }
}
