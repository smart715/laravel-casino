<?php

namespace VanguardLTE\Games\Cleocatra\PragmaticLib;

class SlotBank
{
    public static function addBank($totalBet, $bank, $toJackpot, $toProfit, $toBonus){
        // calculate how much goes to the bank
        $toBank = $totalBet - $toJackpot - $toProfit;
        var_dump('toBank='.$toBank);
        $bank->increment('slots',$toBank);
        return $toBank;
    }
}
