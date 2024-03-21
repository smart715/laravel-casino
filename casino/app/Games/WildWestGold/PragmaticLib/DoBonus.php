<?php

namespace VanguardLTE\Games\WildWestGold\PragmaticLib;

class DoBonus{

  public static function doBonus($user, $game, $bet, $lines, $log, $index, $counter, $bank, $shop, $jpgs, $gameSettings){
    $wins = [];
    $win_fs = 0;
    $i = 0;
    while($i < 9){
      if(rand(0, 9) == 9)
        $wins[] = 2;
        else $wins[] = 1;
        $win_fs += $wins[$i];
        $i ++;
      }
      
      $time = (int) round(microtime(true) * 1000);
      
    $toLog = [
      'fsmul' => 1,
      'bgid' => 0,
      'balance' => $log['Balance'],
      'index' => $index,
      'balance_cash' => $log['Balance'],
      'balance_bonus' => 0,
      'na' => 's',
      'bgt' => 32,
      'end' => 1,
      'sver' => 5,
      'n_reel_set' => 1,
      'fsres' => 0.00,
      'end' => 1,
      'wins_mask' => 'nff,nff,nff,nff,nff,nff,nff,nff,nff',
      'fs' => 1,
      'fswin' => 0.00,
      'counter' => $counter,
      'fsmax' => $win_fs,
      'wins' => implode(',', $wins),
      'win_fs' => $win_fs,
      'mbp' => $log['mbp'],
      'mbv' => $log['mbv'],
      'tw' => $log['tw']
    ];
    var_dump('doBonus');
    
    $toServer = [
      'fsmul=1',
      'bgid=0',
      'balance='.$log['Balance'],
      'index='.$index,
      'balance_cash='.$log['Balance'],
      'balance_bonus=0',
      'na=s',
      'stime='.$time,
      'bgt=32',
      'end=1',
      'sver=5',
      'n_reel_set=1',
      'fsres=0.00',
      'end=1',
      'wins_mask=nff,nff,nff,nff,nff,nff,nff,nff,nff',
      'fs=1',
      'fswin=0.00',
      'counter='.$counter,
      'fsmax='.$win_fs,
      'wins='.implode(',', $wins),
      'win_fs='.$win_fs
    ];
    
    //write a log
    Log::setLog($toLog, $game->id, $user->id, $user->shop_id);

    return '&'.(implode('&', $toServer));
  }
}

?>