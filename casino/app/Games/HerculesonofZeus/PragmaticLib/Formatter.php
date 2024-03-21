<?php

namespace VanguardLTE\Games\HerculesonofZeus\PragmaticLib;


class Formatter
{
    public static function toLog($slotArea, $index, $counter, $bet, $lines, $doubleChance, $reelSet, $win, $log, $user, $freeSpins){
        $toLog = [
            'SymbolsAfter' => $slotArea['SymbolsAfter'],
            'SymbolsBelow' => $slotArea['SymbolsBelow'],
            'SlotArea' => $slotArea['SlotArea'],
            'Balance' => $user->balance,
            'Index' => $index,
            'Counter' => $counter,
            'Bet' => $bet,
            'Lines' => $lines,
            'DoubleChance' => $doubleChance,
            'ReelSet' => $reelSet,
            'TotalWin' => $win['TotalWin'],
            'Win' => $win['TotalWin'],
        ];
        $addLog = self::situationToLog($log, $win, $freeSpins, $toLog); // common gaming situations
        return array_merge($toLog, $addLog);

    }

    public static function toServer($logData){
        $response = [
            'tw='.$logData['TotalWin'],
            'prg_m=wm',
            'balance='.$logData['Balance'],
            'prg=1',
            'index='.$logData['Index'],
            'balance_cash='.$logData['Balance'],
            'reel_set='.$logData['ReelSet'],
            'balance_bonus=0.00',
            'na=s',
            'bl='.$logData['DoubleChance'],
            'stime='.time()*1000,
            'sa='.implode(',', $logData['SymbolsAfter']),
            'sb='.implode(',', $logData['SymbolsBelow']),
            'sh=3',
            'c='.$logData['Bet'],
            'sver=5',
            'counter='.$logData['Counter'],
            'l='.$logData['Lines'],
            's='.implode(',', $logData['SlotArea']),
            'w='.$logData['Win'],
        ];

        // If there was no respin and the first win appeared
        if ($logData['State'] === 'FirstRespin' && $logData['Win'] > 0){
            $positions = self::positionsToServer($logData['WinLines']);
            $addResponse = [
                'rs=t',
                'rs_p='.$logData['Respin'],
                'rs_c=1',
                'rs_m=1',
                'tmb_win='.$logData['TotalWin'],

            ];
            $response = array_merge($response, $positions, $addResponse);
        }
        // If there was a respin and another win appeared
        if ($logData['State'] === 'Respin' && $logData['Win'] > 0){
            $positions = self::positionsToServer($logData['WinLines']);
            $addResponse = [
                'rs_p='.$logData['Respin'],
                'rs_c=1',
                'rs_m=1',
                'tmb_win='.$logData['TotalWin'],
                'rs_win='.$logData['RespinWin'],

            ];
            $response = array_merge($response, $positions, $addResponse);
        }
        // if this is the last respin
        if ($logData['State'] === 'LastRespin'){
            $repl = array_keys($response, 'na=s');
            $response[$repl[0]] = 'na=c'; // replace value
            $addResponse = [
                'rs_t='.$logData['Respin'],
                'rs_win='.$logData['RespinWin'],
                'tmb_res='.$logData['TotalWin'],
                'tmb_win='.$logData['TotalWin'],

            ];
            $response = array_merge($response, $addResponse);
        }

        if (array_key_exists('FSPay', $logData)){ // When you win a free spin, show where the scatters are and how much the payment is
            $responseFs[] = 'psym='.$logData['Scatter'].'~'.$logData['FSPay'].'~'.implode(',', $logData['ScatterPositions']);
        }
        if (array_key_exists('FreeSpinNumber', $logData) && $logData['FreeSpinNumber'] < $logData['FreeSpins']){
            $responseFs = [
                'fsmul=1',
                'fsmax=10',
                'fswin=0.00',
                'fs='.$logData['FreeSpinNumber'],
                'fsres=0.00',
            ];
            $response = array_merge($response, $responseFs);
        }
        if(array_key_exists('FreeState', $logData) && $logData['FreeState'] == 'LastFreeSpin'){
            $responseFs = [
                'fsmul_total=1',
                'fswin_total=0.00',
                'fs_total='.$logData['FreeSpinNumber'],
                'fsres_total=0.00',
                'fs_bought=10'
            ];
            $response = array_merge($response, $responseFs);
        }

/*        $toWin = [

            // выигрыш
            'tw=1.60', // общий выигрыш
            'tmb=0,8~1,8~2,8~6,8~7,8~8,8~10,8~16,8', // описание выигрышных символов чтобы их убрать // 0 символ, 8 ~ 1 символ тоже 8 и тд
            'rs=t', // респин = требуется (true) // при первом выигрыше
            'tmb_win=1.60', // снова общий выигрыш
            'l0=0~1.60~0~1~2~6~7~8~10~16', // линия 0 и выигрыш по ней и позиции символов выигравших
            'rs_p=0', // номер респина // если нужен респин
            'rs_c=1', // если выигрыш // обязаловка постоянка
            'rs_m=1', // если выигрыш // обязаловка постоянка
            'w=1.60', // выигрыш за респин

            // респин
            'rs_win=0.50', // сколько выиграно за респины
            'w=0.50', // текущий выигрыш за этот спин
            'rs_p=1', // какой по счету респин

            // нет выигрыша после респина

            'tmb_res=2.90', // общий выигрыш
            'na=c', // постоянка, только после респина если нет выигрыша
            'rs_t=3', // добавляется если текущий спин не закончился респином (нет выигрыша) а предыдущие были в респине, берется из rs_p
            'tmb_win=2.90', // общий выигрыш
            'rs_win=1.30', // сколько было выиграно только за респины

        ];
        if (array_key_exists('FreeSpins', $logData)){
            if (array_key_exists('FSPay', $logData)){ // при выигрыше фриспина показать где скаттеры и сколько оплата
                $responseFs[] = 'psym='.$logData['Scatter'].'~'.$logData['FSPay'].'~'.implode(',', $logData['ScatterPositions']);
            }
            if ($logData['State'] === 'LastRespin'){ // если фриспины кончились - то подводим итог
                $responseFs = [
                    'fsmul_total=1',
                    'fswin_total=0.00',
                    'fs_total='.$logData['FreeSpinNumber'],
                    'fsres_total=0.00',
                    'fs_bought=10'
                ];
            }else{
                $responseFs = [
                    'fsmul=1',
                    'fsmax=10',
                    'fswin=0.00',
                    'fs='.$logData['FreeSpinNumber'],
                    'fsres=0.00',
                    'fs_bought=10'
                ];
            }
            $response = array_merge($response, $responseFs);
        }

        if ($log && array_key_exists('FreeSpins',$log)){ // если в логе уже есть фриспины
            $toLog['FreeSpins'] = $log['FreeSpins'];
            $toLog['FreeSpinNumber'] = $log['FreeSpinNumber'] +1;
        }
        if ($freeSpins && array_key_exists('FreeSpins', $freeSpins)){ // если выигрыш фриспинов
            $toLog['FreeSpins'] = $freeSpins['FreeSpins'];
            $toLog['FreeSpinNumber'] = 1;
            $toLog['FSPay'] = $freeSpins['Pay'];
            $toLog['Scatter'] = $freeSpins['Scatter'];
            $toLog['ScatterPositions'] = $freeSpins['ScatterPositions'];
            $toLog['TotalWin'] += $freeSpins['Pay'];
            $toLog['Win'] += $freeSpins['Pay'];
        }
        if (array_key_exists('FreeSpinNumber', $toLog) && $toLog['FreeSpinNumber'] >= $toLog['FreeSpins']){ // если фриспины закончились
            $toLog['EndFS'] = true;
        }*/
        return implode('&', $response);
    }

