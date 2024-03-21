<?php

namespace VanguardLTE\Games\HerculesonofZeus\PragmaticLib;

class SlotBank
{
    public static function addBank($totalBet, $bank, $toJackpot, $toProfit, $toBonus){
        // calculate how much goes to the bank
        $toBank = $totalBet - $toJackpot - $toProfit;
        $bank->increment('slots',$toBank);
        return $toBank;
    }
}
