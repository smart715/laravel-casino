<?php

namespace VanguardLTE\Games\BullFiesta\PragmaticLib;

use Illuminate\Support\Facades\DB;

class Jackpots
{
    public static function toJP($bet, $jpgs){
        $toJackpots = 0;
        $upsertArray = [];
        // iterate over the jackpots, add to the jackpot bank, calculate the total amount in the jackpot
        foreach ($jpgs as $jpg) {
            $upsertArray[] = [
                'id' => $jpg->id,
                'name' => $jpg->name,
                'balance' => $jpg->balance + $bet * ($jpg->percent / 100),
                'shop_id' => $jpg->shop_id
            ];
            $toJackpots += $bet * ($jpg->percent / 100);
        }
        DB::table('jpg')->upsert($upsertArray,['id','name','shop_id'], ['balance']);
        return $toJackpots;
    }

    public static function fromJP($amount, $jpgs){
        $isTakenOut = 0;
        $upsertArray = [];
        // iterate over the jackpots, add to the jackpot bank, calculate the total amount in the jackpot
        foreach ($jpgs as $jpg) {
            $flag = 0;
            if($jpg->balance >= $amount && $isTakenOut == 0){
                $flag = 1;
                var_dump($jpg->name.'_'.$jpg->balance.'_'.($amount * $flag).'_shop='.$jpg->shop_id);
                $isTakenOut = 1;
            }
            $upsertArray[] = [
                'id' => $jpg->id,
                'name' => $jpg->name,
                'balance' => $jpg->balance - $amount * $flag,
                'shop_id' => $jpg->shop_id
            ];
        }
        var_dump('!!!', $upsertArray);
        DB::table('jpg')->upsert($upsertArray,['id','name','shop_id'], ['balance']);
        return $isTakenOut;
    }

    public static function isEnough($amount, $jpgs){
        // iterate over the jackpots, if one jackpot is able to pay the amount, then return true
        foreach ($jpgs as $jpg) {
            if($jpg->balance >= $amount)
                return true;
        }
        return false;
    }
}
