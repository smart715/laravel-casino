<?php

namespace VanguardLTE\Games\BigBassKeepingitReel\PragmaticLib;

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

}