    private static function positionsToServer($winLines){
        // return positions in a suitable form
        $result = [];
        $tmb = [];
        $l = [];
        foreach ($winLines as $key => $winLine) {
            $l = 'l'.$key.'=0~'.$winLine['Pay'].'~'.implode('~', $winLine['Positions']);
            $tmb[] = implode(','.$winLine['WinSymbol'].'~', $winLine['Positions']);
            $result[] = $l;
        }
        $result[] = 'tmb='.implode('~', $tmb);
        return $result;

        //'tmb=1,10~2,11~4,11~6,11~7,10~8,10~10,11~11,10~12,11~14,10~17,10~21,10~23,11~25,11~27,10~29,11',

        //'l0=0~40.00~1~7~8~11~14~17~21~27',
        //'l1=0~25.00~2~4~6~10~12~23~25~29',
        //"WinLines":[{"WinSymbol":8,"CountSymbols":8,"Pay":1.60,"Positions":[10,11,12,13,16,17,18,19]}]
    }

    private static function situationToLog($log, $win, $freeSpins, $toLog){
    //if there is no log then the default state Spin
        if (!$log || !array_key_exists('State', $log)) $state = 'Spin';
        else $state = $log['State'];
    //if there is no win, and there is no previous respin - then the state Spin
        if ($win['TotalWin'] == 0 && $state != 'Respin'){
            $addLog = [ 'State' => 'Spin' ];
        }
	//if there is a win, but no free spins, and there is no previous respin - then FirstRespin
        if ($win['TotalWin'] > 0 && $state != 'Respin'){
            $addLog = [
                'Respin' => 0,
                'RespinWin' => 0,
                'WinLines' => $win['WinLines'],
                'State' => 'FirstRespin'
            ];
        }
	//if there is a win and there is a previous respin - то Respin
        if ($win['TotalWin'] > 0 && ($state === 'Respin' || $state === 'FirstRespin')){
            $addLog = [
                'Respin' => $log['Respin'] + 1,
                'RespinWin' => $log['RespinWin'] + $win['TotalWin'],
                'WinLines' => $win['WinLines'],
                'TotalWin' => $log['TotalWin'] + $win['TotalWin'],
                'State' => 'Respin'
            ];
        }
	//if there is no win and there is a previous respin - то LastRespin
        if ($win['TotalWin'] == 0 && ($state === 'Respin' || $state === 'FirstRespin')){
            $addLog = [
                'Respin' => $log['Respin'],
                'RespinWin' => $log['RespinWin'],
                'TotalWin' => $log['TotalWin'],
                'State' => 'LastRespin'
            ];
        }


	//if there is no win, and these are free spins, and the previous spin or last respin - then the state Spin
        if ($log && $freeSpins && !array_key_exists('FreeSpinNumber', $log)){
            $addFSLog = [
                'FreeState' => 'FirstFreeSpin',
                'FreeSpins' => $freeSpins['FreeSpins'],
                'FreeSpinNumber' => 1,
                'FSPay' => $freeSpins['Pay'],
                'Scatter' => $freeSpins['Scatter'],
                'ScatterPositions' => $freeSpins['ScatterPositions'],
                'TotalWin' => $toLog['TotalWin'] + $freeSpins['Pay'],
                'Win' => $toLog['TotalWin'] + $freeSpins['Pay']
            ];
            $addLog = array_merge($addLog, $addFSLog);
        }
        if ($log && array_key_exists('FreeSpinNumber', $log) && $log['FreeSpinNumber'] < $log['FreeSpins']){
            if ($addLog['State'] === 'Spin' || $addLog['State'] === 'FirstRespin') $addFS = 1; else $addFS = 0;
            $addFSLog = [
                'FreeState' => 'FreeSpin',
                'FreeSpins' => $log['FreeSpins'],
                'FreeSpinNumber' => $log['FreeSpinNumber'] + $addFS
            ];
            $addLog = array_merge($addLog, $addFSLog);
        }
        if ($log && array_key_exists('FreeSpinNumber', $log) && $log['FreeSpinNumber'] == $log['FreeSpins']){
            $addFSLog = [
                'FreeSpins' => $freeSpins['FreeSpins'],
                'FreeState' => 'LastFreeSpin',
            ];
            $addLog = array_merge($addLog, $addFSLog);
        }

	//If LastRespin or FreeLastRespin - check if there is a free spins win and add free spins or additional free spins and winnings from scatters

        return $addLog;
    }
}
