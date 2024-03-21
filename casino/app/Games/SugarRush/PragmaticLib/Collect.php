<?php

namespace VanguardLTE\Games\SugarRush\PragmaticLib;


class Collect
{
    public static function collect($user, $index, $counter, $log, $callbackUrl, $game){
        $currentLog = $log->getLog();
        $user->increment('balance', $currentLog['TotalWin']);
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
        return implode('&', $response);
    }

}
