<?php

namespace VanguardLTE\Games\KingdomOfAsgard\PragmaticLib;

class LogAndServer
{
    public static function getResult($slotArea, $index, $counter, $bet, $lines, $reelSet, $win, $pur, 
                                     $log, $user, $changeBalance, $gameSettings){
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
            'tw' => $win['TotalWin'],
            'w' => $win['TotalWin'],
            'state' => 'spin',
            'na' => 'c',
            'reel_set' => $reelSet
        ];
        $time = (int) round(microtime(true) * 1000);
        $toServer = [
            'tw='.$toLog['w'],
            'balance='.number_format($toLog['Balance'], 2, ".", ""),
            'index='.$toLog['Index'],
            'balance_cash='.number_format($toLog['Balance'], 2, ".", ""),
            'balance_bonus=0.00',
            'na=c',
            'stime='.$time,
            'sa='.implode(',', $toLog['sa']),
            'sb='.implode(',', $toLog['sb']),
            'sh=3',
            'c='.$toLog['Bet'],
            'sver=5',
            'counter='.$toLog['Counter'],
            'l='.$toLog['l'],
            's='.implode(',', $toLog['s']),
            'w='.$toLog['w'],
            'reel_set='.$reelSet    ,
            'st=rect'
        ];
        var_dump('5_1_0');
        $nakey = array_keys($toServer, 'na=c')[0];
        $twkey = array_keys($toServer, 'tw='.$toLog['w'])[0];
        $wkey = array_keys($toServer, 'w='.$toLog['w'])[0];
        var_dump('5_1_1');

        // remove unnecessary values from mo
        foreach($slotArea['mo_t'] as $ind => $val)
            if($val != 'v'){
                $slotArea['mo_t'][$ind] = 'r';
                $slotArea['mo'][$ind] = 0;
            }
        $slotArea['mo_wpos'] = $slotArea['mo'];
        // add extra keys
        $addKeys = ['is', 'mo', 'mo_t', 'mo_wpos', 'ep', 'stf', 'accm', 'acci', 'accv', 'trail'];
        foreach($addKeys as $val){
            if(array_key_exists($val, $slotArea)){
                $toLog[$val] = $slotArea[$val];
                switch($val){ 
                    case 'msr':
                        $toServer[] = 'msr='.$slotArea[$val];
                        break;
                    case 'ep':
                        $toServer[] = 'ep='.$slotArea[$val];
                        break;
                    case 'stf':
                        $toServer[] = 'stf='.$slotArea[$val];
                        break;
                    case 'acci':
                        $toServer[] = 'acci='.$slotArea[$val];
                        break;
                    case 'accv':
                        $toServer[] = 'accv='.implode('~', $slotArea[$val]);
                        $accvkey = array_keys($toServer, 'accv='.implode('~', $slotArea[$val]))[0];
                        break;
                    case 'accm':
                        $toServer[] = 'accm='.implode('~', $slotArea[$val]);
                        break;
                    case 'trail':
                        $toServer[] = 'trail='.$slotArea[$val];
                        break;
                    default:
                        $toServer[] = $val.'='.implode(',', $toLog[$val]);
                }
            }
        }
        if($log && array_key_exists('fs', $log) || $log && array_key_exists('fs_total', $log) && $log['na'] == 's'){
            $prg_m = 'cp,acw';
            if(array_key_exists('prg', $log))
                $prg = $log['prg'];
            else $prg = [0, 0];
        }
        // if there is mo win add extra keys
        if(array_key_exists('mo_tv', $win)){
            $addToLog['mo_tv'] = $win['mo_tv'];
            $addToLog['mo_tw'] = $win['mo_tw'];
            $addToLog['mo_c'] = 1;
            $addToServer[] = 'mo_tv='.$win['mo_tv'];
            $addToServer[] = 'mo_tw='.$win['mo_tw'];
            $addToServer[] = 'mo_c=1';
            if($log && array_key_exists('fs', $log)){
                $prg[0] += $win['mo_tv'];
                $prg[1] += $win['mo_tw'];
            }
            $toLog = array_merge($toLog, $addToLog);
            $toServer = array_merge($toServer, $addToServer);
        }

