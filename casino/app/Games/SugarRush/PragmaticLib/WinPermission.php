<?php

namespace VanguardLTE\Games\SugarRush\PragmaticLib;

class WinPermission
{
    public static function winCheck($freespins, $buyFS, $bank, &$currentState, $win, $multipliers, $log){
        // в классе возвращаем сумму выигрыша (за вычетом того что уже выплачено.
        // если выпали фриспины (FSPay) то проверяем куплены ли фриспины, если нет - проверяем есть ли деньги на выплату
        if ($freespins){
            // если фриспины не куплены - проверяем есть ли в банке деньги для выплаты
            if (!$buyFS === '0'){
                // проверяем есть ли в банке бонуса выплата за скаттеры
                if (array_key_exists('Pay', $freespins)){ // Если есть выплата за скаттеры
                    if ($bank->bonus < $freespins['Pay']) return false; // Если в банке достаточно денег для выплаты - платим иначе false
                }
            }
        }
        // если сейчас не идут фриспины - проверить есть ли сумма в банке для выплаты
        if (!$freespins && !array_key_exists('FreeSpinNumber', $currentState)){
            if ($bank->slots < $win) return false;
        }
        // если сейчас идут фриспины и есть выигрыш - то проверяем в логе BankCredit
        if (array_key_exists('FreeSpinNumber', $currentState) && $currentState['FreeState'] != 'FirstFreeSpin'){
            // Если сейчас последний респин - то обнуляем банк кредит
            if ($currentState['State'] === 'LastRespin' && array_key_exists('BankCredit', $log)) unset($log['BankCredit']);
            // если есть шары на поле - умножаем текущий выигрыш на шары
            if (array_key_exists('tmb_win', $currentState)) $win = $currentState['tmb_win'];
            if ($multipliers){
                $total_mult = 0;
                foreach ($multipliers as $multiplier) {
                    $total_mult += $multiplier['Multiplier'];
                }
                if($currentState['State'] == 'LastRespin')
                    $total_mult = $currentState['PrgSum'];
                else $total_mult += $currentState['PrgSum'];
                // умножаем текущий выигрыш на общий множитель
                if (array_key_exists('tmb_win', $currentState)) $win = $currentState['tmb_win'] * $total_mult;
                else $win = $win * $total_mult;
            }

            if ($bank->bonus < $win) return false;
            else { // если деньги на выплату есть - платим,
                if($currentState['State'] != 'LastRespin')
                    $win = 0;
                return ['CurrentWin' => $win];
            }
        }

        return true;
    }

}
