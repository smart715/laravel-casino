<?php

namespace VanguardLTE\Games\HerculesonofZeus\PragmaticLib;


class Collect
{
    public static function collect($user, $index, $counter, $log, $callbackUrl, $game){
        $currentLog = $log->getLog();
        // var_dump(array_key_exists('isCollected', $currentLog));
        if(!array_key_exists('isCollected', $currentLog)){
            $user->increment('balance', $currentLog['tw']);
            // var_dump('2');
            Log::setCollected($game->id, $user->id, 1);
        }
        // var_dump('3');
        $user->save();
        $game->save();
        $time = (int) round(microtime(true) * 1000);
        $response = [
            'balance='.$user->balance,
            'index='.$index,
            'balance_cash='.$user->balance,
            'balance_bonus=0.00',
            'na=s',
            'stime='.$time,
            'sver=5',
            'counter='.$counter
        ];
        return '&'.implode('&', $response);
    }

}
