<?php

namespace VanguardLTE\Games\BigBassSplash\PragmaticLib;

class DoBonus{

  public static function doBonus($user, $game, $bet, $lines, $log, $index, $counter, $bank, $shop, $jpgs, $gameSettings){
    var_dump('doBonus_1_');
    
    $trails = self::getTrails();

    $bet = $log['Bet'];
    $lines = $log['l'];
    $slotArea = [];
    $slotArea['SlotArea'] = is_array($log['s']) ? $log['s'] : explode(',', $log['s']);
    
    $scatterTmp = explode('~', $gameSettings['scatters']);
    $scatterTmp[2] = explode(',', $scatterTmp[2]);
    $scatterCnt = count(array_keys($slotArea['SlotArea'], 1));
    $fsmax = $scatterTmp[2][5 - $scatterCnt];
    $time = (int) round(microtime(true) * 1000);
    if($trails[4][1])
      $fsmax += 2;
      
      foreach($trails as $index => $trail){
        $trails[$index] = implode('~', $trail);
      }
      var_dump('doBonus_5', $trails);
    $trails = implode(';', $trails);
    // make toLog and toServer
    $toLog = [
      'fsmul' => 1,
      'trail' => $trails,
      'fsmax' => $fsmax,
      'fswin' => 0,
      'fs' => 1,
      'fsres' => 0,
      'g' => '{bg_0:{bgid:"0",bgt:"69",end:"1",rw:"0.00"}}',
      'Bet' => $bet,
      'Balance' => $log['Balance'],
      'Index' => $index,
      'Balance_cash' => $log['Balance'],
      'Balance_bonus' => 0,
      'na' => 's',
      'stime' => $time,
      'sver' => 5,
      'Counter' => $counter,
      'l' => $log['l'],
      'tw' => $log['tw']
    ];
    
    $toServer = [
      'fsmul=1',
      'trail='.$trails,
      'fsmax='.$toLog['fsmax'],
      'fswin=0',
      'fs=1',
      'fsres=0',
      'g={bg_0:{bgid:"0",bgt:"69",end:"1",rw:"0.00"}}',
      'balance='.$log['Balance'],
      'index='.$index,
      'balance_cash='.$log['Balance'],
      'balance_bonus=0',
      'na=s',
      'stime='.$time,
      'sver=5',
      'counter='.$counter,
    ];
    
    $toLog['ServerState'] = $toServer;
    //write a log
    Log::setLog($toLog, $game->id, $user->id, $user->shop_id);
    var_dump('doBonus_8');

    return '&'.(implode('&', $toServer));
  }

  private static function getTrails(){
    $trailKeys = ['mm', 'l2', 'md', 'mf', 'fs'];
    $trailValues = [0, 0, 0, 0, 0];
    foreach($trailValues as $index => $trailValue){
      if(rand(1, 1000) <= 400)
        $trailValues[$index] += 1;
    }
    $trails = [];
    foreach($trailKeys as $index => $trailKey){
      $trails[] = [$trailKey, $trailValues[$index]];
    }
    return $trails;
  }
}

?>