<?php

namespace VanguardLTE\Games\GreatRhinoDeluxe\PragmaticLib;

function handleAccv($accv){
  $accv = explode('~', $accv);
  $pos = explode(',', $accv[3]);
  $accv[3] = [];
  var_dump('accv=', $accv);
  foreach($pos as $value){
    if($value || $value == '0')
      $accv[3][] = $value;
  }
  $accv[3] = implode(',', $accv[3]);
  var_dump('accv=', $accv);
  $accv = implode('~', $accv);
  return $accv;
}

class DoBonus{

  public static function doBonus($user, $game, $bet, $lines, $log, $index, $counter, $bank, $shop, $jpgs, $gameSettings){    
    reDoBonus:
    var_dump('doBonus_1_');
    
    $bg_mp = -1;
    if($log && array_key_exists('bg_mp', $log)){
      $bg_mp = $log['bg_mp'];
    }
    $bg_mv = 2;
    $rsb_c = $log['rsb_c'] + 1;
    $rsb_m = $log['rsb_m'];
    if($rsb_m == 1)
      return self::collectRhino($user, $game, $bet, $lines, $log, $index, $counter, $bank, $shop, $jpgs, $gameSettings);
    $bpw = $log['bpw'];
    $bet = $log['Bet'];
    $lines = $log['l'];
    $slotArea = [];
    $slotArea['SlotArea'] = is_array($log['s']) ? $log['s'] : explode(',', $log['s']);
    
    // Set all un-rhino symbols to blank
    $i = 0;
    while($i < 15){
      $inStack = 0;
      $j = -2;
      while($j < 2){
        if($i + $j * 5 > -1 && $i + $j * 5 < 15 && $slotArea['SlotArea'][$i + $j * 5] == 3)
          $inStack ++;
        $j ++;
      }
      $slotArea['SlotArea'][$i] = $slotArea['SlotArea'][$i] == 3 ? 3 : 12;
      $i ++;
    }
    // Add multiplier
    if($rsb_c == 1 && rand(0, 2) == 2)
      $bg_mp = array_keys($slotArea['SlotArea'], 3)[rand(0, count(array_keys($slotArea['SlotArea'], 3)) - 1)];

    // Add new Rhinos
    $isAdding = rand(0, 1);
    $add = 0;
    if($isAdding){
      $newCnt = rand(0, 2);
      $add = $newCnt;
      var_dump('doBonus_2_addCnt='.$newCnt);
      while($newCnt){
        $index = rand(0, 14);
        if(array_count_values($slotArea['SlotArea'])[3] == 15){
          $newCnt = 0;
          break;
        }
        if($slotArea['SlotArea'][$index] == 12){
          $slotArea['SlotArea'][$index] = 3;
          $newCnt --;
        }
      }
    }
    // Check if there is enough money for payment
    var_dump('doBonus_3_slotArea='.implode(',', $slotArea['SlotArea']));
    $winChecker = new WinChecker($gameSettings);
    $win = $winChecker->getWin(8, $log, $bet, $slotArea, $bg_mp, $bg_mv);
    if($win['TotalWin'] > $bank->slots)
      goto reDoBonus;
    $cnts = array_count_values($slotArea['SlotArea']);
    if($cnts[3] >= 14 ){
      $bg_i = explode(',', $gameSettings['bg_i']);
      $jpgWin = $bg_i[1 - ($cnts[3] - 14)] * $bet * $lines;
      $isEnough = Jackpots::isEnough($jpgWin, $jpgs);
      if($isEnough == false)
        goto reDoBonus;
    }

    // according to specified rate increase respin max value by one
    if($add)  $rsb_m += 1;
    $bgid = 1;
    $na = 'b';
    $time = (int) round(microtime(true) * 1000);
    $end = 0;
    $bpw = $win['TotalWin'];
    $sver = 5;
    $addToLog = [];
    $addToServer = [];
    
    // If this is the last bonus turn handle the winning
    var_dump('doBonus_4_TotalWin='.$win['TotalWin']);
    $isTakenOut = 0;
    if($rsb_c == $rsb_m){
      $cnts = array_count_values($slotArea['SlotArea']);
      if($cnts[3] >= 14 ){
        $bg_i = explode(',', $gameSettings['bg_i']);
        $jpgWin = $bg_i[$cnts[3] - 14] * $bet * $lines;
        $isTakenOut = Jackpots::fromJP($jpgWin, $jpgs);

        if($isTakenOut)
          $win['TotalWin'] = $jpgWin;
      }
      $addToLog = [
        'tw' => $win['TotalWin'],
        'coef' => $bet,
        'rw' => $win['TotalWin'],
      ];
      $addToServer = [
        'tw='.$win['TotalWin'],
        'coef='.$bet,
        'rw='.$win['TotalWin']
      ];
      if($isTakenOut){
        $addToLog['rsb_wt'] = 'sw';
        $addToLog['mo_jp'] = $win['TotalWin'];
        $addToLog['mo_tw'] = $win['TotalWin'];
        $addToServer[] = 'rsb_wt=sw';
        $addToServer[] = 'mo_jp='.$win['TotalWin'];
        $addToServer[] = 'mo_tw='.$win['TotalWin'];
      }
      if($addToLog['tw'] > 0 && !$isTakenOut){
        $addToLog['WinLines'] = $win['WinLines'];
        $positions = LogAndServer::positionsToServer($addToLog['WinLines']);
        $addToServer = array_merge($addToServer, $positions);
      }

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
      'bgt' => $log['bgt'],
      'end' => $end,
      'sver' => $sver,
      'bpw' => $bpw,
      'Counter' => $counter,
      's' => implode(',', $slotArea['SlotArea']),
      'l' => $log['l']
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
      'bgt='.$log['bgt'],
      'end='.$end,
      'sver=5',
      'bpw='.$bpw,
      'counter='.$counter,
      's='.implode(',', $slotArea['SlotArea'])
    ];

    if($rsb_c == $rsb_m)
      unset($toServer[array_keys($toServer, 'bpw='.$bpw)[0]]);


    $toLog = array_merge($toLog, $addToLog);
    $toServer = array_merge($toServer, $addToServer);
    if($bg_mp > -1){
      $toLog['bg_mp'] = $bg_mp;
      $toLog['bg_mv'] = 2;
      $toServer[] = 'bg_mp='.$toLog['bg_mp'];
      $toServer[] = 'bg_mv='.$toLog['bg_mv'];
    }
    $toLog['ServerState'] = $toServer;
    var_dump('doBonus_6_bg_mp='.$bg_mp);
    // take out or add cash from or to bank
    SwitchMoney::set(8, 0, $shop, $bank, $jpgs, $user, $game, 0, array_key_exists('tw', $toLog) ? $toLog['tw'] : 0, $slotArea, 0, $toLog, 0, $isTakenOut);
    var_dump('doBonus_7');
    //write a log
    Log::setLog($toLog, $game->id, $user->id, $user->shop_id);
    var_dump('doBonus_8');

    return '&'.(implode('&', $toServer));
  }