        // handling FS
        $fswin = 0;
        // If this is the trigger to the Free Spin Mode
        if($pur === '0'){
            $scatterTmp = explode('~', $gameSettings['scatters']);
            $scatterTmp[2] = explode(',', $scatterTmp[2]);
            $scatterTmp[3] = explode(',', $scatterTmp[3]);
            $scatterCnt = count(array_keys($toLog['s'], 1));
            var_dump('scatters=', $scatterTmp);
            $fsmax = $scatterTmp[2][5 - $scatterCnt];
            $fsmul = $scatterTmp[3][5 - $scatterCnt];
            $addToLog = [
                'fsmul' => $fsmul,
                'fsmax' => $fsmax,
                'na' => 's',
                'fswin' => 0,
                'puri' => 0,
                'fs' => 1,
                'fsres' => 0
            ];
            $addToServer = [
                'fsmul='.$fsmul,
                'fsmax='.$fsmax,
                'fswin=0',
                'puri=0',
                'fs=1',
                'fsres=0'
            ];
            var_dump('5_1_1_2.8');
            $toServer[$nakey] = 's';
            $toLog = array_merge($toLog, $addToLog);
            $toServer = array_merge($toServer, $addToServer);
        }

        var_dump('5_2');
        // If this is free spin
        if($log && array_key_exists('fs', $log)){
            if($log['fs'] == $log['fsmax'] && ($slotArea['accv'][1] < $slotArea['accv'][0] * 4)){
                $addToLog = [
                    'fs_total' => $log['fs'],
                    'fswin_total' => $log['fswin'] + $win['TotalWin'] + $fswin,
                    'fsmul_total' => $log['fsmul'],
                    'fsres_total' => $log['fsres'] + $win['TotalWin'] + $fswin
                ];
                $addToServer = [
                    'fs_total='.$addToLog['fs_total'],
                    'fswin_total='.$addToLog['fswin_total'],
                    'fsmul_total='.$log['fsmul'],
                    'fsres_total='.$addToLog['fsres_total'],
                    'fsend_total=1'
                ];
                $toLog['state'] = 'lastRespin';
                $toLog['na'] = 'c';
                $toLog['w'] = $fswin + $win['TotalWin'];
                $toLog['tw'] = $log['tw'] + $toLog['w'] * $log['fsmul'];
                $toServer[$nakey] = 'na=c';
                $toServer[$twkey] = 'tw='.$toLog['tw'];
                $toServer[$wkey] = 'w='.$toLog['w'];
            }
            else {
                $addToLog = [
                    'fsmul' => $log['fsmul'],
                    'fsmax' => $log['fsmax'] == $log['fs'] ? $log['fsmax'] + $gameSettings['settings_addfs'] : $log['fsmax'],
                    'fswin' => $log['fswin'] + $win['TotalWin'] + $fswin,
                    'fs' => $log['fs'] + 1,
                    'fsres' => $log['fswin'] + $win['TotalWin'] + $fswin
                ];
                $addToServer = [
                    'fsmul='.$log['fsmul'],
                    'fsmax='.$addToLog['fsmax'],
                    'fswin='.$addToLog['fswin'],
                    'fs='.$addToLog['fs'],
                    'fsres='.$addToLog['fsres']
                ];
                if($log['fsmax'] == $log['fs']){
                    $toLog['accv'][0] = $toLog['accv'][2] = $toLog['accv'][0] + 1;
                    if($toLog['accv'][2] == 4)
                        $toLog['accv'][2] = 5;
                    $toServer[$accvkey] = 'accv='.implode('~', $toLog['accv']);
                    $addToServer[] = 'fsmore=8';
                }
                $toLog['state'] = 'respin';
                $toLog['na'] = 's';
                $toLog['w'] = $fswin + $win['TotalWin'];
                $toLog['tw'] = $log['tw'] + $toLog['w'] * $log['fsmul'];
                $toServer[$nakey] = 'na=s';
                $toServer[$twkey] = 'tw='.$toLog['tw'];
                $toServer[$wkey] = 'w='.$toLog['w'];
            }
            // if($pur === '1'){
            //     var_dump('3_pur='.$pur.'_fsmax='.$addToLog['fsmax']);
            // }
            if($fswin > 0){
                $addToLog['me'] = $me;
                $addToLog['mes'] = $mes;
                $addToLog['psym'] = $psym;
                $addToServer[] = 'me='.$me;
                $addToServer[] = 'mes='.$mes;
                $addToServer[] = 'psym='.$psym;
            }
            $addToLog['prg_m'] = $prg_m;
            $addToLog['prg'] = $prg;
            $addToServer[] = 'prg_m='.$addToLog['prg_m'];
            $addToServer[] = 'prg='.implode(',', $addToLog['prg']);
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
