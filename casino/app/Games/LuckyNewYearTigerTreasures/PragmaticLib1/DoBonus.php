<?php

namespace VanguardLTE\Games\LuckyNewYearTigerTreasures\PragmaticLib;

class DoBonus{

  public static function doBonus($user, $game, $bet, $lines, $log, $index, $counter, $bank, $shop, $jpgs, $gameSettings){
    reDoBonus:
    var_dump('doBonus_1_');
    $rsb_c = $log['rsb_c'] + 1;
    $rsb_m = $log['rsb_m'];
    $bpw = $log['bpw'];
    $bet = $log['Bet'];
    $lines = $log['l'];
    $slotArea = [];
    $slotArea['SlotArea'] = is_array($log['s']) ? $log['s'] : explode(',', $log['s']);
    $mo = $log['mo'];
    $mo_t = $log['mo_t'];
    $mo_v = explode(',', $gameSettings['mo_v']);
    
    // Set all un-rhino symbols to blank
    $i = 0;
    // while($i < 19){
    //   $slotArea['SlotArea'][$i] = $slotArea['SlotArea'][$i] == 3 ? 3 : 13;
    //   $i ++;
    // }
    // Add new Rhinos
    $isAdding = rand(0,1);
    $add = 0;
    if($isAdding){
      $newCnt = rand(0,2);
      $add = $newCnt;
      var_dump('doBonus_2_addCnt='.$newCnt);
      $addCnt = 0;
      while($newCnt){
        $index = rand(0, 14);
        if($slotArea['SlotArea'][$index] != 11){
          // set the price of the money symbol
          $moInd = 0; //40
          $random = rand(0, 999);
          if($random > 310)
              $moInd ++;  //80   
          if($random > 640)
              $moInd ++;  //120
          if($random > 790)
              $moInd ++;  //160
          if($random > 880)
              $moInd ++;  //200
          if($random > 930)
              $moInd ++;  //240
          if($random > 945)
              $moInd ++;  //280
          if($random > 960)
              $moInd ++;  //320
          if($random > 970)
              $moInd ++;  //400
          if($random > 975)
              $moInd ++;  //560
          if($random > 980)
              $moInd ++;  //640
          if($random > 985)
              $moInd ++;  //720
          if($random > 990)
              $moInd ++;  //800
          if($random > 995)
              $moInd ++;  //800
          if($random > 998)
              $moInd ++;  //800
          if($random > 999)
              $moInd ++;  //800
          $mo[$index] = $mo_v[$moInd];
          if($moInd == 14)
              $mo_t[$index] = 'jp3';
          else if($moInd == 15)
              $mo_t[$index] = 'jp2';
          else $mo_t[$index] = 'v';
          $bpw += $mo_v[$moInd] * $bet;
          $slotArea['SlotArea'][$index] = 11;
          $newCnt --;
          $addCnt ++;
        }
        if(array_count_values($slotArea['SlotArea'])[11] == 14)
          $newCnt = 0;
      }
      if($addCnt)
        $rsb_c = 0;
      if($rsb_c < 0)
        $rsb_c = 0;
    }
    // Check if there is enough money for payment
    var_dump('doBonus_3_slotArea='.implode(',', $slotArea['SlotArea']));
    if($bpw > $bank->slots)
      goto reDoBonus;
    $cnts = array_count_values($slotArea['SlotArea']);
    if($cnts[11] >= 15 ){
      $mo_jp = explode(';', $gameSettings['mo_jp']);
      $jpgWin = $mo_jp[$cnts[11] - 13] * $bet;
      $isEnough = Jackpots::isEnough($jpgWin, $jpgs);
      var_dump('$jpgwin='.$jpgWin.' isEnough='.$isEnough);
      if($isEnough == false)
        goto reDoBonus;
    }

    // according to specified rate increase respin max value by one
    $bgid = 0;
    $na = 'b';
    $time = (int) round(microtime(true) * 1000);
    $end = 0;
    $sver = 5;
    $addToLog = [];
    $addToServer = [];
    
    // If this is the last bonus turn handle the winning
    var_dump('doBonus_4_TotalWin='.$bpw);
    $isTakenOut = 0;
    if($rsb_c == $rsb_m){
      var_dump('!!!');
      $cnts = array_count_values($slotArea['SlotArea']);
      var_dump('!!!');
      if($cnts[11] >= 15 ){
        $mo_jp = explode(';', $gameSettings['mo_jp']);
        $jpgWin = $mo_jp[$cnts[11] - 13] * $bet;
        $bgid = 1 - ($cnts[11] - 13);
        $isTakenOut = Jackpots::fromJP($jpgWin, $jpgs);

        if($isTakenOut)
          $bpw = $jpgWin;
      }
      var_dump('!!!');
      $addToLog = [
        'tw' => $bpw,
        'coef' => $bet * $lines,
        'rw' => $bpw,
      ];
      var_dump('!!!');
      $addToServer = [
        'tw='.$bpw,
        'coef='.$bet * $lines,
        'rw='.$bpw
      ];
      var_dump('!!!');
      if($isTakenOut){
        $addToLog['rsb_wt'] = 'sw';
        $addToLog['mo_jp'] = $bpw;
        $addToLog['mo_tw'] = $bpw;
        $addToServer[] = 'rsb_wt=sw';
        $addToServer[] = 'mo_jp='.$bpw;
        $addToServer[] = 'mo_tw='.$bpw;
      }
      var_dump('!!!');

      $bpw = 0;
      $na = 'cb';
      $end = 1;
    }
    var_dump('doBonus_5');
    // make toLog and toServer
    $toLog = [
      'Bet' => $bet,
      'bgid' => $bgid,
      'rsb_m' => $rsb_m,
      'Balance' => $log['Balance'],
      'rsb_c' => $rsb_c,
      'Index' => $index,
      'Balance_cash' => $log['Balance'],
      'Balance_bonus' => 0,
      'na' => $na,
      'stime' => $time,
      'end' => $end,
      'sver' => $sver,
      'bpw' => $bpw,
      'Counter' => $counter,
      's' => implode(',', $slotArea['SlotArea']),
      'l' => $log['l'],
      'rsb_s' => $log['rsb_s'],
      'mo' => $mo,
      'mo_t' => $mo_t
    ];

    $toServer = [
      'bgid='.$bgid,
      'rsb_m='.$rsb_m,
      'balance='.$log['Balance'],
      'rsb_c='.$rsb_c,
      'index='.$index,
      'balance_cash='.$log['Balance'],
      'balance_bonus=0',
      'na='.$na,
      'stime='.$time,
      'end='.$end,
      'sver=5',
      'bpw='.$bpw,
      'counter='.$counter,
      's='.implode(',', $slotArea['SlotArea']),
      'e_aw=0',
      'rsb_s='.implode(',', $log['rsb_s']),
      'mo='.implode(',', $mo),
      'mo_t='.implode(',', $mo_t)
    ];

    $toLog['ServerState'] = $toServer;

    $toLog = array_merge($toLog, $addToLog);
    $toServer = array_merge($toServer, $addToServer);
    var_dump('doBonus_6');
    // take out or add cash from or to bank
    SwitchMoney::set(8, 0, $shop, $bank, $jpgs, $user, $game, 0, array_key_exists('tw', $toLog) ? $toLog['tw'] : 0, $slotArea, 0, $toLog, 0, $isTakenOut);
    var_dump('doBonus_7');
    //write a log
    Log::setLog($toLog, $game->id, $user->id, $user->shop_id);
    var_dump('doBonus_8');

    return '&'.(implode('&', $toServer));
  }
}

?>