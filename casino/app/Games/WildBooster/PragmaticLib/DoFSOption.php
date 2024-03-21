<?php

namespace VanguardLTE\Games\WildBooster\PragmaticLib;

class DoFSOption{
  public static function doFSOption($user, $game, $ind, $index, $counter, $balance){
    $time = (int) round(microtime(true) * 1000);
    $res = [
      'balance='.$balance,
      'balance_cash='.$balance,
      'balance_bonus=0',
      'counter='.$counter,
      'index='.$index,
      'fsmul=1',
      'fs_opt_mask='.implode(',', ['fs', 'm', 'msk']),
      'fsmax=5',
      'na=s',
      'fswin=0',
      'fs_opt='.implode('~', ['5,1,0', '5,1,0']),
      'fs=1',
      'fsres=0',
      'sver=5',
      'stime='.$time,
      'fsopt_i='.$ind
    ];
    Log::changeLog($game->id, $user->id, $ind);

    return '&'.(implode('&', $res));
  }

  private static function selectMysterySymbol(){
    return 3 + rand(0, 8);
  }
}

?>