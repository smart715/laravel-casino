<?php

namespace VanguardLTE\Games\PirateGoldDeluxe\PragmaticLib;

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
    $bmo = $log['bmo'];
    $bmo_t = $log['bmo_t'];
    $bmo_v = explode(',', $gameSettings['mo_v']);
    $rs_s = $log['rs_s'];
    
    // Set all un-rhino symbols to blank
    $i = 0;
    // while($i < 19){
    //   $slotArea['SlotArea'][$i] = $slotArea['SlotArea'][$i] == 3 ? 3 : 13;
    //   $i ++;
    // }
    // Add new Rhinos
    $isAdding = rand(0,1);
    if($rsb_c == $rsb_m && count(array_keys($rs_s, 13)) < 12 && $isAdding == 0)
      $isAdding = 1;
    $add = 0;
    if($isAdding){
      $newCnt = rand(0,2);
      if($rsb_c == $rsb_m && count(array_keys($rs_s, 13)) < 12 && $newCnt = 0)
        $newCnt = 1;
      $add = $newCnt;
      var_dump('doBonus_2_addCnt='.$newCnt);
      $addCnt = 0;
      while($newCnt){
        $index = rand(0, 19);
        if($slotArea['SlotArea'][$index] != 13){
          // set the price of the money symbol
          $moInd = 0; //40
          $random = rand(0, 999);
          if($random > 310)
              $moInd ++;  //80   
          if($random > 640)
              $moInd ++;  //120
          if($random > 790)
              $moInd ++;  //160
          if($random > 890)
              $moInd ++;  //200
          if($random > 940)
              $moInd ++;  //240
          if($random > 960)
              $moInd ++;  //280
          if($random > 975)
              $moInd ++;  //320
          if($random > 985)
              $moInd ++;  //400
          if($random > 990)
              $moInd ++;  //560
          if($random > 993)
              $moInd ++;  //640
          if($random > 996)
              $moInd ++;  //720
          if($random > 998)
              $moInd ++;  //800
          
          // decide the number of mo
          $mo_num = 1;
          if(rand(1, 7) == 7)
            $mo_num = 2;
            $new_mo_val = 0;
          foreach($log['bmo'] as $ind => $val)
            if($mo_num == 1 && $bmo_t[$ind] == 'v' || $mo_num == 2 && $bmo_t[$ind] != 'r')
              $new_mo_val += $val;
          $bmo[$index] = $new_mo_val;
          $bmo_t[$index] = 'mo'.$mo_num;
          $bpw += $bmo[$index] * $bet;
          $slotArea['SlotArea'][$index] = 13;
          $rs_s[$index] = 13;
          $newCnt --;
          $addCnt ++;
        }
        if(array_count_values($slotArea['SlotArea'])[13] == 17)
          $newCnt = 0;
      }
      if($addCnt)
        $rsb_c = 0;
    }
    // Check if there is enough money for payment
    var_dump('doBonus_3_slotArea='.implode(',', $slotArea['SlotArea']));
    if($bpw > $bank->slots)
      goto reDoBonus;
    $cnts = array_count_values($slotArea['SlotArea']);
    if($cnts[13] >= 18 ){
      $mo_jp = explode(';', $gameSettings['mo_jp']);
      $jpgWin = $mo_jp[$cnts[13] - 18] * $bet;
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
      if($cnts[13] >= 18 ){
        $mo_jp = explode(';', $gameSettings['mo_jp']);
        $jpgWin = $mo_jp[$cnts[13] - 18] * $bet;
        $bgid = 1 - ($cnts[13] - 18);
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
      'bmo' => $bmo,
      'bmo_t' => $bmo_t,
      'wp' => $bpw / $bet,
      'bgt' => $log['bgt'],
      'rs_s' => $rs_s
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
      'rsb_rt=0',
      'rsb_mu=0',
      'e_aw=0',
      'rsb_s='.implode(',', $log['rsb_s']),
      'bmo='.implode(',', $bmo),
      'bmo_t='.implode(',', $bmo_t),
      'wp='.$toLog['wp'],
      'bgt='.$toLog['bgt'],
      'rs_s='.implode(',', $toLog['rs_s'])
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