<?php

namespace VanguardLTE\Games\HerculesonofZeus\PragmaticLib;


class SwitchMoney
{

    public static function set($pur, $bet, $shop, $bank, $jpgs, $user, $game, $callbackUrl, $win, $slotArea, $fswin, $currentLog, $win_permisson){
        var_dump('6_0_bet='.$bet);
        if ($bet){ // if rate is not 0
            $bet *= -1;
            $user->decrement('balance', $bet);
            $user->save();
        }
        if (is_array($win_permisson)) $win = $win_permisson['win'];
        
        $toBonus = array_key_exists('fswin', $currentLog) ? $currentLog['fswin'] : false;
                
        if ($user->count_balance > 0 && $user->count_balance > $bet ) { // if you don't run out of money
            $user->count_balance -= $bet;
            $myMoney = $bet;
        }
        else if ($user->count_balance < $bet && $user->count_balance > 0) { // if own money is not enough to bet
            $myMoney = $user->count_balance;
            $user->count_balance = 0;
        }else $myMoney = 0;
        $user->save();
        $toJackpot = Jackpots::toJP($myMoney, $jpgs); // split into jackpots
        $toProfit = $myMoney * ((100 - $shop->percent) / 100); // calculate the owner's profit
        
        // var_dump('6_1_bet='.$bet.'_toJackpot='.$toJackpot.'_toProfit='.$toProfit);
        $toSlotBank = SlotBank::addBank($bet, $bank, $toJackpot, $toProfit, $toBonus); // add money to the bank and calculate how much money is in the slot
        var_dump('6_2');

        // if (array_key_exists('fswin', $currentLog)) $win = $currentLog['FSPay']; // if free spins fell out, then add to the winnings

        $game->stat_out += $win + $fswin;
        $game->stat_in += $toSlotBank; // add the amount of money deposited into the slot
        $game->rtp_stat_out += $win;
        $game->rtp_stat_in += $toSlotBank; // add the amount of money deposited into the slot for rtp calculation
        $game->save();

        Statistic::setStatistic($user, $win + $fswin, $game, $bank, $bet, $toSlotBank, $toJackpot, $toProfit, $fswin, $slotArea);
        // var_dump('6_3_fswin='.$fswin.'_win='.$win.'_slots='.$bank->slots.'_bonus='.$bank->bonus);

        $bank->decrement('bonus',$fswin);
        $bank->decrement('slots',$win);
        $bank->save();
        var_dump('6_4');
    }
}
