<?php 
namespace VanguardLTE\Games\WolfMoonRisingBS
{
    set_time_limit(5);
    class Server
    {
        public function get($request, $game)
        {
            function get_($request, $game)
            {
                \DB::transaction(function() use ($request, $game)
                {
                    try
                    {
                        $userId = \Auth::id();
                        if( $userId == null ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"","serverResponse":"invalid login"}';
                            exit( $response );
                        }
                        $slotSettings = new SlotSettings($game, $userId);
                        if( !$slotSettings->is_active() ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"","serverResponse":"Game is disabled"}';
                            exit( $response );
                        }
                        $postData = json_decode(trim(file_get_contents('php://input')), true);
                        $result_tmp = [];
                        $aid = '';
                        $aid = (string)$_POST['CMD'];
                        switch( $aid ) 
                        {
                            case 'ENTER':
                                $balanceInCents = sprintf('%01.2f', $slotSettings->GetBalance());
                                $lastEvent = $slotSettings->GetHistory();
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                if( $lastEvent != 'NULL' ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $lastEvent->serverResponse->bonusWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $lastEvent->serverResponse->totalFreeGames);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $lastEvent->serverResponse->currentFreeGames);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $lastEvent->serverResponse->totalWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', $lastEvent->serverResponse->Balance);
                                    $reels = $lastEvent->serverResponse->reelsSymbols;
                                    $curReels = '' . $reels->reel1[0] . ',' . $reels->reel1[1] . ',' . $reels->reel1[2];
                                    $curReels .= ('|' . $reels->reel2[0] . ',' . $reels->reel2[1] . ',' . $reels->reel2[2]);
                                    $curReels .= ('|' . $reels->reel3[0] . ',' . $reels->reel3[1] . ',' . $reels->reel3[2]);
                                    $curReels .= ('|' . $reels->reel4[0] . ',' . $reels->reel4[1] . ',' . $reels->reel4[2]);
                                    $curReels .= ('|' . $reels->reel5[0] . ',' . $reels->reel5[1] . ',' . $reels->reel5[2]);
                                    $lines = $lastEvent->serverResponse->slotLines;
                                    $bet = $lastEvent->serverResponse->slotBet;
                                }
                                else
                                {
                                    $tr = (object)[
                                        'reel1' => [
                                            rand(1, 6), 
                                            rand(1, 6), 
                                            rand(1, 6)
                                        ], 
                                        'reel2' => [
                                            rand(1, 6), 
                                            rand(1, 6), 
                                            rand(1, 6)
                                        ], 
                                        'reel3' => [
                                            rand(1, 6), 
                                            rand(1, 6), 
                                            rand(1, 6)
                                        ], 
                                        'reel4' => [
                                            rand(1, 6), 
                                            rand(1, 6), 
                                            rand(1, 6)
                                        ], 
                                        'reel5' => [
                                            rand(1, 6), 
                                            rand(1, 6), 
                                            rand(1, 6)
                                        ]
                                    ];
                                    $bet = 0;
                                    $lines = 25;
                                    $curReels = '' . $tr->reel1[0] . ',' . $tr->reel1[1] . ',' . $tr->reel1[2];
                                    $curReels .= ('|' . $tr->reel2[0] . ',' . $tr->reel2[1] . ',' . $tr->reel2[2]);
                                    $curReels .= ('|' . $tr->reel3[0] . ',' . $tr->reel3[1] . ',' . $tr->reel3[2]);
                                    $curReels .= ('|' . $tr->reel4[0] . ',' . $tr->reel4[1] . ',' . $tr->reel4[2]);
                                    $curReels .= ('|' . $tr->reel5[0] . ',' . $tr->reel5[1] . ',' . $tr->reel5[2]);
                                }
                                $slotSettings->SetGameData($slotSettings->slotId . 'Lines', $lines);
                                $slotSettings->SetGameData($slotSettings->slotId . 'Betline', $bet);
                                if( $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') < $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') ) 
                                {
                                    $betline = $slotSettings->Bet[$bet];
                                    $bwCoin = round($slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') / $betline);
                                    $fwCoin = round($slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') / $betline);
                                    $freeLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                    $result_tmp[0] = 'RESULT=OK&BALANCE=' . $balanceInCents . '&game_mode=free&server_type=AP&LASTHAND=YES&FREESPIN=' . $freeLeft . '&STATE=FREESPIN&IBETVALUES=' . implode('', $slotSettings->Bet) . '&LASTSTOPREEL=' . $curReels . '&COMPLEXWIN=' . $fwCoin . '&LASTBET=' . $bet . '|25|1&FREESPINS_PAYOUT=' . $bwCoin . '&AUTOPLAY_VALUES=5|10|15|20|25|30|40|50|100&DEFCOIN=' . $bet . '&POSSIBLE_LINES=25&DEFAULTNUMLINES=25&DEFAULTBETPERLINE=1&IPAYOUT2=10|2|2|0|0|0|0|0|0|0|0|2|0|50&IPAYOUT3=200|25|25|15|10|10|10|5|5|5|5|5|125|75&IPAYOUT4=2000|100|100|100|75|50|50|50|25|25|25|25|500|100&IPAYOUT5=250000|7500|7500|400|250|250|125|100|100|100|100|100|2500|125&FREESPIN_MULTIPLIER=2&FSCOUNT=12&SPECIAL_PAYOUT=25&BONUSBALANCE=0&GID=810&TIME=' . time() . '&END=0';
                                }
                                else
                                {
                                    $result_tmp[0] = 'RESULT=OK&BALANCE=' . $balanceInCents . '&game_mode=free&server_type=AP&LASTHAND=NO&AUTOPLAY_VALUES=5|10|15|20|25|30|40|50|100&IBETVALUES=' . implode('', $slotSettings->Bet) . '&DEFCOIN=' . $bet . '&POSSIBLE_LINES=25&DEFAULTNUMLINES=25&DEFAULTBETPERLINE=1&IPAYOUT2=10|2|2|0|0|0|0|0|0|0|0|2|0|50&IPAYOUT3=200|25|25|15|10|10|10|5|5|5|5|5|125|75&IPAYOUT4=2000|100|100|100|75|50|50|50|25|25|25|25|500|100&IPAYOUT5=250000|7500|7500|400|250|250|125|100|100|100|100|100|2500|125&FREESPIN_MULTIPLIER=2&FSCOUNT=12&SPECIAL_PAYOUT=25&BONUSBALANCE=0&GID=810&TIME=' . time() . '&END=0';
                                }
                                break;
                            case 'DOBONUS':
                            case 'PLACEBET':
                                $linesId = [];
                                $linesId[0] = [
                                    2, 
                                    2, 
                                    2, 
                                    2, 
                                    2
                                ];
                                $linesId[1] = [
                                    1, 
                                    1, 
                                    1, 
                                    1, 
                                    1
                                ];
                                $linesId[2] = [
                                    3, 
                                    3, 
                                    3, 
                                    3, 
                                    3
                                ];
                                $linesId[3] = [
                                    1, 
                                    2, 
                                    3, 
                                    2, 
                                    1
                                ];
                                $linesId[4] = [
                                    3, 
                                    2, 
                                    1, 
                                    2, 
                                    3
                                ];
                                $linesId[5] = [
                                    1, 
                                    1, 
                                    2, 
                                    1, 
                                    1
                                ];
                                $linesId[6] = [
                                    3, 
                                    3, 
                                    2, 
                                    3, 
                                    3
                                ];
                                $linesId[7] = [
                                    2, 
                                    3, 
                                    3, 
                                    3, 
                                    2
                                ];
                                $linesId[8] = [
                                    2, 
                                    1, 
                                    1, 
                                    1, 
                                    2
                                ];
                                $linesId[9] = [
                                    2, 
                                    1, 
                                    2, 
                                    1, 
                                    2
                                ];
                                $linesId[10] = [
                                    2, 
                                    3, 
                                    2, 
                                    3, 
                                    2
                                ];
                                $linesId[11] = [
                                    1, 
                                    2, 
                                    1, 
                                    2, 
                                    1
                                ];
                                $linesId[12] = [
                                    3, 
                                    2, 
                                    3, 
                                    2, 
                                    3
                                ];
                                $linesId[13] = [
                                    2, 
                                    2, 
                                    1, 
                                    2, 
                                    2
                                ];
                                $linesId[14] = [
                                    2, 
                                    2, 
                                    3, 
                                    2, 
                                    2
                                ];
                                $linesId[15] = [
                                    1, 
                                    2, 
                                    2, 
                                    2, 
                                    1
                                ];
                                $linesId[16] = [
                                    3, 
                                    2, 
                                    2, 
                                    2, 
                                    3
                                ];
                                $linesId[17] = [
                                    1, 
                                    2, 
                                    3, 
                                    3, 
                                    3
                                ];
                                $linesId[18] = [
                                    3, 
                                    2, 
                                    1, 
                                    1, 
                                    1
                                ];
                                $linesId[19] = [
                                    1, 
                                    3, 
                                    1, 
                                    3, 
                                    1
                                ];
                                $linesId[20] = [
                                    3, 
                                    1, 
                                    3, 
                                    1, 
                                    3
                                ];
                                $linesId[21] = [
                                    1, 
                                    1, 
                                    2, 
                                    3, 
                                    3
                                ];
                                $linesId[22] = [
                                    3, 
                                    3, 
                                    2, 
                                    1, 
                                    1
                                ];
                                $linesId[23] = [
                                    2, 
                                    1, 
                                    2, 
                                    3, 
                                    2
                                ];
                                $linesId[24] = [
                                    2, 
                                    3, 
                                    2, 
                                    1, 
                                    2
                                ];
                                if( $aid == 'DOBONUS' ) 
                                {
                                    $postData['slotEvent'] = 'freespin';
                                    $lines = $slotSettings->GetGameData($slotSettings->slotId . 'Lines');
                                    $be = $slotSettings->GetGameData($slotSettings->slotId . 'Betline');
                                    $betline = $slotSettings->Bet[$be];
                                }
                                else
                                {
                                    $tmpInputData = explode('|', $_POST['BET']);
                                    $lines = (int)$tmpInputData[1];
                                    $betline = $slotSettings->Bet[$tmpInputData[0]];
                                    $slotSettings->SetGameData($slotSettings->slotId . 'Lines', $lines);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'Betline', $tmpInputData[0]);
                                    $postData['slotEvent'] = 'bet';
                                }
                                $allbet = $betline * $lines;
                                $balanceInCents = sprintf('%01.2f', $slotSettings->GetBalance());
                                if( $postData['slotEvent'] == 'bet' ) 
                                {
                                    if( $lines <= 0 || $betline <= 0.0001 ) 
                                    {
                                        $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid bet state"}';
                                        exit( $response );
                                    }
                                    if( $slotSettings->GetBalance() < $allbet ) 
                                    {
                                        $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid balance"}';
                                        exit( $response );
                                    }
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') < $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') && $postData['slotEvent'] == 'freespin' ) 
                                    {
                                        $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid bonus state"}';
                                        exit( $response );
                                    }
                                }
                                if( $postData['slotEvent'] != 'freespin' ) 
                                {
                                    if( !isset($postData['slotEvent']) ) 
                                    {
                                        $postData['slotEvent'] = 'bet';
                                    }
                                    $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                                    $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                                    $jackState = $slotSettings->UpdateJackpots($allbet);
                                    if( is_array($jackState) ) 
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'JackWinID', $jackState['isJackId']);
                                    }
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', sprintf('%01.2f', $slotSettings->GetBalance()) * 100);
                                    $bonusMpl = 1;
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1);
                                    $bonusMpl = $slotSettings->slotFreeMpl;
                                }
                                $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $allbet, $lines);
                                $winType = $winTypeTmp[0];
                                $spinWinLimit = $winTypeTmp[1];
                                $symViewPosition = [
                                    [
                                        0, 
                                        3, 
                                        6, 
                                        9, 
                                        12
                                    ], 
                                    [
                                        1, 
                                        4, 
                                        7, 
                                        10, 
                                        13
                                    ], 
                                    [
                                        2, 
                                        5, 
                                        8, 
                                        11, 
                                        14
                                    ]
                                ];
                                for( $i = 0; $i <= 2000; $i++ ) 
                                {
                                    $totalWin = 0;
                                    $lineWins = [];
                                    $cWins = [
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0
                                    ];
                                    $cWins2 = [
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0
                                    ];
                                    $wild = ['0'];
                                    $scatter = '12';
                                    $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                    for( $k = 0; $k < 25; $k++ ) 
                                    {
                                        $tmpStringWin = '';
                                        for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                                        {
                                            $csym = (string)$slotSettings->SymbolGame[$j];
                                            if( $csym == $scatter || !isset($slotSettings->Paytable['SYM_' . $csym]) ) 
                                            {
                                            }
                                            else
                                            {
                                                $s = [];
                                                $s[0] = $reels['reel1'][$linesId[$k][0] - 1];
                                                $s[1] = $reels['reel2'][$linesId[$k][1] - 1];
                                                $s[2] = $reels['reel3'][$linesId[$k][2] - 1];
                                                $s[3] = $reels['reel4'][$linesId[$k][3] - 1];
                                                $s[4] = $reels['reel5'][$linesId[$k][4] - 1];
                                                $svp = [];
                                                $svp[0] = $symViewPosition[$linesId[$k][0] - 1][0];
                                                $svp[1] = $symViewPosition[$linesId[$k][1] - 1][1];
                                                $svp[2] = $symViewPosition[$linesId[$k][2] - 1][2];
                                                $svp[3] = $symViewPosition[$linesId[$k][3] - 1][3];
                                                $svp[4] = $symViewPosition[$linesId[$k][4] - 1][4];
                                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) ) 
                                                {
                                                    $mpl = 1;
                                                    if( in_array($s[0], $wild) && in_array($s[1], $wild) ) 
                                                    {
                                                        $mpl = 0;
                                                    }
                                                    else if( in_array($s[0], $wild) || in_array($s[1], $wild) ) 
                                                    {
                                                        $mpl = $slotSettings->slotWildMpl;
                                                    }
                                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][2] * $betline * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $cWins2[$k] = $slotSettings->Paytable['SYM_' . $csym][2];
                                                        $tmpStringWin = '' . $k . '-' . $slotSettings->Paytable['SYM_' . $csym][2] . '-' . $svp[0] . ',' . $svp[1];
                                                    }
                                                }
                                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                                {
                                                    $mpl = 1;
                                                    if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) ) 
                                                    {
                                                        $mpl = 0;
                                                    }
                                                    else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) ) 
                                                    {
                                                        $mpl = $slotSettings->slotWildMpl;
                                                    }
                                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betline * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $cWins2[$k] = $slotSettings->Paytable['SYM_' . $csym][3];
                                                        $tmpStringWin = '' . $k . '-' . $slotSettings->Paytable['SYM_' . $csym][3] . '-' . $svp[0] . ',' . $svp[1] . ',' . $svp[2];
                                                    }
                                                }
                                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                                                {
                                                    $mpl = 1;
                                                    if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) ) 
                                                    {
                                                        $mpl = 0;
                                                    }
                                                    else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) ) 
                                                    {
                                                        $mpl = $slotSettings->slotWildMpl;
                                                    }
                                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][4] * $betline * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $cWins2[$k] = $slotSettings->Paytable['SYM_' . $csym][4];
                                                        $tmpStringWin = '' . $k . '-' . $slotSettings->Paytable['SYM_' . $csym][4] . '-' . $svp[0] . ',' . $svp[1] . ',' . $svp[2] . ',' . $svp[3];
                                                    }
                                                }
                                                if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                                                {
                                                    $mpl = 1;
                                                    if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) && in_array($s[4], $wild) ) 
                                                    {
                                                        $mpl = 0;
                                                    }
                                                    else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) || in_array($s[4], $wild) ) 
                                                    {
                                                        $mpl = $slotSettings->slotWildMpl;
                                                    }
                                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][5] * $betline * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $cWins2[$k] = $slotSettings->Paytable['SYM_' . $csym][5];
                                                        $tmpStringWin = '' . $k . '-' . $slotSettings->Paytable['SYM_' . $csym][5] . '-' . $svp[0] . ',' . $svp[1] . ',' . $svp[2] . ',' . $svp[3] . ',' . $svp[4];
                                                    }
                                                }
                                            }
                                        }
                                        if( $cWins[$k] > 0 && $tmpStringWin != '' ) 
                                        {
                                            array_push($lineWins, $tmpStringWin);
                                            $totalWin += $cWins[$k];
                                        }
                                    }
                                    $scattersWin = 0;
                                    $scattersStr = '';
                                    $scattersCount = 0;
                                    $scPos = [];
                                    $scp = 0;
                                    for( $r = 1; $r <= 5; $r++ ) 
                                    {
                                        for( $p = 0; $p <= 2; $p++ ) 
                                        {
                                            if( $reels['reel' . $r][$p] == $scatter ) 
                                            {
                                                $scattersCount++;
                                                $scPos[] = $scp;
                                            }
                                            $scp++;
                                        }
                                    }
                                    $scattersWin = $slotSettings->Paytable['SYM_' . $scatter][$scattersCount] * $allbet * $bonusMpl;
                                    $sgwin = 0;
                                    if( $scattersCount >= 3 ) 
                                    {
                                        $scattersStr = '';
                                    }
                                    $totalWin += $scattersWin;
                                    if( $i > 1000 ) 
                                    {
                                        $winType = 'none';
                                    }
                                    if( $i > 1500 ) 
                                    {
                                        $response = '{"responseEvent":"error","responseType":"' . $postData['slotEvent'] . '","serverResponse":"' . $totalWin . ' Bad Reel Strip"}';
                                        exit( $response );
                                    }
                                    if( $slotSettings->MaxWin < ($totalWin * $slotSettings->CurrentDenom) ) 
                                    {
                                    }
                                    else
                                    {
                                        $minWin = $slotSettings->GetRandomPay();
                                        if( $i > 700 ) 
                                        {
                                            $minWin = 0;
                                        }
                                        if( $slotSettings->increaseRTP && $winType == 'win' && $totalWin < ($minWin * $allbet) ) 
                                        {
                                        }
                                        else if( $scattersCount >= 3 && $winType != 'bonus' ) 
                                        {
                                        }
                                        else if( $totalWin <= $spinWinLimit && $winType == 'bonus' ) 
                                        {
                                            $cBank = $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''));
                                            if( $cBank < $spinWinLimit ) 
                                            {
                                                $spinWinLimit = $cBank;
                                            }
                                            else
                                            {
                                                break;
                                            }
                                        }
                                        else if( $totalWin > 0 && $totalWin <= $spinWinLimit && $winType == 'win' ) 
                                        {
                                            $cBank = $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''));
                                            if( $cBank < $spinWinLimit ) 
                                            {
                                                $spinWinLimit = $cBank;
                                            }
                                            else
                                            {
                                                break;
                                            }
                                        }
                                        else if( $totalWin == 0 && $winType == 'none' ) 
                                        {
                                            break;
                                        }
                                    }
                                }
                                if( $totalWin > 0 ) 
                                {
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                                    $slotSettings->SetBalance($totalWin);
                                }
                                $reportWin = $totalWin;
                                if( $postData['slotEvent'] == 'freespin' ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') + $totalWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') + $totalWin);
                                    $balanceInCents = $slotSettings->GetGameData($slotSettings->slotId . 'FreeBalance');
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                }
                                $fs = 0;
                                $freeDataStr = '&TOTALWIN=' . round($totalWin / $betline) . '&STATE=MAIN&COMPLEXWIN=' . round($totalWin / $betline);
                                if( $scattersCount >= 3 ) 
                                {
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $slotSettings->slotFreeCount[$scattersCount]);
                                    }
                                    else
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->slotFreeCount[$scattersCount]);
                                    }
                                    $bwCoin = round($slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') / $betline);
                                    $fwCoin = round($slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') / $betline);
                                    $freeLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                    $freeDataStr = '&FSADD=' . $slotSettings->slotFreeCount[$scattersCount] . '&FREESPIN=' . $freeLeft . '&SCATTERWIN=' . round($scattersWin / $allbet) . '|' . implode(',', $scPos) . '&FREESPINPAYOUT=' . $bwCoin . '&STATE=FREESPIN';
                                    $fs = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                }
                                $curReels = $reels['reel1'][0] . ',' . $reels['reel1'][1] . ',' . $reels['reel1'][2];
                                $curReels .= ('|' . $reels['reel2'][0] . ',' . $reels['reel2'][1] . ',' . $reels['reel2'][2]);
                                $curReels .= ('|' . $reels['reel3'][0] . ',' . $reels['reel3'][1] . ',' . $reels['reel3'][2]);
                                $curReels .= ('|' . $reels['reel4'][0] . ',' . $reels['reel4'][1] . ',' . $reels['reel4'][2]);
                                $curReels .= ('|' . $reels['reel5'][0] . ',' . $reels['reel5'][1] . ',' . $reels['reel5'][2]);
                                $winString = '';
                                if( count($lineWins) ) 
                                {
                                    if( $postData['slotEvent'] == 'freespin' || $winType == 'bonus' ) 
                                    {
                                        $winString = '&PAYOUT_LEFT=' . implode('|', $lineWins);
                                    }
                                    else
                                    {
                                        $winString = '&DUPP=' . round($totalWin * 100) . ',' . round($totalWin / 2 * 100) . '|' . round($totalWin * 2 * 100) . ',' . round(($totalWin * 2 * 100) / 2) . '&GAMBLE=ALL&PAYOUT_LEFT=' . implode('|', $lineWins);
                                    }
                                }
                                $jsSpin = '' . json_encode($reels) . '';
                                $jsJack = '' . json_encode($slotSettings->Jackpots) . '';
                                $response = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{"slotLines":' . $lines . '' . $slotSettings->GetGameData($slotSettings->slotId . 'FirstSpin') . ',"slotBet":' . $slotSettings->GetGameData($slotSettings->slotId . 'Betline') . ',"totalFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . ',"currentFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') . ',"Balance":' . $balanceInCents . ',"afterBalance":' . $balanceInCents . ',"bonusWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') . ',"totalWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') . ',"winLines":["' . $winString . '"],"Jackpots":' . $jsJack . ',"reelsSymbols":' . $jsSpin . '}}';
                                $slotSettings->SaveLogReport($response, $allbet, $lines, $reportWin, $postData['slotEvent']);
                                $winstring = '';
                                $balanceInCents = sprintf('%01.2f', $slotSettings->GetBalance());
                                if( $postData['slotEvent'] == 'freespin' ) 
                                {
                                    $bwCoin = round($slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') / $betline);
                                    $fwCoin = round($slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') / $betline);
                                    $freeLeft = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') - $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame');
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') ) 
                                    {
                                        $freeDataStr = '&TOTALWIN=' . ($totalWin / $betline) . '&FREESPINS_PAYOUT=' . $bwCoin . '&STATE=MAIN&COMPLEXWIN=' . $fwCoin . '';
                                    }
                                    else
                                    {
                                        $freeDataStr = '&TOTALWIN=' . ($totalWin / $betline) . '&FREESPINS_PAYOUT=' . $bwCoin . '&STATE=FREESPIN&FSADD=0&COMPLEXWIN=' . $fwCoin . '&FREESPIN=' . $freeLeft;
                                    }
                                }
                                $result_tmp[0] = 'RESULT=OK&BALANCE=' . $balanceInCents . '&STOPREEL=' . $curReels . '&BONUSBALANCE=0&GID=810&TIME=' . time() . '&END=0' . $winString . $freeDataStr;
                                break;
                            case 'DOUBLEUP':
                                $postData['slotEvent'] = 'gambleResult';
                                $Balance = $slotSettings->GetBalance();
                                $isGambleWin = rand(1, $slotSettings->GetGambleSettings());
                                $dealerCard = '';
                                $totalWin = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                $gambleWin = 0;
                                $statBet = $totalWin;
                                $gChoice = (string)$_POST['CHOICE'];
                                $gMode = (string)$_POST['GAMBLE'];
                                if( $slotSettings->MaxWin < ($totalWin * $slotSettings->CurrentDenom) ) 
                                {
                                    $isGambleWin = 0;
                                }
                                if( $gMode == 'HALF' ) 
                                {
                                    $winRatio = 1;
                                }
                                else
                                {
                                    $winRatio = 2;
                                }
                                if( $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : '')) < ($totalWin * $winRatio) ) 
                                {
                                    $isGambleWin = 0;
                                }
                                if( $isGambleWin == 1 ) 
                                {
                                    $gambleState = 'TRUE';
                                    $gambleWin = $totalWin;
                                    $totalWin = $totalWin * $winRatio;
                                    if( $gChoice == 'HEADS' ) 
                                    {
                                        $dealerCard = 'HEADS';
                                    }
                                    else
                                    {
                                        $dealerCard = 'TAILS';
                                    }
                                    $dup = round($totalWin * 100) . ',' . round($totalWin / 2 * 100) . '|' . round($totalWin * 2 * 100) . ',' . round(($totalWin * 2 * 100) / 2);
                                }
                                else
                                {
                                    $gambleState = 'FALSE';
                                    $gambleWin = -1 * $totalWin;
                                    $totalWin = 0;
                                    if( $gChoice == 'HEADS' ) 
                                    {
                                        $dealerCard = 'TAILS';
                                    }
                                    else
                                    {
                                        $dealerCard = 'HEADS';
                                    }
                                    $dup = '0,0|0,0';
                                }
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                $slotSettings->SetBalance($gambleWin);
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $gambleWin * -1);
                                $afterBalance = $slotSettings->GetBalance();
                                $jsSet = '{"dealerCard":"' . $dealerCard . '","gambleState":"' . $gambleState . '","totalWin":' . $totalWin . ',"afterBalance":' . $afterBalance . ',"Balance":' . $Balance . '}';
                                $response_log = '{"responseEvent":"gambleResult","serverResponse":' . $jsSet . '}';
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $totalWin);
                                $slotSettings->SaveLogReport($response_log, $statBet, 1, $gambleWin, $postData['slotEvent']);
                                $result_tmp[0] = 'RESULT=OK&BALANCE=' . $afterBalance . '&COIN=' . $dealerCard . '&DUWIN=' . $gambleState . '&STATE=MAIN&GAMBLE=' . $gMode . '&DUPP=' . $dup . '&BONUSBALANCE=0&GID=810&TIME=' . time() . '&END=0';
                                break;
                        }
                        $response = implode('------:::', $result_tmp);
                        $slotSettings->SaveGameData();
                        $slotSettings->SaveGameDataStatic();
                        echo $response;
                    }
                    catch( \Exception $e ) 
                    {
                        if( isset($slotSettings) ) 
                        {
                            $slotSettings->InternalErrorSilent($e);
                        }
                        else
                        {
                            $strLog = '';
                            $strLog .= "\n";
                            $strLog .= ('{"responseEvent":"error","responseType":"' . $e . '","serverResponse":"InternalError","request":' . json_encode($_REQUEST) . ',"requestRaw":' . file_get_contents('php://input') . '}');
                            $strLog .= "\n";
                            $strLog .= ' ############################################### ';
                            $strLog .= "\n";
                            $slg = '';
                            if( file_exists(storage_path('logs/') . 'GameInternal.log') ) 
                            {
                                $slg = file_get_contents(storage_path('logs/') . 'GameInternal.log');
                            }
                            file_put_contents(storage_path('logs/') . 'GameInternal.log', $slg . $strLog);
                        }
                    }
                }, 5);
            }
            get_($request, $game);
        }
    }

}
