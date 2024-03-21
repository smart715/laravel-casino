<?php

namespace VanguardLTE\Games\GatesofOlympus\PragmaticLib;

use Illuminate\Support\Facades\DB;

class Jackpots
{
    public static function toJP($bet, $jpgs){
        $toJackpots = 0;
        $upsertArray = [];
        // перебираем джекпоты, прибавляем в банк джекпота, считаем общую сумму в джекпот
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
