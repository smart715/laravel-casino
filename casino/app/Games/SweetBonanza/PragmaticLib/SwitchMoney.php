<?php

namespace VanguardLTE\Games\SweetBonanza\PragmaticLib;


class SwitchMoney
{

    public static function set($bet, $shop, $bank, $jpgs, $user, $game, $callbackUrl, $win, $slotArea, $freespins, $currentLog, $win_permisson){
        if ($bet){ // если ставка не 0
            $bet *= -1;
            $user->decrement('balance', $bet);
            $user->save();
        }
        if (is_array($win_permisson)) $win = $win_permisson['CurrentWin'];

        $toBonus = array_key_exists('FSPay', $currentLog) ? $currentLog['FSPay'] : false;

        $toJackpot = Jackpots::toJP($bet, $jpgs); // распределить по джекпотам
        $toProfit = $bet * ((100 - $shop->percent) / 100); // посчитать прибыль владельцу

        $toSlotBank = SlotBank::addBank($bet, $bank, $toJackpot, $toProfit, $toBonus); // добавить деньги в банк и посчитать сколько денег вообще в слот

        if (array_key_exists('FSPay', $currentLog)) $win = $currentLog['FSPay']; // если выпали фриспины то добавить к выигрышу

        $game->stat_out += $win;
        $game->stat_in += $toSlotBank; // добавить сумму внесенных в слот денег
        $game->save();

        Statistic::setStatistic($user, $win, $game, $bank, $bet, $toSlotBank, $toJackpot, $toProfit, $freespins, $slotArea);


        if (array_key_exists('FreeState', $currentLog)){
            $bank->decrement('bonus',$win);
        }else{
            $bank->decrement('slots',$win);
        }
        $bank->save();
    }
}
