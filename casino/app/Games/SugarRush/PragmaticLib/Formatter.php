<?php

namespace VanguardLTE\Games\SugarRush\PragmaticLib;


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
        $addLog = self::situationToLog($log, $win, $freeSpins, $toLog); // обычные ситуации с играми
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
            'sh=5',
            'c='.$logData['Bet'],
            'sver=5',
            'counter='.$logData['Counter'],
            'l='.$logData['Lines'],
            's='.implode(',', $logData['SlotArea']),
            'w='.$logData['Win'],
        ];

        // Если не было респина и появился первый выигрыш
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
        // Если был респин и появился еще выигрыш
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
        // если это последний респин
        if ($logData['State'] === 'LastRespin'){
            $repl = array_keys($response, 'na=s');
            $response[$repl[0]] = 'na=c'; // заменить значение
            $addResponse = [
                'rs_t='.$logData['Respin'],
                'rs_win='.$logData['RespinWin'],
                'tmb_res='.$logData['TotalWin'],
                'tmb_win='.$logData['TotalWin'],

            ];
            $response = array_merge($response, $addResponse);
        }

        if (array_key_exists('FSPay', $logData)){ // при выигрыше фриспина показать где скаттеры и сколько оплата
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
        // вернуть позиции в подходящем виде
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
    //если нет лога то по умолчанию состояние Spin
        if (!$log || !array_key_exists('State', $log)) $state = 'Spin';
        else $state = $log['State'];
    //если нет выигрыша, и нет предыдущего респина - то состояние Spin
        if ($win['TotalWin'] == 0 && $state != 'Respin'){
            $addLog = [ 'State' => 'Spin' ];
        }
	//если есть выигрыш, но нет фриспинов, и нет предыдущего респина - то FirstRespin
        if ($win['TotalWin'] > 0 && $state != 'Respin'){
            $addLog = [
                'Respin' => 0,
                'RespinWin' => 0,
                'WinLines' => $win['WinLines'],
                'State' => 'FirstRespin'
            ];
        }
	//если есть выигрыш, и есть предыдущий респин - то Respin
        if ($win['TotalWin'] > 0 && ($state === 'Respin' || $state === 'FirstRespin')){
            $addLog = [
                'Respin' => $log['Respin'] + 1,
                'RespinWin' => $log['RespinWin'] + $win['TotalWin'],
                'WinLines' => $win['WinLines'],
                'TotalWin' => $log['TotalWin'] + $win['TotalWin'],
                'State' => 'Respin'
            ];
        }
	//если нет выигрыша, и есть предыдущий респин - то LastRespin
        if ($win['TotalWin'] == 0 && ($state === 'Respin' || $state === 'FirstRespin')){
            $addLog = [
                'Respin' => $log['Respin'],
                'RespinWin' => $log['RespinWin'],
                'TotalWin' => $log['TotalWin'],
                'State' => 'LastRespin'
            ];
        }


	//если нет выигрыша, и это фриспины, и предыдущий спин или ласт респин - то состояние Spin
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

	//Если LastRespin или FreeLastRespin - проверяем есть ли выигрыш фриспинов и добавляем фриспины или дополнительные фриспины и выигрыш от скаттеров

        return $addLog;
    }
}
