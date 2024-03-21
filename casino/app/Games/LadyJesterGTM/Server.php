<?php 
namespace VanguardLTE\Games\LadyJesterGTM
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
                        $response = '';
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
                        if( $postData['slotEvent'] == 'update' ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $postData['slotEvent'] . '","serverResponse":"' . $slotSettings->GetBalance() . '"}';
                            exit( $response );
                        }
                        if( $postData['slotEvent'] == 'bet' || $postData['slotEvent'] == 'freespin' || $postData['slotEvent'] == 'respin' ) 
                        {
                            if( !in_array($postData['slotLines'], $slotSettings->gameLine) || !in_array($postData['slotBet'], $slotSettings->Bet) ) 
                            {
                                $response = '{"responseEvent":"error","responseType":"' . $postData['slotEvent'] . '","serverResponse":"invalid bet state"}';
                                exit( $response );
                            }
                            if( $slotSettings->GetBalance() < ($postData['slotLines'] * $postData['slotBet']) && $postData['slotEvent'] == 'bet' ) 
                            {
                                $response = '{"responseEvent":"error","responseType":"' . $postData['slotEvent'] . '","serverResponse":"invalid balance"}';
                                exit( $response );
                            }
                            if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') && $postData['slotEvent'] == 'freespin' ) 
                            {
                                $response = '{"responseEvent":"error","responseType":"' . $postData['slotEvent'] . '","serverResponse":"invalid bonus state"}';
                                exit( $response );
                            }
                        }
                        else if( $postData['slotEvent'] == 'slotGamble' && ($slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') <= 0 || $slotSettings->GetBalance() < $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin')) ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $postData['slotEvent'] . '","serverResponse":"invalid gamble state"}';
                            $slotSettings->InternalError($response . ' -- TotalWin = ' . $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') . ' -- Balance = ' . $slotSettings->GetBalance());
                            exit( $response );
                        }
                        if( $postData['slotEvent'] == 'getSettings' ) 
                        {
                            $lastEvent = $slotSettings->GetHistory();
                            if( $lastEvent != 'NULL' ) 
                            {
                                if( isset($lastEvent->serverResponse->expSymbol) ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'ExpSymbol', $lastEvent->serverResponse->expSymbol);
                                }
                                if( isset($lastEvent->serverResponse->bonusWin) ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $lastEvent->serverResponse->bonusWin);
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $lastEvent->serverResponse->totalWin);
                                }
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $lastEvent->serverResponse->totalFreeGames);
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $lastEvent->serverResponse->currentFreeGames);
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $lastEvent->serverResponse->totalWin);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', $lastEvent->serverResponse->Balance);
                                $LadyJesterGTMStacked = $lastEvent->serverResponse->stackedWilds;
                                for( $i = 0; $i < 5; $i++ ) 
                                {
                                    $slotSettings->SetGameData('LadyJesterGTMStacked.' . $i, $LadyJesterGTMStacked[$i]);
                                }
                                for( $r = 1; $r <= 5; $r++ ) 
                                {
                                    if( $slotSettings->GetGameData('LadyJesterGTMStacked.' . ($r - 1)) > 0 ) 
                                    {
                                        $slotSettings->SetGameData('LadyJesterGTMStacked.' . ($r - 1), -1);
                                    }
                                    if( $slotSettings->GetGameData('LadyJesterGTMStacked.' . ($r - 1)) == 0 ) 
                                    {
                                        $slotSettings->SetGameData('LadyJesterGTMStacked.' . ($r - 1), 1);
                                    }
                                }
                            }
                            $jsSet = json_encode($slotSettings);
                            $lang = json_encode(\Lang::get('games.' . $game));
                            $response = '{"responseEvent":"getSettings","slotLanguage":' . $lang . ',"serverResponse":' . $jsSet . '}';
                        }
                        else if( $postData['slotEvent'] == 'gamble5GetUserCards' ) 
                        {
                            $Balance = $slotSettings->GetBalance();
                            $isGambleWin = rand(1, $slotSettings->GetGambleSettings());
                            $dealerCard = $slotSettings->GetGameData('LadyJesterGTMDealerCard');
                            $totalWin = $slotSettings->GetGameData('LadyJesterGTMTotalWin');
                            $gambleWin = 0;
                            $gambleChoice = $postData['gambleChoice'] - 2;
                            $gambleState = '';
                            $gambleCards = [
                                2, 
                                3, 
                                4, 
                                5, 
                                6, 
                                7, 
                                8, 
                                9, 
                                10, 
                                11, 
                                12, 
                                13, 
                                14
                            ];
                            $gambleSuits = [
                                'C', 
                                'S', 
                                'D', 
                                'H'
                            ];
                            $gambleId = [
                                '', 
                                '', 
                                '2', 
                                '3', 
                                '4', 
                                '5', 
                                '6', 
                                '7', 
                                '8', 
                                '9', 
                                '10', 
                                'J', 
                                'Q', 
                                'K', 
                                'A'
                            ];
                            $userCard = 0;
                            $statBet = $totalWin;
                            if( $slotSettings->MaxWin < ($totalWin * $slotSettings->CurrentDenom) ) 
                            {
                                $isGambleWin = 0;
                            }
                            if( $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : '')) < ($totalWin * 2) ) 
                            {
                                $isGambleWin = 0;
                            }
                            if( $isGambleWin == 1 ) 
                            {
                                $userCard = rand($dealerCard, 14);
                            }
                            else
                            {
                                $userCard = rand(2, $dealerCard);
                            }
                            if( $dealerCard < $userCard ) 
                            {
                                $gambleWin = $totalWin;
                                $totalWin = $totalWin * 2;
                                $gambleState = 'win';
                            }
                            else if( $userCard < $dealerCard ) 
                            {
                                $gambleWin = -1 * $totalWin;
                                $totalWin = 0;
                                $gambleState = 'lose';
                            }
                            else
                            {
                                $gambleWin = $totalWin;
                                $totalWin = $totalWin;
                                $gambleState = 'draw';
                            }
                            if( $gambleWin != $totalWin ) 
                            {
                                $slotSettings->SetBalance($gambleWin);
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $gambleWin * -1);
                            }
                            $afterBalance = $slotSettings->GetBalance();
                            $userCards = [
                                rand(2, 14), 
                                rand(2, 14), 
                                rand(2, 14), 
                                rand(2, 14)
                            ];
                            $userCards[$gambleChoice] = $userCard;
                            for( $i = 0; $i < 4; $i++ ) 
                            {
                                $userCards[$i] = '"' . $gambleId[$userCards[$i]] . $gambleSuits[rand(0, 3)] . '"';
                            }
                            $userCardsStr = implode(',', $userCards);
                            $slotSettings->SetGameData('LadyJesterGTMTotalWin', $totalWin);
                            $jsSet = '{"dealerCard":"' . $dealerCard . '","playerCards":[' . $userCardsStr . '],"gambleState":"' . $gambleState . '","totalWin":' . $totalWin . ',"afterBalance":' . $afterBalance . ',"Balance":' . $Balance . '}';
                            $response = '{"responseEvent":"gambleResult","deb":' . $userCards[$gambleChoice] . ',"serverResponse":' . $jsSet . '}';
                        }
                        else if( $postData['slotEvent'] == 'gamble5GetDealerCard' ) 
                        {
                            $gambleCards = [
                                2, 
                                3, 
                                4, 
                                5, 
                                6, 
                                7, 
                                8, 
                                9, 
                                10, 
                                11, 
                                12, 
                                13, 
                                14
                            ];
                            $gambleId = [
                                '', 
                                '', 
                                '2', 
                                '3', 
                                '4', 
                                '5', 
                                '6', 
                                '7', 
                                '8', 
                                '9', 
                                '10', 
                                'J', 
                                'Q', 
                                'K', 
                                'A'
                            ];
                            $gambleSuits = [
                                'C', 
                                'S', 
                                'D', 
                                'H'
                            ];
                            $tmpDc = $gambleCards[rand(0, 12)];
                            $slotSettings->SetGameData('LadyJesterGTMDealerCard', $tmpDc);
                            $dealerCard = $gambleId[$tmpDc] . $gambleSuits[rand(0, 3)];
                            $jsSet = '{"dealerCard":"' . $dealerCard . '"}';
                            $response = '{"responseEvent":"gamble5DealerCard","serverResponse":' . $jsSet . '}';
                        }
                        else if( $postData['slotEvent'] == 'slotGamble' ) 
                        {
                            $Balance = $slotSettings->GetBalance();
                            $isGambleWin = rand(1, $slotSettings->GetGambleSettings());
                            $dealerCard = '';
                            $totalWin = $slotSettings->GetGameData('LadyJesterGTMTotalWin');
                            $gambleWin = 0;
                            $statBet = $totalWin;
                            if( $slotSettings->MaxWin < ($totalWin * $slotSettings->CurrentDenom) ) 
                            {
                                $isGambleWin = 0;
                            }
                            if( $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : '')) < ($totalWin * 2) ) 
                            {
                                $isGambleWin = 0;
                            }
                            if( $isGambleWin == 1 ) 
                            {
                                $gambleState = 'win';
                                $gambleWin = $totalWin;
                                $totalWin = $totalWin * 2;
                                if( $postData['gambleChoice'] == 'red' ) 
                                {
                                    $tmpCards = [
                                        'D', 
                                        'H'
                                    ];
                                    $dealerCard = $tmpCards[rand(0, 1)];
                                }
                                else
                                {
                                    $tmpCards = [
                                        'C', 
                                        'S'
                                    ];
                                    $dealerCard = $tmpCards[rand(0, 1)];
                                }
                            }
                            else
                            {
                                $gambleState = 'lose';
                                $gambleWin = -1 * $totalWin;
                                $totalWin = 0;
                                if( $postData['gambleChoice'] == 'red' ) 
                                {
                                    $tmpCards = [
                                        'C', 
                                        'S'
                                    ];
                                    $dealerCard = $tmpCards[rand(0, 1)];
                                }
                                else
                                {
                                    $tmpCards = [
                                        'D', 
                                        'H'
                                    ];
                                    $dealerCard = $tmpCards[rand(0, 1)];
                                }
                            }
                            $slotSettings->SetGameData('LadyJesterGTMTotalWin', $totalWin);
                            $slotSettings->SetBalance($gambleWin);
                            $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $gambleWin * -1);
                            $afterBalance = $slotSettings->GetBalance();
                            $jsSet = '{"dealerCard":"' . $dealerCard . '","gambleState":"' . $gambleState . '","totalWin":' . $totalWin . ',"afterBalance":' . $afterBalance . ',"Balance":' . $Balance . '}';
                            $response = '{"responseEvent":"gambleResult","serverResponse":' . $jsSet . '}';
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $totalWin);
                            $slotSettings->SaveLogReport($response, $statBet, 1, $gambleWin, $postData['slotEvent']);
                        }
                        else if( $postData['slotEvent'] == 'bet' || $postData['slotEvent'] == 'freespin' ) 
                        {
                            $linesId = [];
                            $linesId[0] = [];
                            $linesId[1] = [
                                1, 
                                1, 
                                1, 
                                1, 
                                1
                            ];
                            $linesId[2] = [
                                2, 
                                2, 
                                2, 
                                2, 
                                2
                            ];
                            $linesId[3] = [
                                3, 
                                3, 
                                3, 
                                3, 
                                3
                            ];
                            $linesId[4] = [
                                4, 
                                4, 
                                4, 
                                4, 
                                4
                            ];
                            $linesId[5] = [
                                1, 
                                2, 
                                3, 
                                2, 
                                1
                            ];
                            $linesId[6] = [
                                2, 
                                3, 
                                4, 
                                3, 
                                2
                            ];
                            $linesId[7] = [
                                3, 
                                2, 
                                1, 
                                2, 
                                3
                            ];
                            $linesId[8] = [
                                4, 
                                3, 
                                2, 
                                3, 
                                4
                            ];
                            $linesId[9] = [
                                1, 
                                1, 
                                1, 
                                1, 
                                2
                            ];
                            $linesId[10] = [
                                2, 
                                2, 
                                2, 
                                2, 
                                1
                            ];
                            $linesId[11] = [
                                3, 
                                3, 
                                3, 
                                3, 
                                4
                            ];
                            $linesId[12] = [
                                4, 
                                4, 
                                4, 
                                4, 
                                3
                            ];
                            $linesId[13] = [
                                1, 
                                2, 
                                2, 
                                2, 
                                2
                            ];
                            $linesId[14] = [
                                2, 
                                2, 
                                2, 
                                2, 
                                3
                            ];
                            $linesId[15] = [
                                3, 
                                3, 
                                3, 
                                3, 
                                2
                            ];
                            $linesId[16] = [
                                4, 
                                3, 
                                3, 
                                3, 
                                3
                            ];
                            $linesId[17] = [
                                2, 
                                1, 
                                1, 
                                1, 
                                1
                            ];
                            $linesId[18] = [
                                2, 
                                3, 
                                3, 
                                3, 
                                3
                            ];
                            $linesId[19] = [
                                3, 
                                2, 
                                2, 
                                2, 
                                2
                            ];
                            $linesId[20] = [
                                3, 
                                4, 
                                4, 
                                4, 
                                4
                            ];
                            $linesId[21] = [
                                1, 
                                1, 
                                1, 
                                2, 
                                3
                            ];
                            $linesId[22] = [
                                2, 
                                2, 
                                2, 
                                3, 
                                4
                            ];
                            $linesId[23] = [
                                3, 
                                3, 
                                3, 
                                2, 
                                1
                            ];
                            $linesId[24] = [
                                4, 
                                4, 
                                4, 
                                3, 
                                2
                            ];
                            $linesId[25] = [
                                1, 
                                2, 
                                3, 
                                3, 
                                3
                            ];
                            $linesId[26] = [
                                2, 
                                3, 
                                4, 
                                4, 
                                4
                            ];
                            $linesId[27] = [
                                3, 
                                2, 
                                1, 
                                1, 
                                1
                            ];
                            $linesId[28] = [
                                4, 
                                3, 
                                2, 
                                2, 
                                2
                            ];
                            $linesId[29] = [
                                1, 
                                1, 
                                2, 
                                1, 
                                1
                            ];
                            $linesId[30] = [
                                2, 
                                2, 
                                1, 
                                2, 
                                2
                            ];
                            $linesId[31] = [
                                3, 
                                3, 
                                4, 
                                3, 
                                3
                            ];
                            $linesId[32] = [
                                4, 
                                4, 
                                3, 
                                4, 
                                4
                            ];
                            $linesId[33] = [
                                1, 
                                2, 
                                2, 
                                2, 
                                1
                            ];
                            $linesId[34] = [
                                2, 
                                2, 
                                3, 
                                2, 
                                2
                            ];
                            $linesId[35] = [
                                3, 
                                3, 
                                4, 
                                3, 
                                3
                            ];
                            $linesId[36] = [
                                4, 
                                3, 
                                3, 
                                3, 
                                4
                            ];
                            $linesId[37] = [
                                2, 
                                1, 
                                1, 
                                1, 
                                2
                            ];
                            $linesId[38] = [
                                2, 
                                3, 
                                3, 
                                3, 
                                2
                            ];
                            $linesId[39] = [
                                3, 
                                2, 
                                2, 
                                2, 
                                3
                            ];
                            $linesId[40] = [
                                3, 
                                4, 
                                4, 
                                4, 
                                3
                            ];
                            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $postData['slotBet'] * $postData['slotLines'], $postData['slotLines']);
                            $winType = $winTypeTmp[0];
                            $spinWinLimit = $winTypeTmp[1];
                            if( $postData['slotEvent'] != 'freespin' ) 
                            {
                                if( !isset($postData['slotEvent']) ) 
                                {
                                    $postData['slotEvent'] = 'bet';
                                }
                                $bankSum = ($postData['slotBet'] * $postData['slotLines']) / 100 * $slotSettings->GetPercent();
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                                $jackState = $slotSettings->UpdateJackpots($postData['slotBet'] * $postData['slotLines'], false, false);
                                $slotSettings->SetBalance(-1 * ($postData['slotBet'] * $postData['slotLines']), $postData['slotEvent']);
                                $bonusMpl = 1;
                                $slotSettings->SetGameData('LadyJesterGTMBonusWin', 0);
                                $slotSettings->SetGameData('LadyJesterGTMFreeGames', 0);
                                $slotSettings->SetGameData('LadyJesterGTMCurrentFreeGame', 0);
                                $slotSettings->SetGameData('LadyJesterGTMTotalWin', 0);
                                $slotSettings->SetGameData('LadyJesterGTMFreeBalance', 0);
                                $slotSettings->SetGameData('LadyJesterGTMIsJack', 0);
                                for( $i = 0; $i < 5; $i++ ) 
                                {
                                    $slotSettings->SetGameData('LadyJesterGTMStacked.' . $i, -1);
                                }
                            }
                            else
                            {
                                $slotSettings->SetGameData('LadyJesterGTMCurrentFreeGame', $slotSettings->GetGameData('LadyJesterGTMCurrentFreeGame') + 1);
                                $bonusMpl = $slotSettings->slotFreeMpl;
                                if( $slotSettings->GetGameData('LadyJesterGTMStacked.0') > 0 && $slotSettings->GetGameData('LadyJesterGTMStacked.1') > 0 && $slotSettings->GetGameData('LadyJesterGTMStacked.2') > 0 && $slotSettings->GetGameData('LadyJesterGTMStacked.3') > 0 && $slotSettings->GetGameData('LadyJesterGTMStacked.4') > 0 ) 
                                {
                                    $jackState = $slotSettings->UpdateJackpots($postData['slotBet'] * $postData['slotLines'], true, true);
                                }
                                else
                                {
                                    $jackState = $slotSettings->UpdateJackpots($postData['slotBet'] * $postData['slotLines'], true, false);
                                }
                            }
                            $Balance = $slotSettings->GetBalance();
                            for( $i = 0; $i <= 2000; $i++ ) 
                            {
                                $isSlotJack = false;
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
                                    0
                                ];
                                $wild = ['P_1'];
                                $scatter = 'SCAT';
                                $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                if( isset($jackState) && $jackState['isJackPay'] ) 
                                {
                                    $rline = rand(1, $postData['slotLines'] - 1);
                                    $slotSettings->SetGameData('LadyJesterGTMIsJack', 1);
                                    $winType = 'win';
                                    $spinWinLimit = $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''));
                                    for( $jl = 1; $jl <= 5; $jl++ ) 
                                    {
                                        $jreel = $slotSettings->PutBonusToLine($jl, $linesId[$rline][$jl - 1], 'P_1');
                                        if( $reels['reel' . $jl][0] != 'P_1' && $reels['reel' . $jl][1] != 'P_1' && $reels['reel' . $jl][2] != 'P_1' ) 
                                        {
                                            $reels['reel' . $jl][rand(0, 2)] = 'P_1';
                                        }
                                    }
                                }
                                $reelsTmp = $reels;
                                for( $r = 1; $r <= 5; $r++ ) 
                                {
                                    for( $p = 0; $p <= 3; $p++ ) 
                                    {
                                        if( $slotSettings->GetGameData('LadyJesterGTMStacked.' . ($r - 1)) > 0 ) 
                                        {
                                            $reels['reel' . $r][$p] = 'P_1';
                                            if( $reelsTmp['reel' . $r][$p] == 'P_1' ) 
                                            {
                                                $reelsTmp['reel' . $r][$p] = 'P_2';
                                            }
                                        }
                                    }
                                }
                                $isSlotJack = false;
                                $jackSymCnt = 0;
                                for( $r = 1; $r <= 5; $r++ ) 
                                {
                                    for( $p = 0; $p <= 3; $p++ ) 
                                    {
                                        if( $reels['reel' . $r][$p] == 'P_1' ) 
                                        {
                                            $jackSymCnt++;
                                        }
                                    }
                                }
                                if( $jackSymCnt >= 15 ) 
                                {
                                    $isSlotJack = true;
                                }
                                $lWin = 0;
                                for( $k = 0; $k < $postData['slotLines']; $k++ ) 
                                {
                                    $tmpStringWin = '';
                                    for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                                    {
                                        $csym = $slotSettings->SymbolGame[$j];
                                        if( $csym == $scatter || !isset($slotSettings->Paytable[$csym]) || $csym == 'P_1_1' || $csym == 'P_1_2' || $csym == 'P_1_3' || $csym == 'P_1_4' ) 
                                        {
                                        }
                                        else
                                        {
                                            $s = [];
                                            $s[0] = $reels['reel1'][$linesId[$k + 1][0] - 1];
                                            $s[1] = $reels['reel2'][$linesId[$k + 1][1] - 1];
                                            $s[2] = $reels['reel3'][$linesId[$k + 1][2] - 1];
                                            $s[3] = $reels['reel4'][$linesId[$k + 1][3] - 1];
                                            $s[4] = $reels['reel5'][$linesId[$k + 1][4] - 1];
                                            if( $s[0] == $csym || in_array($s[0], $wild) ) 
                                            {
                                                $mpl = 1;
                                                $tmpWin = $slotSettings->Paytable[$csym][1] * $postData['slotBet'] * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"Count":1,"Line":' . $k . ',"Win":' . $cWins[$k] . ',"stepWin":' . ($cWins[$k] + $totalWin + $slotSettings->GetGameData('LadyJesterGTMBonusWin')) . ',"winReel1":[' . ($linesId[$k + 1][0] - 1) . ',"' . $s[0] . '"],"winReel2":["none","none"],"winReel3":["none","none"],"winReel4":["none","none"],"winReel5":["none","none"]}';
                                                }
                                            }
                                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) ) 
                                            {
                                                $mpl = 1;
                                                if( in_array($s[0], $wild) && in_array($s[1], $wild) ) 
                                                {
                                                    $mpl = 1;
                                                }
                                                else if( in_array($s[0], $wild) || in_array($s[1], $wild) ) 
                                                {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                    for( $wld = 0; $wld < 2; $wld++ ) 
                                                    {
                                                        if( in_array($s[$wld], $wild) ) 
                                                        {
                                                            $s[$wld] = 'P_1';
                                                        }
                                                    }
                                                }
                                                $tmpWin = $slotSettings->Paytable[$csym][2] * $postData['slotBet'] * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"Count":2,"Line":' . $k . ',"Win":' . $cWins[$k] . ',"stepWin":' . ($cWins[$k] + $totalWin + $slotSettings->GetGameData('LadyJesterGTMBonusWin')) . ',"winReel1":[' . ($linesId[$k + 1][0] - 1) . ',"' . $s[0] . '"],"winReel2":[' . ($linesId[$k + 1][1] - 1) . ',"' . $s[1] . '"],"winReel3":["none","none"],"winReel4":["none","none"],"winReel5":["none","none"]}';
                                                }
                                            }
                                            $s[0] = $reels['reel1'][$linesId[$k + 1][0] - 1];
                                            $s[1] = $reels['reel2'][$linesId[$k + 1][1] - 1];
                                            $s[2] = $reels['reel3'][$linesId[$k + 1][2] - 1];
                                            $s[3] = $reels['reel4'][$linesId[$k + 1][3] - 1];
                                            $s[4] = $reels['reel5'][$linesId[$k + 1][4] - 1];
                                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) ) 
                                            {
                                                $mpl = 1;
                                                if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) ) 
                                                {
                                                    $mpl = 1;
                                                }
                                                else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) ) 
                                                {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                    for( $wld = 0; $wld < 3; $wld++ ) 
                                                    {
                                                        if( in_array($s[$wld], $wild) ) 
                                                        {
                                                            $s[$wld] = 'P_1';
                                                        }
                                                    }
                                                }
                                                $tmpWin = $slotSettings->Paytable[$csym][3] * $postData['slotBet'] * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"Count":3,"Line":' . $k . ',"Win":' . $cWins[$k] . ',"stepWin":' . ($cWins[$k] + $totalWin + $slotSettings->GetGameData('LadyJesterGTMBonusWin')) . ',"winReel1":[' . ($linesId[$k + 1][0] - 1) . ',"' . $s[0] . '"],"winReel2":[' . ($linesId[$k + 1][1] - 1) . ',"' . $s[1] . '"],"winReel3":[' . ($linesId[$k + 1][2] - 1) . ',"' . $s[2] . '"],"winReel4":["none","none"],"winReel5":["none","none"]}';
                                                }
                                            }
                                            $s[0] = $reels['reel1'][$linesId[$k + 1][0] - 1];
                                            $s[1] = $reels['reel2'][$linesId[$k + 1][1] - 1];
                                            $s[2] = $reels['reel3'][$linesId[$k + 1][2] - 1];
                                            $s[3] = $reels['reel4'][$linesId[$k + 1][3] - 1];
                                            $s[4] = $reels['reel5'][$linesId[$k + 1][4] - 1];
                                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) ) 
                                            {
                                                $mpl = 1;
                                                if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) ) 
                                                {
                                                    $mpl = 1;
                                                }
                                                else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) ) 
                                                {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                    for( $wld = 0; $wld < 4; $wld++ ) 
                                                    {
                                                        if( in_array($s[$wld], $wild) ) 
                                                        {
                                                            $s[$wld] = 'P_1';
                                                        }
                                                    }
                                                }
                                                $tmpWin = $slotSettings->Paytable[$csym][4] * $postData['slotBet'] * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"Count":4,"Line":' . $k . ',"Win":' . $cWins[$k] . ',"stepWin":' . ($cWins[$k] + $totalWin + $slotSettings->GetGameData('LadyJesterGTMBonusWin')) . ',"winReel1":[' . ($linesId[$k + 1][0] - 1) . ',"' . $s[0] . '"],"winReel2":[' . ($linesId[$k + 1][1] - 1) . ',"' . $s[1] . '"],"winReel3":[' . ($linesId[$k + 1][2] - 1) . ',"' . $s[2] . '"],"winReel4":[' . ($linesId[$k + 1][3] - 1) . ',"' . $s[3] . '"],"winReel5":["none","none"]}';
                                                }
                                            }
                                            $s[0] = $reels['reel1'][$linesId[$k + 1][0] - 1];
                                            $s[1] = $reels['reel2'][$linesId[$k + 1][1] - 1];
                                            $s[2] = $reels['reel3'][$linesId[$k + 1][2] - 1];
                                            $s[3] = $reels['reel4'][$linesId[$k + 1][3] - 1];
                                            $s[4] = $reels['reel5'][$linesId[$k + 1][4] - 1];
                                            if( ($s[0] == $csym || in_array($s[0], $wild)) && ($s[1] == $csym || in_array($s[1], $wild)) && ($s[2] == $csym || in_array($s[2], $wild)) && ($s[3] == $csym || in_array($s[3], $wild)) && ($s[4] == $csym || in_array($s[4], $wild)) ) 
                                            {
                                                $mpl = 1;
                                                if( in_array($s[0], $wild) && in_array($s[1], $wild) && in_array($s[2], $wild) && in_array($s[3], $wild) && in_array($s[4], $wild) ) 
                                                {
                                                    $mpl = 1;
                                                }
                                                else if( in_array($s[0], $wild) || in_array($s[1], $wild) || in_array($s[2], $wild) || in_array($s[3], $wild) || in_array($s[4], $wild) ) 
                                                {
                                                    $mpl = $slotSettings->slotWildMpl;
                                                    for( $wld = 0; $wld < 5; $wld++ ) 
                                                    {
                                                        if( in_array($s[$wld], $wild) ) 
                                                        {
                                                            $s[$wld] = 'P_1';
                                                        }
                                                    }
                                                }
                                                $tmpWin = $slotSettings->Paytable[$csym][5] * $postData['slotBet'] * $mpl * $bonusMpl;
                                                if( $cWins[$k] < $tmpWin ) 
                                                {
                                                    $cWins[$k] = $tmpWin;
                                                    $tmpStringWin = '{"Count":5,"Line":' . $k . ',"Win":' . $cWins[$k] . ',"stepWin":' . ($cWins[$k] + $totalWin + $slotSettings->GetGameData('LadyJesterGTMBonusWin')) . ',"winReel1":[' . ($linesId[$k + 1][0] - 1) . ',"' . $s[0] . '"],"winReel2":[' . ($linesId[$k + 1][1] - 1) . ',"' . $s[1] . '"],"winReel3":[' . ($linesId[$k + 1][2] - 1) . ',"' . $s[2] . '"],"winReel4":[' . ($linesId[$k + 1][3] - 1) . ',"' . $s[3] . '"],"winReel5":[' . ($linesId[$k + 1][4] - 1) . ',"' . $s[4] . '"]}';
                                                }
                                                if( $isSlotJack ) 
                                                {
                                                    $tmpStringWin = '';
                                                }
                                            }
                                        }
                                    }
                                    if( $cWins[$k] > 0 && $tmpStringWin != '' && !$isSlotJack ) 
                                    {
                                        $lWin = $cWins[$k];
                                        array_push($lineWins, $tmpStringWin);
                                        $totalWin += $cWins[$k];
                                    }
                                }
                                $reels = $reelsTmp;
                                $scattersWin = 0;
                                $scattersStr = '{';
                                $scattersCount = 0;
                                $wildCnt = 0;
                                for( $r = 1; $r <= 5; $r++ ) 
                                {
                                    for( $p = 0; $p <= 3; $p++ ) 
                                    {
                                        if( $reels['reel' . $r][$p] == $scatter ) 
                                        {
                                            $scattersCount++;
                                            $scattersStr .= ('"winReel' . $r . '":[' . $p . ',"' . $scatter . '"],');
                                        }
                                        if( $reelsTmp['reel' . $r][$p] == 'P_1' ) 
                                        {
                                            $wildCnt++;
                                        }
                                    }
                                }
                                $nextWin = 0;
                                if( $wildCnt > 0 && $postData['slotEvent'] == 'freespin' ) 
                                {
                                    $nextWin = $slotSettings->CalculateSpin($postData['slotBet'], $postData['slotLines'], $linesId, $reelsTmp);
                                }
                                if( isset($jackState) && $jackState['isJackPay'] ) 
                                {
                                    $nextWin = 0;
                                }
                                $scattersWin = $slotSettings->Paytable[$scatter][$scattersCount] * $postData['slotBet'] * $postData['slotLines'];
                                if( $scattersCount >= 3 && $slotSettings->slotBonus ) 
                                {
                                    $scattersStr .= '"scattersType":"bonus",';
                                }
                                else if( $scattersWin > 0 ) 
                                {
                                    $scattersStr .= '"scattersType":"win",';
                                }
                                else
                                {
                                    $scattersStr .= '"scattersType":"none",';
                                }
                                $scattersStr .= ('"scattersWin":' . $scattersWin . '}');
                                $totalWin += $scattersWin;
                                if( $slotSettings->GetGameData('LadyJesterGTMStacked.0') > 0 && $slotSettings->GetGameData('LadyJesterGTMStacked.1') > 0 && $slotSettings->GetGameData('LadyJesterGTMStacked.2') > 0 && $slotSettings->GetGameData('LadyJesterGTMStacked.3') > 0 && $slotSettings->GetGameData('LadyJesterGTMStacked.4') > 0 ) 
                                {
                                    $totalWin = 0;
                                    $lineWins = [];
                                    $isSlotJack = true;
                                    $winType = 'none';
                                }
                                if( $i > 700 ) 
                                {
                                    $winType = 'none';
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
                                    if( $slotSettings->increaseRTP && $winType == 'win' && $totalWin < ($minWin * $postData['slotBet'] * $postData['slotLines']) ) 
                                    {
                                    }
                                    else
                                    {
                                        if( $i > 1500 ) 
                                        {
                                            $response = '{"responseEvent":"error","responseType":"' . $postData['slotEvent'] . '","serverResponse":"Bad Reel Strip"}';
                                            exit( $response );
                                        }
                                        if( $isSlotJack ) 
                                        {
                                            break;
                                        }
                                        if( $scattersCount >= 3 && $winType != 'bonus' ) 
                                        {
                                        }
                                        else
                                        {
                                            if( $totalWin + $nextWin <= $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : '')) && $postData['slotEvent'] == 'freespin' ) 
                                            {
                                                break;
                                            }
                                            if( $totalWin + $nextWin <= $spinWinLimit && $winType == 'bonus' ) 
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
                                            else if( $totalWin > 0 && $totalWin + $nextWin <= $spinWinLimit && $winType == 'win' ) 
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
                                }
                            }
                            if( $totalWin > 0 ) 
                            {
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                            }
                            if( $postData['slotEvent'] == 'freespin' && $slotSettings->GetGameData('LadyJesterGTMFreeGames') <= $slotSettings->GetGameData('LadyJesterGTMCurrentFreeGame') && $winType != 'bonus' && $slotSettings->GetGameData('LadyJesterGTMTotalWin') + $totalWin > 0 ) 
                            {
                                $slotSettings->SetBalance($slotSettings->GetGameData('LadyJesterGTMTotalWin') + $totalWin);
                            }
                            else if( $postData['slotEvent'] != 'freespin' && $winType != 'bonus' && $totalWin > 0 ) 
                            {
                                $slotSettings->SetBalance($totalWin);
                            }
                            $reportWin = $totalWin;
                            if( $postData['slotEvent'] == 'freespin' ) 
                            {
                                $slotSettings->SetGameData('LadyJesterGTMBonusWin', $slotSettings->GetGameData('LadyJesterGTMBonusWin') + $totalWin);
                                $slotSettings->SetGameData('LadyJesterGTMTotalWin', $slotSettings->GetGameData('LadyJesterGTMTotalWin') + $totalWin);
                                $totalWin = $slotSettings->GetGameData('LadyJesterGTMBonusWin');
                                $Balance = $slotSettings->GetGameData('LadyJesterGTMFreeBalance');
                                for( $r = 1; $r <= 5; $r++ ) 
                                {
                                    for( $p = 0; $p <= 3; $p++ ) 
                                    {
                                        if( $reels['reel' . $r][$p] == 'P_1' ) 
                                        {
                                            $slotSettings->SetGameData('LadyJesterGTMStacked.' . ($r - 1), 0);
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $slotSettings->SetGameData('LadyJesterGTMTotalWin', $totalWin);
                            }
                            if( $scattersCount >= 3 ) 
                            {
                                if( $slotSettings->GetGameData('LadyJesterGTMFreeGames') > 0 ) 
                                {
                                    $slotSettings->SetGameData('LadyJesterGTMFreeBalance', $Balance);
                                    $slotSettings->SetGameData('LadyJesterGTMBonusWin', $totalWin);
                                    $slotSettings->SetGameData('LadyJesterGTMFreeGames', $slotSettings->GetGameData('LadyJesterGTMFreeGames') + $slotSettings->slotFreeCount);
                                }
                                else
                                {
                                    $slotSettings->SetGameData('LadyJesterGTMFreeBalance', $Balance);
                                    $slotSettings->SetGameData('LadyJesterGTMBonusWin', $totalWin);
                                    $slotSettings->SetGameData('LadyJesterGTMFreeGames', $slotSettings->slotFreeCount);
                                }
                            }
                            $LadyJesterGTMStacked = [];
                            for( $i = 0; $i < 5; $i++ ) 
                            {
                                if( $slotSettings->HasGameData('LadyJesterGTMStacked.' . $i) ) 
                                {
                                    $LadyJesterGTMStacked[] = $slotSettings->GetGameData('LadyJesterGTMStacked.' . $i);
                                }
                            }
                            $jsSpin = '' . json_encode($reels) . '';
                            $jsJack = '' . json_encode($slotSettings->Jackpots) . '';
                            $winString = implode(',', $lineWins);
                            $stackedWilds = '"stackedWilds":[' . implode(',', $LadyJesterGTMStacked) . ']';
                            $response = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{' . $stackedWilds . ',"slotJackpot":' . $slotSettings->slotJackpot[0] . ',"slotLines":' . $postData['slotLines'] . ',"slotBet":' . $postData['slotBet'] . ',"totalFreeGames":' . $slotSettings->GetGameData('LadyJesterGTMFreeGames') . ',"currentFreeGames":' . $slotSettings->GetGameData('LadyJesterGTMCurrentFreeGame') . ',"Balance":' . $Balance . ',"afterBalance":' . $slotSettings->GetBalance() . ',"totalWin":' . $totalWin . ',"winLines":[' . $winString . '],"bonusInfo":' . $scattersStr . ',"Jackpots":' . $jsJack . ',"reelsSymbols":' . $jsSpin . '}}';
                            $slotSettings->SaveLogReport($response, $postData['slotBet'], $postData['slotLines'], $reportWin, $postData['slotEvent']);
                            if( isset($slotSettings->Jackpots['jackPay']) ) 
                            {
                                $slotSettings->SetBalance($slotSettings->slotJackpot[$jackState['isJackId']]);
                                $slotSettings->SaveLogReport($response, $postData['slotBet'], $postData['slotLines'], $slotSettings->Jackpots['jackPay'], 'JPG');
                            }
                            if( $postData['slotEvent'] == 'freespin' ) 
                            {
                                for( $r = 1; $r <= 5; $r++ ) 
                                {
                                    if( $slotSettings->GetGameData('LadyJesterGTMStacked.' . ($r - 1)) > 0 ) 
                                    {
                                        $slotSettings->SetGameData('LadyJesterGTMStacked.' . ($r - 1), -1);
                                    }
                                    if( $slotSettings->GetGameData('LadyJesterGTMStacked.' . ($r - 1)) == 0 ) 
                                    {
                                        $slotSettings->SetGameData('LadyJesterGTMStacked.' . ($r - 1), 1);
                                    }
                                }
                            }
                        }
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
