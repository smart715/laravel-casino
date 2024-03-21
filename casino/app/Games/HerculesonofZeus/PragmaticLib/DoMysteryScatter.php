<?php

namespace VanguardLTE\Games\HerculesonofZeus\PragmaticLib;

class DoMysteryScatter{
  public static function doMystery($user, $game){
    $ms = self::selectMysterySymbol();
    $res = [
      'fsmul=1',
      'fsmax=10',
      'ms='.$ms,
      'purtr=1',
      'reel_set=14',
      'na=s',
      'fswin=0',
      'puri=0',
      'fs=1',
      'fsres=0'
    ];
    Log::changeLog($game->id, $user->id, $ms);

    return '&'.(implode('&', $res));
  }

  private static function selectMysterySymbol(){
    return 3 + rand(0, 8);
  }
}

?>