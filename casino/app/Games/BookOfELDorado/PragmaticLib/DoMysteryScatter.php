<?php

namespace VanguardLTE\Games\BookOfELDorado\PragmaticLib;

class DoMysteryScatter{
  public static function doMystery($user, $game, $log, $index, $counter){
    $ms = self::selectMysterySymbol();
    $time = (int) round(microtime(true) * 1000);
    $res = [
      'ms='.implode(',', $ms),
      'balance='.$log['Balance'],
      'balance_cash='.$log['Balance'],
      'index='.$index,
      'counter='.$counter,
      'stime='.$time,
      'na=s',
      'sver=5'
    ];
    Log::changeLog($game->id, $user->id, $ms);

    return '&'.(implode('&', $res));
  }

  private static function selectMysterySymbol(){
    $ms = [];
    $ms[0] = rand(3, 6);
    $ms[1] = rand(7, 11);
    return $ms;
  }
}

?>