  private static function collectRhino($user, $game, $bet, $lines, $log, $index, $counter, $bank, $shop, $jpgs, $gameSettings){
    reDo:
    $rsb_c = $log['rsb_c'] + 1;
    $rsb_m = $log['rsb_m'];
    var_dump('doBonus_1_');
    $bpw = array_key_exists('bpw', $log) ?  $log['bpw'] : 0;
    $bet = $log['Bet'];
    $lines = $log['l'];
    $accvs = explode('~', handleAccv($log['accv']));
    $slotArea = [];
    $slotArea['SlotArea'] = is_array($log['s']) ? $log['s'] : explode(',', $log['s']);
    
    // format slot area
    $i = 0;
    while($i < 15){
      $slotArea['SlotArea'][$i] = $slotArea['SlotArea'][$i] = 12;
      $i ++;
    }
    // Add new Rhinos
    $add = 0;
    var_dump('rhinoPos='.$accvs[3]);
    $newCnt = count(explode(',', $accvs[3]));
    $add = $newCnt;
    var_dump('doBonus_2_addCnt='.$newCnt);
    while($newCnt){
      $index1 = rand(0, 14);
      if($slotArea['SlotArea'][$index1] == 12){
        $slotArea['SlotArea'][$index1] = 3;
        $newCnt --;
      }
      if(array_count_values($slotArea['SlotArea'])[3] == 15)
        $newCnt = 0;
    }
    // Check if there is enough money for payment
    var_dump('doBonus_3_slotArea='.implode(',', $slotArea['SlotArea']));
    $winChecker = new WinChecker($gameSettings);
    $win = $winChecker->getWin(8, $log, $bet, $slotArea, -1, 0);
    if($win['TotalWin'] > $bank->slots)
      goto reDo;
    $cnts = array_count_values($slotArea['SlotArea']);

    // according to specified rate increase respin max value by one
    $bgid = 0;
    $na = 'c';
    $time = (int) round(microtime(true) * 1000);
    $end = 0;
    $bpw = $win['TotalWin'];
    $sver = 5;
    $addToLog = [];
    $addToServer = [];
    
    // If this is the last bonus turn handle the winning
    var_dump('doBonus_4_TotalWin='.$win['TotalWin']);
    if($rsb_c == $rsb_m){
      $addToLog = [
        'tw' => $win['TotalWin'] + $log['tw'],
        'coef' => $bet,
        'rw' => $win['TotalWin'],
      ];
      $addToServer = [
        'tw='.($win['TotalWin'] + $log['tw']),
        'coef='.$bet,
        'rw='.$win['TotalWin']
      ];
      if($addToLog['tw'] > 0){
        $addToLog['WinLines'] = $win['WinLines'];
        $positions = LogAndServer::positionsToServer($addToLog['WinLines']);
        $addToServer = array_merge($addToServer, $positions);
      }

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
      'na' => 'c',
      'stime' => $time,
      'bgt' => $log['bgt'],
      'end' => $end,
      'sver' => $sver,
      'bpw' => $bpw,
      'Counter' => $counter,
      's' => implode(',', $slotArea['SlotArea']),
      'l' => $log['l'],
      'accm' => $log['accm'],
      'accv' => handleAccv($log['accv']),
      'acci' => 0
    ];

    $toServer = [
      'bgid='.$bgid,
      'rsb_m='.$rsb_m,
      'balance='.$log['Balance'],
      'rsb_c='.$rsb_c,
      'index='.$index,
      'balance_cash='.$log['Balance'],
      'balance_bonus=0',
      'na=c',
      'stime='.$time,
      'bgt='.$log['bgt'],
      'end='.$end,
      'sver=5',
      'bpw='.$bpw,
      'counter='.$counter,
      's='.implode(',', $slotArea['SlotArea']),
      'accm='.$toLog['accm'],
      'accv='.$toLog['accv'],
      'acci=0'
    ];

    $toLog['ServerState'] = $toServer;

    $toLog = array_merge($toLog, $addToLog);
    $toServer = array_merge($toServer, $addToServer);
    var_dump('doBonus_6');
    // take out or add cash from or to bank
    SwitchMoney::set(8, 0, $shop, $bank, $jpgs, $user, $game, 0, array_key_exists('rw', $toLog) ? $toLog['rw'] : 0, $slotArea, 0, $toLog, 0, 0);
    var_dump('doBonus_7');
    //write a log
    Log::setLog($toLog, $game->id, $user->id, $user->shop_id);
    var_dump('doBonus_8');

    return '&'.(implode('&', $toServer));
  }
}

?>