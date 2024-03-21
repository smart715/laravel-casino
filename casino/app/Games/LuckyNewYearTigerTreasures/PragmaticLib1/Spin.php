<?php

namespace VanguardLTE\Games\LuckyNewYearTigerTreasures\PragmaticLib;

use VanguardLTE\Services\Api\Api;

class Spin
{
    public static function spinResult($user, $game, $bet, $lines, $log, $gameSettings, $index, $counter, $callbackUrl, $pur, $bank, $shop, $jpgs){
        var_dump('0');
       // if ($user->balance < $bet * $lines) return false;
        $newSpinCnt = 0;
        $gameSettings = $gameSettings->all;
        $currentLog = $log->getLog();
        var_dump('0_1');
        // $lines = $doubleChance == 0 ? $lines : $lines * 1.25;
        if ($currentLog &&
            (array_key_exists('state', $currentLog) && $currentLog['state'] != 'spin' && $currentLog['state'] != 'lastRespin' ||
                array_key_exists('FreeState', $currentLog) && $currentLog['FreeState'] != 'LastFreeSpin')){
            $changeBalance = 0;
        }else{
            $changeBalance = ($bet * $lines * -1);
            if ($pur === '0') $changeBalance *= 100;
        }
        var_dump('0_2');
        if ($user->balance < -1 * $changeBalance) return false;
        NewSpin:
        //build a playing field
        $reelSet = 0;
        $pur1 = $pur;
        if($currentLog && array_key_exists('puri', $currentLog) || $pur1 == '0')    $reelSet = 1;
        if ($currentLog && array_key_exists('fs', $currentLog)) $reelSet = 1; // if free spins - then the set of reels is 4th
        $slotArea = SlotArea::getSlotArea($gameSettings,$reelSet,$currentLog);
        var_dump('1');

        // BuyFreeSpins::addWilds($slotArea['SlotArea'], $gameSettings, 0, 5); // add wilds
        // SlotArea::makeFullStack($slotArea);
        if(SlotArea::getMsCnt($slotArea['SlotArea']) > 5){
            if($currentLog && array_key_exists('fs', $currentLog))
                goto NewSpin;
               if($game->stat_in - $game->stat_out < 5000)
                goto NewSpin;
            $pur1 = '2';
        }
        else if($currentLog && array_key_exists('fs', $currentLog) && $currentLog['fs'] == $currentLog['fsmax'])
            $slotArea['ScatterCount'] = BuyFreeSpins::addScatters($slotArea['SlotArea'], $gameSettings, 0, 1);
        else if($currentLog && array_key_exists('fs', $currentLog))
            $slotArea['ScatterCount'] = BuyFreeSpins::addScatters($slotArea['SlotArea'], $gameSettings, 0, 2);
       else 
            $slotArea['ScatterCount'] = BuyFreeSpins::addScatters($slotArea['SlotArea'], $gameSettings, 0, 3);

        // if scatter count is greater than settings_needfs make pur = 0
        if($slotArea['ScatterCount'] >= $gameSettings['settings_needfs'])
            if(!$currentLog || $currentLog && !array_key_exists('fs', $currentLog))
                $pur1 = '0';
            else {
                var_dump('2_1_scatterCount='.$slotArea['ScatterCount'].'_settings-needfs='.$gameSettings['settings_needfs']);
                $pur1 = '1';
            }
        $reelSet = $pur1 == '0' ? $reelSet = 1 : $reelSet;
        SlotArea::getMO($gameSettings, $slotArea, $log, $pur1);
        var_dump('2_needfs='.$gameSettings['settings_needfs'].'sCnt='.$slotArea['ScatterCount']);
        if($currentLog && array_key_exists('fs', $currentLog))
            SlotArea::setGiantSymbol($gameSettings, $slotArea, $currentLog);
        //check win (return array with win amount and symbol positions
        $winChecker = new WinChecker($gameSettings);
        $win = $winChecker->getWin($pur1, $currentLog, $bet, $slotArea);
        var_dump('3');

        //put everything in a convenient array
        $logAndServer = LogAndServer::getResult($slotArea, $index, $counter, $bet, $lines, $reelSet,
            $win, $pur1, $currentLog, $user, $changeBalance, $gameSettings);
        var_dump('6');
        // check if you can win
        $fswin = array_key_exists('fswin', $win) ? $win['fswin'] : 0;
        if(array_key_exists(1, array_count_values($slotArea['SlotArea'])) && array_count_values($slotArea['SlotArea'])[1] > 2)
            $win['TotalWin'] += SlotArea::getPsym($gameSettings, $slotArea['SlotArea'], $bet, $lines)['psymwin'];
        else if($pur1 === '0')
            $win['TotalWin'] += SlotArea::getPsym($gameSettings, $slotArea['SlotArea'], $bet, $lines)['psymwin'];
        if ($win['TotalWin'] + $fswin > 0 || array_key_exists('bpw', $logAndServer['Log']))
            $win_permission = WinPermission::winCheck($fswin,$pur1,$bank,$logAndServer['Log'],$win['TotalWin'], $currentLog, $changeBalance, $shop);
        else $win_permission = true;
        if (!$win_permission) {
            $newSpinCnt ++;
            goto NewSpin;
        }
        // check rtp when you spin
        $checkRtpSlots = new CheckRtp($gameSettings['rtp_slots'], $game);
        if($pur1 != '0' && $currentLog && !array_key_exists('fs', $currentLog) && !$checkRtpSlots->checkRtp($bet * $lines, $win['TotalWin'] , $user, $game, $bank) && $newSpinCnt < 4 && $bank->slots > $bet * $lines){
            $newSpinCnt ++;
            goto NewSpin;
        }
        // check rtp when you're on free spin
        $checkRtpBonus = new CheckRtp($gameSettings['rtp_bonus'], $game);
        if($currentLog 
        && array_key_exists('fs', $currentLog) 
        && !$checkRtpBonus->checkRtp($bet * $lines * 100 / $currentLog['fsmax'], $win['TotalWin'] + $fswin, $user, $game, $bank)
        && $bank->bonus + $bank->slots > $bet * $lines * 100 / $currentLog['fsmax']
        && $newSpinCnt < 4){
            $newSpinCnt ++;
            goto NewSpin;
        }
        $fswin = 0;
        // allocate money to the bank and write it down in statistics
        // $freeSpins = 0;
        SwitchMoney::set($pur1, $changeBalance, $shop, $bank, $jpgs, $user, $game, $callbackUrl, $win['TotalWin'], $slotArea, $fswin, $logAndServer['Log'], $win_permission, 0);
        var_dump('8');
        //write a log
        Log::setLog($logAndServer['Log'], $game->id, $user->id, $user->shop_id);
        var_dump('9');

        //give to the server
        $response = '&'.(implode('&', $logAndServer['Server']));
        var_dump('10');
        return $response;
    }

}
