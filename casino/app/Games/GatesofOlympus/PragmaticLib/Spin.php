<?php

namespace VanguardLTE\Games\GatesofOlympus\PragmaticLib;

use VanguardLTE\Services\Api\Api;

class Spin
{
    public static function spinResult($user, $game, $bet, $lines, $log, $gameSettings, $index, $counter, $callbackUrl, $doubleChance, $buyFS, $bank, $shop, $jpgs){
        
        $gameSettings = $gameSettings->all;
        $currentLog = $log->getLog();
        $lines = $doubleChance == 0 ? $lines : $lines * 1.25;
        if ($currentLog &&
            ($currentLog['State'] != 'Spin' && $currentLog['State'] != 'LastRespin' ||
                array_key_exists('FreeState', $currentLog) && $currentLog['FreeState'] != 'LastFreeSpin')){
            $changeBalance = 0;
        }else{
            $changeBalance = ($bet * $lines * -1);
            if ($buyFS === '0') $changeBalance *= 100;
        }
        if ($user->balance < -1 * $changeBalance) return false;

        NewSpin:
        //построить игровое поле
        $reelSet = $doubleChance == 0 ? 0 : 2;
        if ($currentLog && array_key_exists('FreeState', $currentLog)
            && $currentLog['FreeState'] != 'LastFreeSpin' && $currentLog['FreeSpinNumber'] > 1) $reelSet = 1; // if free spins - then the set of reels is 4th
        $slotArea = SlotArea::getSlotArea($gameSettings,$reelSet,$currentLog);

        if ($buyFS === '0') BuyFreeSpins::getFreeSpin($slotArea['SlotArea'], $gameSettings); // покупка фриспинов

        //проверить выигрыш (вернуть массив с суммой выигрыша и позициями символов
        $winChecker = new WinChecker($gameSettings);
        $win = $winChecker->getWin($bet, $slotArea);

        $freeSpins = false;
        // проверить фриспины если нет выигрыша
        if ($win['TotalWin'] == 0) {
            $freeSpins = FreeSpin::check($slotArea['SlotArea'], $currentLog, $gameSettings, $bet);
            /*if ($freeSpins && $currentLog && array_key_exists('FreeSpins', $currentLog) && $currentLog['FreeState'] == 'LastFreeSpin')
                goto NewSpin;*/
        }

        // отключить получение дополнительных фриспинов при последнем фриспине, иначе они не засчитываются.
        //if ($currentLog && array_key_exists('FreeSpins', $currentLog) && $currentLog['FreeSpinNumber'] >= $currentLog['FreeSpins']) if (!$freeSpins) goto NewSpin;
        // проверить множители если фриспины
        $multipliers = false;
        if ($currentLog && array_key_exists('FreeSpins', $currentLog) && $currentLog['FreeState'] != 'LastFreeSpin')
            $multipliers = Multiple::getBonanzaMultiple($slotArea['SlotArea'], $gameSettings, $currentLog);


        //составить все в удобный массив
        $logAndServer = LogAndServer::getResult($slotArea, $index, $counter, $bet, $lines, $doubleChance, $reelSet,
            $win, $currentLog, $user, $freeSpins, $multipliers, $changeBalance, $bank, $game);

        // проверить можно ли выиграть
        if ($win['TotalWin'] > 0 || array_key_exists('tmb_win', $logAndServer['Log']))
            $win_permission = WinPermission::winCheck($freeSpins,$buyFS,$bank,$logAndServer['Log'],$win['TotalWin'], $multipliers, $currentLog);
        else $win_permission = true;
        if (!$win_permission) goto NewSpin;
        // распределить деньги в банк и записать в статистику
        SwitchMoney::set($changeBalance, $shop, $bank, $jpgs, $user, $game, $callbackUrl, $win['TotalWin'], $slotArea, $freeSpins, $logAndServer['Log'], $win_permission);
        //записать лог
        Log::setLog($logAndServer['Log'], $game->id, $user->id, $user->shop_id);

        //отдать серверу
        $response = implode('&', $logAndServer['Server']);
        return $response;
    }

}
