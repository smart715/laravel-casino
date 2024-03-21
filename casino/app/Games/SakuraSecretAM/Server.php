<?php 
namespace VanguardLTE\Games\SakuraSecretAM
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
                    $floatBet = 100;
                    $response = '';
                    $lines = 1;
                    $linesFixed = 1;
                    $symCount = '9';
                    $symCountAll = '9';
                    $balanceInCents = sprintf('%01.2f', $slotSettings->GetBalance()) * $floatBet;
                    $fixedLinesFormated0 = dechex($lines + 1);
                    if( strlen($fixedLinesFormated0) <= 1 ) 
                    {
                        $fixedLinesFormated0 = '0' . $fixedLinesFormated0;
                    }
                    $fixedLinesFormated = dechex($lines);
                    if( strlen($fixedLinesFormated) <= 1 ) 
                    {
                        $fixedLinesFormated = '0' . $fixedLinesFormated;
                    }
                    $fixedLinesFormatedStr = '';
                    for( $i = 1; $i <= $lines; $i++ ) 
                    {
                        $fixedLinesFormatedStr .= '10';
                    }
                    $gameData = [];
                    $tmpPar = explode(',', $postData['gameData']);
                    $gameData['slotEvent'] = $tmpPar[0];
                    if( $gameData['slotEvent'] == 'A/u251' || $gameData['slotEvent'] == 'A/u256' ) 
                    {
                        if( $gameData['slotEvent'] == 'A/u256' && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') && $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') > 0 ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $gameData['slotEvent'] . '","serverResponse":"invalid bonus state"}';
                            exit( $response );
                        }
                        if( $slotSettings->GetBalance() < $slotSettings->Bet[$tmpPar[2]] && $gameData['slotEvent'] == 'A/u251' ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $gameData['slotEvent'] . '","serverResponse":"invalid balance"}';
                            exit( $response );
                        }
                        if( !isset($slotSettings->Bet[$tmpPar[2]]) || $tmpPar[1] <= 0 ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $gameData['slotEvent'] . '","serverResponse":"invalid bet/lines"}';
                            exit( $response );
                        }
                    }
                    if( $gameData['slotEvent'] == 'A/u257' && $slotSettings->GetGameData($slotSettings->slotId . 'DoubleWin') <= 0 ) 
                    {
                        $response = '{"responseEvent":"error","responseType":"' . $gameData['slotEvent'] . '","serverResponse":"invalid gamble state"}';
                        exit( $response );
                    }
                    if( $gameData['slotEvent'] == 'A/u256' ) 
                    {
                        $postData['spinType'] = 'free';
                        $gameData['slotEvent'] = 'A/u251';
                    }
                    else
                    {
                        $postData['spinType'] = 'regular';
                    }
                    switch( $gameData['slotEvent'] ) 
                    {
                        case 'A/u350':
                            $winall = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                            if( !is_numeric($winall) ) 
                            {
                                $winall = 0;
                            }
                            $balance = $slotSettings->GetBalance() - $winall;
                            $response = 'UPDATE#' . (sprintf('%01.2f', $balance) * $floatBet);
                            break;
                        case 'A/u25':
                            $slotSettings->SetGameData($slotSettings->slotId . 'Cards', [
                                '00', 
                                '00', 
                                '00', 
                                '00', 
                                '00', 
                                '00', 
                                '00', 
                                '00'
                            ]);
                            $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                            $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', 0);
                            $betsArr = $slotSettings->Bet;
                            $betString = '';
                            $minBets = '';
                            $maxBets = '';
                            for( $b = 0; $b < count($betsArr); $b++ ) 
                            {
                                $betsArr[$b] = (double)$betsArr[$b] * $floatBet;
                                $betString .= (dechex(strlen(dechex($betsArr[$b]))) . dechex($betsArr[$b]));
                            }
                            $minBets .= (strlen(dechex($betsArr[0])) . dechex($betsArr[0]));
                            $maxBets .= (strlen(dechex($betsArr[count($betsArr) - 1] * 1)) . dechex($betsArr[count($betsArr) - 1] * 1));
                            $betsLength = count($betsArr);
                            $betsLength = dechex($betsLength);
                            if( strlen($betsLength) <= 1 ) 
                            {
                                $betsLength = '0' . $betsLength;
                            }
                            $balanceFormated = $slotSettings->HexFormat(round($slotSettings->GetBalance() * $floatBet));
                            $slotState = '4';
                            $lastEvent = $slotSettings->GetHistory();
                            if( $lastEvent != 'NULL' ) 
                            {
                                $reels = $lastEvent->serverResponse->reelsSymbols;
                                $reelSate = $slotSettings->HexFormat($reels->rp[0]) . $slotSettings->HexFormat($reels->rp[1]) . $slotSettings->HexFormat($reels->rp[2]) . $slotSettings->HexFormat($reels->rp[3]) . $slotSettings->HexFormat($reels->rp[4]);
                                $curBet = dechex($lastEvent->serverResponse->slotBet);
                                if( strlen($curBet) <= 1 ) 
                                {
                                    $curBet = '0' . $curBet;
                                }
                                $curLines = dechex($lastEvent->serverResponse->slotLines);
                                if( strlen($curLines) <= 1 ) 
                                {
                                    $curLines = '0' . $curLines;
                                }
                                $slotSettings->SetGameData('SakuraSecretAMLines', $curLines);
                                $freeMpl = '11';
                                $slotSettings->SetGameData('SakuraSecretAMBonusWin', $lastEvent->serverResponse->bonusWin);
                                $slotSettings->SetGameData('SakuraSecretAMFreeGames', $lastEvent->serverResponse->totalFreeGames);
                                $slotSettings->SetGameData('SakuraSecretAMCurrentFreeGame', $lastEvent->serverResponse->currentFreeGames);
                                $slotSettings->SetGameData('SakuraSecretAMTotalWin', 0);
                                $slotSettings->SetGameData('SakuraSecretAMFreeBalance', 0);
                                $slotSettings->SetGameData('SakuraSecretAMFreeStartWin', 0);
                                $tFree = dechex($slotSettings->GetGameData('SakuraSecretAMFreeGames'));
                                $cFree = dechex($slotSettings->GetGameData('SakuraSecretAMFreeGames') - $slotSettings->GetGameData('SakuraSecretAMCurrentFreeGame'));
                                $freeInfo = strlen($tFree) . $tFree . strlen($cFree) . $cFree;
                                $stateWin = $slotSettings->HexFormat($slotSettings->GetGameData('SakuraSecretAMBonusWin') * $floatBet);
                                if( $slotSettings->GetGameData('SakuraSecretAMCurrentFreeGame') < $slotSettings->GetGameData('SakuraSecretAMFreeGames') && $slotSettings->GetGameData('SakuraSecretAMFreeGames') > 0 ) 
                                {
                                    $slotState = '6';
                                    if( $slotSettings->GetGameData('' . $slotSettings->slotId . 'CurrentFreeGame') == 0 ) 
                                    {
                                        $slotState = '5';
                                    }
                                }
                            }
                            else
                            {
                                $slotSettings->SetGameData('SakuraSecretAMLines', $fixedLinesFormated);
                                $slotState = '4';
                                $reelSate = $slotSettings->GetRandomReels();
                                $curBet = '00';
                                $freeMpl = '11';
                                $freeInfo = '1010';
                                $stateWin = '10';
                            }
                            $response = '05' . $slotSettings->FormatReelStrips('') . '5' . $slotSettings->FormatReelStrips('Bonus') . '040' . $reelSate . '10' . $balanceFormated . '10' . $curBet . $minBets . $maxBets . '09101010101010100909091100' . $reelSate . '0000000000000000' . $betsLength . $betString . '0910101010101010101013fff14ffff15fffff14ffff13fff13111141111151111114111113111#00101010|0';
                            break;
                        case 'A/u250':
                            $fixedLinesFormated = $slotSettings->GetGameData('SakuraSecretAMLines');
                            $balanceFormated = $slotSettings->HexFormat(round($slotSettings->GetBalance() * $floatBet));
                            $lastEvent = $slotSettings->GetHistory();
                            if( $lastEvent != 'NULL' ) 
                            {
                                $reels = $lastEvent->serverResponse->reelsSymbols;
                                $reelSate = $slotSettings->HexFormat($reels->rp[0]) . $slotSettings->HexFormat($reels->rp[1]) . $slotSettings->HexFormat($reels->rp[2]) . $slotSettings->HexFormat($reels->rp[3]) . $slotSettings->HexFormat($reels->rp[4]);
                            }
                            else
                            {
                                $reelSate = $slotSettings->GetRandomReels();
                            }
                            $response = '100010' . $balanceFormated . '10' . $reelSate . '00' . '09' . '10101010101010101010100b101010101010101010101014311d0c18190208#101010';
                            $response = '1000850c6a27e427101022024121723f170109101010101010101010101009101010101010101010000000000000000013fff14ffff15fffff14ffff13fff13111141111151111114111113111#101010';
                            break;
                        case 'A/u251':
                            if( $postData['spinType'] == 'regular' && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') ) 
                            {
                                $umid = '0';
                                $postData['slotEvent'] = 'bet';
                                $bonusMpl = 1;
                                $slotSettings->SetGameData('SakuraSecretAMBonusWin', 0);
                                $slotSettings->SetGameData('SakuraSecretAMFreeGames', 0);
                                $slotSettings->SetGameData('SakuraSecretAMCurrentFreeGame', 0);
                                $slotSettings->SetGameData('SakuraSecretAMTotalWin', 0);
                                $slotSettings->SetGameData('SakuraSecretAMFreeBalance', 0);
                                $slotSettings->SetGameData('SakuraSecretAMFreeStartWin', 0);
                            }
                            if( $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') < $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') ) 
                            {
                                $umid = '0';
                                $postData['slotEvent'] = 'freespin';
                                $slotSettings->SetGameData('SakuraSecretAMCurrentFreeGame', $slotSettings->GetGameData('SakuraSecretAMCurrentFreeGame') + 1);
                                $bonusMpl = $slotSettings->slotFreeMpl;
                            }
                            $lines = 1;
                            $betLine = $slotSettings->Bet[$tmpPar[2]];
                            $betCnt = $tmpPar[2];
                            $postData['bet'] = $betLine * $lines;
                            if( !isset($postData['slotEvent']) ) 
                            {
                                $response = '{"responseEvent":"error","responseType":"slotEvent","serverResponse":"invalid params "}';
                                exit( $response );
                            }
                            if( $postData['slotEvent'] != 'freespin' ) 
                            {
                                if( !isset($postData['slotEvent']) ) 
                                {
                                    $postData['slotEvent'] = 'bet';
                                }
                                $bankSum = $postData['bet'] / 100 * $slotSettings->GetPercent();
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                                $slotSettings->UpdateJackpots($postData['bet']);
                                $slotSettings->SetBalance(-1 * $postData['bet'], $postData['slotEvent']);
                                $balanceFormated = $slotSettings->HexFormat(round($slotSettings->GetBalance() * $floatBet));
                            }
                            $winTypeTmp = $slotSettings->GetSpinSettings($postData['slotEvent'], $postData['bet'], 10);
                            $winType = $winTypeTmp[0];
                            $spinWinLimit = $winTypeTmp[1];
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
                                    0
                                ];
                                $wild = '14';
                                $scatter = '';
                                $reels = $slotSettings->GetReelStrips($winType, $postData['slotEvent']);
                                $wildsArr = [];
                                $wildsArr[0] = [
                                    1, 
                                    1, 
                                    1
                                ];
                                $wildsArr[1] = [
                                    1, 
                                    1, 
                                    1, 
                                    1
                                ];
                                $wildsArr[2] = [
                                    1, 
                                    1, 
                                    1, 
                                    1, 
                                    1
                                ];
                                $wildsArr[3] = [
                                    1, 
                                    1, 
                                    1, 
                                    1
                                ];
                                $wildsArr[4] = [
                                    1, 
                                    1, 
                                    1
                                ];
                                $wildsArrF = [];
                                $wildsArrF[0] = [
                                    'f', 
                                    'f', 
                                    'f'
                                ];
                                $wildsArrF[1] = [
                                    'f', 
                                    'f', 
                                    'f', 
                                    'f'
                                ];
                                $wildsArrF[2] = [
                                    'f', 
                                    'f', 
                                    'f', 
                                    'f', 
                                    'f'
                                ];
                                $wildsArrF[3] = [
                                    'f', 
                                    'f', 
                                    'f', 
                                    'f'
                                ];
                                $wildsArrF[4] = [
                                    'f', 
                                    'f', 
                                    'f'
                                ];
                                $wildsArrP = [];
                                $wildsArrP[0] = [
                                    0, 
                                    1, 
                                    2
                                ];
                                $wildsArrP[1] = [
                                    0, 
                                    1, 
                                    2, 
                                    3
                                ];
                                $wildsArrP[2] = [
                                    0, 
                                    1, 
                                    2, 
                                    3, 
                                    4
                                ];
                                $wildsArrP[3] = [
                                    0, 
                                    1, 
                                    2, 
                                    3
                                ];
                                $wildsArrP[4] = [
                                    0, 
                                    1, 
                                    2
                                ];
                                $addWild = rand(1, 5);
                                $addWild = 1;
                                if( $addWild == 1 ) 
                                {
                                    for( $aw = 1; $aw <= 4; $aw++ ) 
                                    {
                                        $awLimit = rand(0, count($wildsArr[$aw]));
                                        for( $aw2 = 0; $aw2 < ($awLimit - 1); $aw2++ ) 
                                        {
                                            shuffle($wildsArrP[$aw]);
                                            $awPos = $wildsArrP[$aw][$aw2];
                                            $wildsArr[$aw][$awPos] = 0;
                                            if( $reels['reel' . ($aw + 1)][$awPos] == 11 && $reels['reel' . ($aw + 1)][$awPos + 1] == 12 && $reels['reel' . ($aw + 1)][$awPos + 2] == 13 ) 
                                            {
                                                $reels['reel' . ($aw + 1)][$awPos] = '14';
                                                $reels['reel' . ($aw + 1)][$awPos + 1] = '14';
                                                $reels['reel' . ($aw + 1)][$awPos + 2] = '14';
                                                $wildsArrF[$aw][$awPos] = 'b';
                                                $wildsArrF[$aw][$awPos + 1] = 'c';
                                                $wildsArrF[$aw][$awPos + 2] = 'd';
                                                $awPos = $awPos + 2;
                                                break;
                                            }
                                            if( $reels['reel' . ($aw + 1)][$awPos] == 12 && $reels['reel' . ($aw + 1)][$awPos - 1] == 11 && $reels['reel' . ($aw + 1)][$awPos + 1] == 13 ) 
                                            {
                                                $reels['reel' . ($aw + 1)][$awPos - 1] = '14';
                                                $reels['reel' . ($aw + 1)][$awPos] = '14';
                                                $reels['reel' . ($aw + 1)][$awPos + 1] = '14';
                                                $wildsArrF[$aw][$awPos - 1] = 'b';
                                                $wildsArrF[$aw][$awPos] = 'c';
                                                $wildsArrF[$aw][$awPos + 1] = 'd';
                                                $awPos = $awPos + 1;
                                                break;
                                            }
                                            if( $reels['reel' . ($aw + 1)][$awPos] == 13 && $reels['reel' . ($aw + 1)][$awPos - 1] == 12 && $reels['reel' . ($aw + 1)][$awPos - 2] == 11 ) 
                                            {
                                                $reels['reel' . ($aw + 1)][$awPos - 2] = '14';
                                                $reels['reel' . ($aw + 1)][$awPos - 1] = '14';
                                                $reels['reel' . ($aw + 1)][$awPos] = '14';
                                                $wildsArrF[$aw][$awPos - 2] = 'b';
                                                $wildsArrF[$aw][$awPos - 1] = 'c';
                                                $wildsArrF[$aw][$awPos] = 'd';
                                                break;
                                            }
                                            if( $reels['reel' . ($aw + 1)][$awPos] == 9 && $reels['reel' . ($aw + 1)][$awPos + 1] == 10 ) 
                                            {
                                                $reels['reel' . ($aw + 1)][$awPos] = '14';
                                                $reels['reel' . ($aw + 1)][$awPos + 1] = '14';
                                                $wildsArrF[$aw][$awPos] = 'e';
                                                $wildsArrF[$aw][$awPos + 1] = 'e';
                                                break;
                                            }
                                            if( $reels['reel' . ($aw + 1)][$awPos] == 10 && $reels['reel' . ($aw + 1)][$awPos - 1] == 9 ) 
                                            {
                                                $reels['reel' . ($aw + 1)][$awPos] = '14';
                                                $reels['reel' . ($aw + 1)][$awPos - 1] = '14';
                                                $wildsArrF[$aw][$awPos] = 'e';
                                                $wildsArrF[$aw][$awPos - 1] = 'e';
                                                break;
                                            }
                                            $wildsArrF[$aw][$awPos] = 'e';
                                            $reels['reel' . ($aw + 1)][$awPos] = '14';
                                        }
                                    }
                                }
                                for( $r = 1; $r <= 5; $r++ ) 
                                {
                                    for( $p = 0; $p <= 5; $p++ ) 
                                    {
                                        if( $reels['reel' . $r][$p] == 11 || $reels['reel' . $r][$p] == 12 || $reels['reel' . $r][$p] == 13 || $reels['reel' . $r][$p] == 10 || $reels['reel' . $r][$p] == 9 ) 
                                        {
                                            $reels['reel' . $r][$p] = '0';
                                        }
                                    }
                                }
                                $tmpStringWin = '';
                                for( $j = 0; $j < count($slotSettings->SymbolGame); $j++ ) 
                                {
                                    $csym = $slotSettings->SymbolGame[$j];
                                    $wsym = [
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0
                                    ];
                                    $wildsym = [
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0
                                    ];
                                    $cntsym = [
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0, 
                                        0
                                    ];
                                    $cWin = 0;
                                    $sMpl = 1;
                                    $offsetMpl = 1;
                                    $offsetMpl0 = 1;
                                    if( $csym == $scatter || !isset($slotSettings->Paytable['SYM_' . $csym]) ) 
                                    {
                                    }
                                    else
                                    {
                                        $rLength = [
                                            0, 
                                            2, 
                                            3, 
                                            4, 
                                            3, 
                                            2
                                        ];
                                        for( $r = 1; $r <= 5; $r++ ) 
                                        {
                                            for( $s = 0; $s <= $rLength[$r]; $s++ ) 
                                            {
                                                if( $reels['reel' . $r][$s] == $csym || $reels['reel' . $r][$s] == $wild ) 
                                                {
                                                    $wsym[$r - 1] = 1;
                                                    $cntsym[$r - 1]++;
                                                }
                                                if( $reels['reel' . $r][$s] == $wild ) 
                                                {
                                                    $wildsym[$r - 1] = 1;
                                                }
                                            }
                                        }
                                        if( $wsym[0] > 0 && $wsym[1] > 0 ) 
                                        {
                                            $sMpl = 1;
                                            $offsetMpl = 1;
                                            $offsetMpl0 = 1;
                                            for( $r = 1; $r <= 2; $r++ ) 
                                            {
                                                if( $cntsym[$r - 1] > 0 ) 
                                                {
                                                    $sMpl = $sMpl * $cntsym[$r - 1];
                                                }
                                            }
                                            $cWin = $slotSettings->Paytable['SYM_' . $csym][2] * $betLine * $sMpl;
                                        }
                                        if( $wsym[0] > 0 && $wsym[1] > 0 && $wsym[2] > 0 ) 
                                        {
                                            $sMpl = 1;
                                            $offsetMpl = 1;
                                            $offsetMpl0 = 1;
                                            for( $r = 1; $r <= 3; $r++ ) 
                                            {
                                                if( $cntsym[$r - 1] > 0 ) 
                                                {
                                                    $sMpl = $sMpl * $cntsym[$r - 1];
                                                }
                                            }
                                            $tWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betLine * $sMpl;
                                            if( $cWin < $tWin ) 
                                            {
                                                $cWin = $tWin;
                                            }
                                        }
                                        if( $wsym[0] > 0 && $wsym[1] > 0 && $wsym[2] > 0 && $wsym[3] > 0 ) 
                                        {
                                            $sMpl = 1;
                                            $offsetMpl = 1;
                                            $offsetMpl0 = 1;
                                            for( $r = 1; $r <= 4; $r++ ) 
                                            {
                                                if( $cntsym[$r - 1] > 0 ) 
                                                {
                                                    $sMpl = $sMpl * $cntsym[$r - 1];
                                                }
                                            }
                                            $tWin = $slotSettings->Paytable['SYM_' . $csym][4] * $betLine * $sMpl;
                                            if( $cWin < $tWin ) 
                                            {
                                                $cWin = $tWin;
                                            }
                                        }
                                        if( $wsym[0] > 0 && $wsym[1] > 0 && $wsym[2] > 0 && $wsym[3] > 0 && $wsym[4] > 0 ) 
                                        {
                                            $sMpl = 1;
                                            $offsetMpl = 1;
                                            $offsetMpl0 = 1;
                                            for( $r = 1; $r <= 5; $r++ ) 
                                            {
                                                if( $cntsym[$r - 1] > 0 ) 
                                                {
                                                    $sMpl = $sMpl * $cntsym[$r - 1];
                                                }
                                            }
                                            $tWin = $slotSettings->Paytable['SYM_' . $csym][5] * $betLine * $sMpl;
                                            if( $cWin < $tWin ) 
                                            {
                                                $cWin = $tWin;
                                            }
                                        }
                                        $totalWin += $cWin;
                                        $cWins[$j] = sprintf('%01.2f', $cWin);
                                    }
                                }
                                $scattersWin = 0;
                                $scattersStr = '{';
                                $scattersCount = 0;
                                $symDouble = 1;
                                $scattersWin = 0;
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
                                $totalWin = $totalWin;
                                if( $slotSettings->MaxWin < ($totalWin * $slotSettings->CurrentDenom) ) 
                                {
                                }
                                else
                                {
                                    if( $i > 1000 ) 
                                    {
                                        $winType = 'none';
                                    }
                                    if( $i > 1500 ) 
                                    {
                                        $response = '{"responseEvent":"error","responseType":"' . $postData['slotEvent'] . '","serverResponse":"' . $totalWin . ' Bad Reel Strip"}';
                                        exit( $response );
                                    }
                                    $minWin = $slotSettings->GetRandomPay();
                                    if( $i > 700 ) 
                                    {
                                        $minWin = 0;
                                    }
                                    if( $slotSettings->increaseRTP && $winType == 'win' && $totalWin < ($minWin * $postData['bet'] * 10) ) 
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
                            $totalWin = sprintf('%01.2f', $totalWin);
                            if( $totalWin > 0 ) 
                            {
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                            }
                            if( $postData['slotEvent'] == 'freespin' && $slotSettings->GetGameData('SakuraSecretAMFreeGames') <= $slotSettings->GetGameData('SakuraSecretAMCurrentFreeGame') && $winType != 'bonus' && $slotSettings->GetGameData('SakuraSecretAMBonusWin') + $totalWin > 0 ) 
                            {
                                $slotSettings->SetBalance($slotSettings->GetGameData('SakuraSecretAMBonusWin') + $totalWin);
                            }
                            else if( $postData['slotEvent'] != 'freespin' && $winType != 'bonus' && $totalWin > 0 ) 
                            {
                                $slotSettings->SetBalance($totalWin);
                            }
                            $reportWin = $totalWin;
                            if( $postData['slotEvent'] == 'freespin' ) 
                            {
                                $slotSettings->SetGameData('SakuraSecretAMBonusWin', $slotSettings->GetGameData('SakuraSecretAMBonusWin') + $totalWin);
                                $slotSettings->SetGameData('SakuraSecretAMTotalWin', $totalWin);
                            }
                            else
                            {
                                $slotSettings->SetGameData('SakuraSecretAMTotalWin', $totalWin);
                            }
                            $gameState = '09';
                            if( $scattersCount >= 3 ) 
                            {
                                $gameState = '05';
                                $bonusMpl = $slotSettings->slotFreeMpl;
                                $scattersWin = $scattersWin * $bonusMpl;
                                if( $slotSettings->GetGameData('SakuraSecretAMFreeGames') > 0 ) 
                                {
                                    $slotSettings->SetGameData('SakuraSecretAMFreeGames', $slotSettings->GetGameData('SakuraSecretAMFreeGames') + $slotSettings->slotFreeCount);
                                }
                                else
                                {
                                    $slotSettings->SetGameData('SakuraSecretAMFreeStartWin', $totalWin);
                                    $slotSettings->SetGameData('SakuraSecretAMBonusWin', $totalWin);
                                    $slotSettings->SetGameData('SakuraSecretAMFreeGames', $slotSettings->slotFreeCount);
                                }
                            }
                            $wildState = '13' . implode('', $wildsArrF[0]) . '14' . implode('', $wildsArrF[1]) . '15' . implode('', $wildsArrF[2]) . '14' . implode('', $wildsArrF[3]) . '13' . implode('', $wildsArrF[4]) . '13' . implode('', $wildsArr[0]) . '14' . implode('', $wildsArr[1]) . '15' . implode('', $wildsArr[2]) . '14' . implode('', $wildsArr[3]) . '13' . implode('', $wildsArr[4]) . '';
                            $jsSpin = '' . json_encode($reels) . '';
                            $jsJack = '' . json_encode($slotSettings->Jackpots) . '';
                            $winString = implode(',', $lineWins);
                            $response_log = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{"symDouble":' . $symDouble . ',"slotLines":' . $lines . ',"slotBet":' . $betCnt . ',"totalFreeGames":' . $slotSettings->GetGameData('SakuraSecretAMFreeGames') . ',"currentFreeGames":' . $slotSettings->GetGameData('SakuraSecretAMCurrentFreeGame') . ',"Balance":' . $slotSettings->GetBalance() . ',"afterBalance":' . $slotSettings->GetBalance() . ',"bonusWin":' . $slotSettings->GetGameData('SakuraSecretAMBonusWin') . ',"freeStartWin":' . $slotSettings->GetGameData('SakuraSecretAMFreeStartWin') . ',"totalWin":' . $totalWin . ',"winLines":[' . $winString . '],"bonusInfo":' . $scattersStr . ',"Jackpots":' . $jsJack . ',"reelsSymbols":' . $jsSpin . '}}';
                            $slotSettings->SaveLogReport($response_log, $betLine, $lines, $reportWin, $postData['slotEvent']);
                            $playerId_ = $slotSettings->HexFormat(0);
                            $reelSate = $slotSettings->HexFormat($reels['rp'][0]) . $slotSettings->HexFormat($reels['rp'][1]) . $slotSettings->HexFormat($reels['rp'][2]) . $slotSettings->HexFormat($reels['rp'][3]) . $slotSettings->HexFormat($reels['rp'][4]);
                            $winLinesFormated = '';
                            for( $i = 0; $i < 9; $i++ ) 
                            {
                                $cWins[$i] = $cWins[$i] / $betLine / $bonusMpl;
                                $winLinesFormated .= $slotSettings->HexFormat(round(round($cWins[$i] * 10, 2)));
                            }
                            $ln_h = '09';
                            $bet_h = dechex($betCnt);
                            if( strlen($bet_h) <= 1 ) 
                            {
                                $bet_h = '0' . $bet_h;
                            }
                            if( $postData['slotEvent'] == 'freespin' ) 
                            {
                                $tFree = dechex($slotSettings->GetGameData('SakuraSecretAMFreeGames'));
                                $cFree = dechex($slotSettings->GetGameData('SakuraSecretAMFreeGames') - $slotSettings->GetGameData('SakuraSecretAMCurrentFreeGame'));
                                $gameState = '06';
                                if( $slotSettings->GetGameData('SakuraSecretAMFreeGames') <= $slotSettings->GetGameData('SakuraSecretAMCurrentFreeGame') ) 
                                {
                                    $gameState = '0c';
                                }
                                if( $scattersCount >= 3 ) 
                                {
                                    $gameState = '0a';
                                }
                                $freeInfo = strlen($tFree) . $tFree . strlen($cFree) . $cFree;
                                $freeWinState = '10';
                                if( $totalWin > 0 ) 
                                {
                                    $freeWinState = '19';
                                }
                                $totalWin = $slotSettings->GetGameData('SakuraSecretAMBonusWin');
                                $bonusMpl = $slotSettings->slotFreeMpl;
                            }
                            else
                            {
                                $tFree = dechex($slotSettings->GetGameData('SakuraSecretAMFreeGames'));
                                $freeWinState = '10';
                                $freeInfo = strlen($tFree) . $tFree . strlen($tFree) . $tFree;
                            }
                            $balanceFormated = $slotSettings->HexFormat(round($slotSettings->GetBalance() * $floatBet));
                            $response = '1' . $gameState . '010' . $balanceFormated . $slotSettings->HexFormat(round($totalWin * 100)) . $reelSate . $bet_h . $ln_h . $freeInfo . $freeWinState . $slotSettings->HexFormat($bonusMpl) . '1010' . $reelSate . '0' . $symCountAll . $winLinesFormated . implode('', $slotSettings->GetGameData($slotSettings->slotId . 'Cards')) . '' . $wildState . '#' . $addWild;
                            $response .= ('_' . json_encode($reels));
                            $slotSettings->SetGameData('SakuraSecretAMDoubleAnswer', $reelSate . $bet_h . $ln_h . $freeInfo . $freeWinState . $slotSettings->HexFormat($bonusMpl) . '1010' . $reelSate . '0' . $symCountAll . $winLinesFormated);
                            $slotSettings->SetGameData('SakuraSecretAMDoubleBalance', $balanceFormated);
                            $slotSettings->SetGameData('SakuraSecretAMDoubleWin', $totalWin);
                            if( $postData['slotEvent'] == 'freespin' ) 
                            {
                                $slotSettings->SetGameData('' . $slotSettings->slotId . 'DoubleWin', $slotSettings->GetGameData('' . $slotSettings->slotId . 'BonusWin'));
                            }
                            else
                            {
                                $slotSettings->SetGameData('' . $slotSettings->slotId . 'DoubleWin', $totalWin);
                            }
                            $response_collect0 = $reelSate . $bet_h . $ln_h . '1010101010101010101010' . '0' . $symCount . $winLinesFormated . implode('', $slotSettings->GetGameData($slotSettings->slotId . 'Cards')) . '#101010';
                            $slotSettings->SetGameData('SakuraSecretAMCollectP0', $response_collect0);
                            $gameState = '04';
                            $balanceFormated = $slotSettings->HexFormat(round($slotSettings->GetBalance() * $floatBet));
                            $response_collect = '1' . $gameState . '010' . $balanceFormated . $slotSettings->HexFormat(round($totalWin * 100)) . $reelSate . $bet_h . $ln_h . '1010101010101010101010' . '0' . $symCountAll . $winLinesFormated . implode('', $slotSettings->GetGameData($slotSettings->slotId . 'Cards')) . '#101010';
                            $slotSettings->SetGameData('SakuraSecretAMCollect', $response_collect);
                            break;
                        case 'A/u254':
                            $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                            $response = $slotSettings->GetGameData('SakuraSecretAMCollect');
                            $slotSettings->SetGameData('SakuraSecretAMTotalWin', 0);
                            break;
                        case 'A/u257':
                            $doubleWin = rand(1, 2);
                            $winall = $slotSettings->GetGameData('SakuraSecretAMDoubleWin');
                            $dbet = $winall;
                            $daction = $tmpPar[1];
                            if( $slotSettings->MaxWin < ($winall * $slotSettings->CurrentDenom) ) 
                            {
                                $doubleWin = 0;
                            }
                            if( $winall > 0 ) 
                            {
                                $slotSettings->SetBalance(-1 * $winall);
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $winall);
                            }
                            $ucard = '';
                            $casbank = $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''));
                            if( $daction <= 2 ) 
                            {
                                if( $casbank < ($winall * 2) ) 
                                {
                                    $doubleWin = 0;
                                }
                            }
                            else if( $casbank < ($winall * 4) ) 
                            {
                                $doubleWin = 0;
                            }
                            $reds = [
                                0, 
                                1, 
                                4, 
                                5, 
                                8, 
                                9, 
                                12, 
                                13, 
                                16, 
                                17, 
                                20, 
                                21, 
                                24, 
                                25, 
                                28, 
                                29, 
                                32, 
                                33, 
                                36, 
                                37, 
                                40, 
                                41, 
                                44, 
                                45, 
                                48, 
                                49, 
                                52
                            ];
                            $blacks = [
                                2, 
                                3, 
                                6, 
                                7, 
                                10, 
                                11, 
                                14, 
                                15, 
                                18, 
                                19, 
                                22, 
                                23, 
                                26, 
                                27, 
                                30, 
                                31, 
                                34, 
                                35, 
                                38, 
                                39, 
                                42, 
                                43, 
                                46, 
                                47, 
                                50, 
                                51
                            ];
                            $suit3 = [
                                0, 
                                4, 
                                8, 
                                12, 
                                16, 
                                20, 
                                24, 
                                28, 
                                32, 
                                36, 
                                40, 
                                44, 
                                48, 
                                52
                            ];
                            $suit4 = [
                                1, 
                                5, 
                                9, 
                                13, 
                                17, 
                                21, 
                                25, 
                                29, 
                                33, 
                                37, 
                                41, 
                                45, 
                                49, 
                                53
                            ];
                            $suit5 = [
                                2, 
                                6, 
                                10, 
                                14, 
                                18, 
                                22, 
                                26, 
                                30, 
                                34, 
                                38, 
                                42, 
                                46, 
                                50
                            ];
                            $suit6 = [
                                3, 
                                7, 
                                11, 
                                15, 
                                19, 
                                23, 
                                27, 
                                31, 
                                35, 
                                39, 
                                43, 
                                47, 
                                51
                            ];
                            if( $daction <= 2 ) 
                            {
                                $winall = $winall * 2;
                            }
                            else
                            {
                                $winall = $winall * 4;
                            }
                            if( $doubleWin == 1 ) 
                            {
                                if( $daction == 1 ) 
                                {
                                    $ucard = $reds[rand(0, 26)];
                                }
                                if( $daction == 2 ) 
                                {
                                    $ucard = $blacks[rand(0, 25)];
                                }
                                if( $daction == 3 ) 
                                {
                                    $ucard = $suit3[rand(0, 12)];
                                }
                                if( $daction == 4 ) 
                                {
                                    $ucard = $suit4[rand(0, 12)];
                                }
                                if( $daction == 5 ) 
                                {
                                    $ucard = $suit5[rand(0, 12)];
                                }
                                if( $daction == 6 ) 
                                {
                                    $ucard = $suit6[rand(0, 12)];
                                }
                            }
                            else
                            {
                                if( $daction == 1 ) 
                                {
                                    $ucard = $blacks[rand(0, 25)];
                                }
                                if( $daction == 2 ) 
                                {
                                    $ucard = $reds[rand(0, 26)];
                                }
                                if( $daction == 3 ) 
                                {
                                    $rnds = [
                                        4, 
                                        5, 
                                        6
                                    ];
                                    $ucard = ${'suit' . $rnds[rand(0, 2)]}[rand(0, 12)];
                                }
                                if( $daction == 4 ) 
                                {
                                    $rnds = [
                                        3, 
                                        5, 
                                        6
                                    ];
                                    $ucard = ${'suit' . $rnds[rand(0, 2)]}[rand(0, 12)];
                                }
                                if( $daction == 5 ) 
                                {
                                    $rnds = [
                                        4, 
                                        3, 
                                        6
                                    ];
                                    $ucard = ${'suit' . $rnds[rand(0, 2)]}[rand(0, 12)];
                                }
                                if( $daction == 6 ) 
                                {
                                    $rnds = [
                                        4, 
                                        5, 
                                        3
                                    ];
                                    $ucard = ${'suit' . $rnds[rand(0, 2)]}[rand(0, 12)];
                                }
                                $winall = 0;
                            }
                            $winall = sprintf('%01.2f', $winall) * $floatBet;
                            $winall_h1 = str_replace('.', '', $winall . '');
                            $winall_h = dechex($winall_h1);
                            $ucard = dechex($ucard);
                            if( strlen($ucard) <= 1 ) 
                            {
                                $ucard = '0' . $ucard;
                            }
                            $doubleCards = $slotSettings->GetGameData($slotSettings->slotId . 'Cards');
                            array_pop($doubleCards);
                            array_unshift($doubleCards, $ucard);
                            $slotSettings->SetGameData($slotSettings->slotId . 'Cards', $doubleCards);
                            $winall = $winall / 100;
                            if( $winall > 0 ) 
                            {
                                $slotSettings->SetBalance($winall);
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $winall);
                            }
                            $response = '107010' . $slotSettings->GetGameData('SakuraSecretAMDoubleBalance') . strlen($winall_h) . $winall_h . $slotSettings->GetGameData('SakuraSecretAMDoubleAnswer') . implode('', $slotSettings->GetGameData($slotSettings->slotId . 'Cards'));
                            $slotSettings->SetGameData('SakuraSecretAMDoubleWin', $winall);
                            $slotSettings->SetGameData('SakuraSecretAMTotalWin', $winall);
                            $balanceFormated = $slotSettings->HexFormat(round($slotSettings->GetBalance() * $floatBet));
                            $response_collect = '104010' . $balanceFormated . strlen($winall_h) . $winall_h . $slotSettings->GetGameData('SakuraSecretAMCollectP0');
                            $slotSettings->SetGameData('SakuraSecretAMCollect', $response_collect);
                            $response_log = '{"responseEvent":"gambleResult","serverResponse":{"totalWin":' . $winall . '}}';
                            if( $winall <= 0 ) 
                            {
                                $winall = -1 * $dbet;
                            }
                            $slotSettings->SaveLogReport($response_log, $dbet, 1, $winall, 'slotGamble');
                            break;
                        case 'A/u258':
                            $winall = $slotSettings->GetGameData('SakuraSecretAMDoubleWin');
                            if( $winall > 0.01 ) 
                            {
                                $winall22 = sprintf('%01.2f', $winall / 2);
                            }
                            else
                            {
                                $winall22 = 0;
                            }
                            $winall = $winall - $winall22;
                            $user_balance = $slotSettings->GetBalance() - $winall;
                            $slotSettings->SetGameData('SakuraSecretAMDoubleWin', $winall);
                            $slotSettings->SetGameData('SakuraSecretAMTotalWin', $winall);
                            $user_balance = sprintf('%01.2f', $user_balance);
                            $str_balance = str_replace('.', '', $user_balance . '');
                            $hexBalance = dechex($str_balance - 0);
                            $rtnBalance = strlen($hexBalance) . $hexBalance;
                            $slotSettings->SetGameData('SakuraSecretAMDoubleBalance', $rtnBalance);
                            $winall = sprintf('%01.2f', $winall) * $floatBet;
                            $winall_h1 = str_replace('.', '', $winall . '');
                            $winall_h = dechex($winall_h1);
                            $doubleCards = '26280b2714161d0c';
                            $response = '108010' . $slotSettings->GetGameData('SakuraSecretAMDoubleBalance') . strlen($winall_h) . $winall_h . $slotSettings->GetGameData('SakuraSecretAMDoubleAnswer') . implode('', $slotSettings->GetGameData($slotSettings->slotId . 'Cards'));
                            break;
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
