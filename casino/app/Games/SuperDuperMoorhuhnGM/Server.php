<?php 
namespace VanguardLTE\Games\SuperDuperMoorhuhnGM
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
                        $postData = $_REQUEST;
                        $balanceInCents = sprintf('%01.2f', $slotSettings->GetBalance()) * 100;
                        $result_tmp = [];
                        $aid = '';
                        $postData['command'] = $postData['func'];
                        if( $postData['command'] == 'gamble' && $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') <= 0 ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid gamble state"}';
                            exit( $response );
                        }
                        if( $postData['command'] == 'spin' ) 
                        {
                            $lines = $slotSettings->GetGameData($slotSettings->slotId . 'Lines');
                            $betline = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentBet');
                            if( $lines <= 0 || $betline <= 0.0001 ) 
                            {
                                $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid bet state"}';
                                exit( $response );
                            }
                            if( $slotSettings->GetBalance() < ($lines * $betline) ) 
                            {
                                $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid balance"}';
                                exit( $response );
                            }
                            if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') < $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') && $postData['freegame'] == '1' ) 
                            {
                                $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid bonus state"}';
                                exit( $response );
                            }
                            if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= 0 && $postData['freegame'] == '1' ) 
                            {
                                $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid bonus state"}';
                                exit( $response );
                            }
                        }
                        $aid = (string)$postData['command'];
                        switch( $aid ) 
                        {
                            case 'init':
                                $lastEvent = $slotSettings->GetHistory();
                                $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'BetStep', 0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'CurrentBet', $slotSettings->Bet[0]);
                                $slotSettings->SetGameData($slotSettings->slotId . 'Lines', 10);
                                if( $lastEvent != 'NULL' ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $lastEvent->serverResponse->bonusWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $lastEvent->serverResponse->totalFreeGames);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', $lastEvent->serverResponse->currentFreeGames);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $lastEvent->serverResponse->bonusWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeBalance', $lastEvent->serverResponse->Balance);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentBet', $lastEvent->serverResponse->CurrentBet);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BetStep', $lastEvent->serverResponse->BetStep);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'Lines', $lastEvent->serverResponse->Lines);
                                    $reels = (array)$lastEvent->serverResponse->reelsSymbols;
                                    $reels['reel1'] = (array)$reels['reel1'];
                                    $reels['reel2'] = (array)$reels['reel2'];
                                    $reels['reel3'] = (array)$reels['reel3'];
                                    $reels['reel4'] = (array)$reels['reel4'];
                                    $reels['reel5'] = (array)$reels['reel5'];
                                    $curBet = $lastEvent->serverResponse->CurrentBet;
                                    $curLines = $lastEvent->serverResponse->Lines;
                                    $r1 = 'i:0;i:' . $reels['reel1'][2] . ';i:1;i:' . $reels['reel1'][1] . ';i:2;i:' . $reels['reel1'][0] . ';i:3;i:' . rand(1, 7) . ';';
                                    $r2 = 'i:0;i:' . $reels['reel2'][2] . ';i:1;i:' . $reels['reel2'][1] . ';i:2;i:' . $reels['reel2'][0] . ';i:3;i:' . rand(1, 7) . ';';
                                    $r3 = 'i:0;i:' . $reels['reel3'][2] . ';i:1;i:' . $reels['reel3'][1] . ';i:2;i:' . $reels['reel3'][0] . ';i:3;i:' . rand(1, 7) . ';';
                                    $r4 = 'i:0;i:' . $reels['reel4'][2] . ';i:1;i:' . $reels['reel4'][1] . ';i:2;i:' . $reels['reel4'][0] . ';i:3;i:' . rand(1, 7) . ';';
                                    $r5 = 'i:0;i:' . $reels['reel5'][2] . ';i:1;i:' . $reels['reel5'][1] . ';i:2;i:' . $reels['reel5'][0] . ';i:3;i:' . rand(1, 7) . ';';
                                    $actFreegame = 0;
                                }
                                else
                                {
                                    $curBet = $slotSettings->Bet[0];
                                    $curLines = 20;
                                    $actFreegame = 0;
                                    $slotSettings->SetGameData($slotSettings->slotId . 'Lines', $curLines);
                                    $r1 = 'i:0;i:' . rand(1, 7) . ';i:1;i:' . rand(1, 7) . ';i:2;i:' . rand(1, 7) . ';i:3;i:' . rand(1, 7) . ';';
                                    $r2 = 'i:0;i:' . rand(1, 7) . ';i:1;i:' . rand(1, 7) . ';i:2;i:' . rand(1, 7) . ';i:3;i:' . rand(1, 7) . ';';
                                    $r3 = 'i:0;i:' . rand(1, 7) . ';i:1;i:' . rand(1, 7) . ';i:2;i:' . rand(1, 7) . ';i:3;i:' . rand(1, 7) . ';';
                                    $r4 = 'i:0;i:' . rand(1, 7) . ';i:1;i:' . rand(1, 7) . ';i:2;i:' . rand(1, 7) . ';i:3;i:' . rand(1, 7) . ';';
                                    $r5 = 'i:0;i:' . rand(1, 7) . ';i:1;i:' . rand(1, 7) . ';i:2;i:' . rand(1, 7) . ';i:3;i:' . rand(1, 7) . ';';
                                }
                                if( $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') < $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                                {
                                    $actFreegame = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1;
                                }
                                $betString = '';
                                for( $i = 0; $i < count($slotSettings->Bet); $i++ ) 
                                {
                                    $betString .= ('i:' . $i . ';i:' . ($slotSettings->Bet[$i] * 100) . ';');
                                }
                                $result_tmp[0] = 'INFO$$1$$a:1:{s:8:"initData";a:40:{s:5:"reels";a:5:{s:21:"reel_a_initsymboldata";a:4:{' . $r1 . '}s:21:"reel_b_initsymboldata";a:4:{' . $r2 . '}s:21:"reel_c_initsymboldata";a:4:{' . $r3 . '}s:21:"reel_d_initsymboldata";a:4:{' . $r4 . '}s:21:"reel_e_initsymboldata";a:4:{' . $r5 . '}}s:9:"possLines";a:3:{i:0;i:5;i:1;i:10;i:2;i:20;}s:15:"maxLinesPerReel";i:0;s:12:"possLinebets";a:' . count($slotSettings->Bet) . ':{' . $betString . '}s:18:"linesForOneLinebet";i:1;s:8:"maxiplay";b:0;s:17:"maxiplayBetFactor";i:1;s:5:"money";i:' . $balanceInCents . ';s:7:"linebet";i:' . ($curBet * 100) . ";s:5:\"lines\";i:20;s:14:\"autospinConfig\";a:9:{i:0;i:10;i:1;i:25;i:2;i:50;i:3;i:75;i:4;i:100;i:5;i:250;i:6;i:500;i:7;i:750;i:8;i:1000;}s:18:\"unlimitedAutospins\";b:1;s:19:\"gamblePulseInterval\";i:544;s:18:\"hasMaxiplayFeature\";b:0;s:13:\"attentionSpin\";b:0;s:12:\"maxGambleBet\";i:15000;s:14:\"maxGambleTries\";i:0;s:15:\"useGambleLadder\";b:1;s:18:\"maxGambleLadderBet\";i:600000000;s:13:\"useGambleCard\";b:1;s:19:\"useUniqueStopButton\";b:0;s:10:\"inDemoMode\";b:0;s:12:\"actFreeRound\";i:0;s:13:\"maxFreeRounds\";i:0;s:12:\"freeRoundBet\";i:0;s:12:\"freeRoundWin\";i:0;s:30:\"useExternalFreeRoundsIntroSign\";b:0;s:27:\"dontShowFreeRoundsOutroSign\";b:0;s:32:\"dontShowWinOnFreeRoundsOutroSign\";b:1;s:20:\"freeRoundsIntroDelay\";i:0;s:20:\"freeRoundsOutroDelay\";i:3000;s:18:\"linemarkersContent\";s:1994:\"\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\n<div class=\"linemarkersBox\">\n\n\t<div class=\"fl linemarkersColumn\">\n\t\t\t\t\t\t\t\t\t<div class=\"linemarker big lines20  \">\n\t\t\t\t\t<div class=\"linemarkerValue\">20</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarkerLabel hidden\">LINES</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarker big lines10  \">\n\t\t\t\t\t<div class=\"linemarkerValue\">10</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarkerLabel hidden\">LINES</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarker big lines5  \">\n\t\t\t\t\t<div class=\"linemarkerValue\">5</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarkerLabel hidden\">LINES</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t<div class=\"fr linemarkersColumn\">\n\t\t\t\t\t\t\t\t\t<div class=\"linemarker big lines20  \">\n\t\t\t\t\t<div class=\"linemarkerValue\">20</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarkerLabel hidden\">LINES</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarker big lines10  \">\n\t\t\t\t\t<div class=\"linemarkerValue\">10</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarkerLabel hidden\">LINES</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarker big lines5  \">\n\t\t\t\t\t<div class=\"linemarkerValue\">5</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarkerLabel hidden\">LINES</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\n\t<div class=\"clear\"></div>\n\n</div>\n\n\n<div class=\"linemarkerClickareasBox\">\n\n\t<div class=\"fl linemarkerClickareasColumn\">\n\t\t\t\t\t\t\t\t\t<div class=\"linemarkerClickarea big lines20 deactivated\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarkerClickarea big lines10 deactivated\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarkerClickarea big lines5 deactivated\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t<div class=\"fr linemarkerClickareasColumn\">\n\t\t\t\t\t\t\t\t\t<div class=\"linemarkerClickarea big lines20 deactivated\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarkerClickarea big lines10 deactivated\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"linemarkerClickarea big lines5 deactivated\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\n\t<div class=\"clear\"></div>\n\n</div>\";s:19:\"linemarkersContentX\";s:484:\"\n<div class=\"linemarkersBoxX\">\n\n\t<div class=\"fl linemarkersColumn\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t<div class=\"fr linemarkersColumn\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\n\t<div class=\"clear\"></div>\n\n</div>\";s:5:\"sound\";b:1;s:8:\"spaceBar\";b:1;s:9:\"quickSpin\";b:0;s:12:\"leftHandMode\";b:0;s:16:\"featureCountdown\";b:0;s:16:\"showJackpotIntro\";b:0;s:9:\"sessionID\";s:26:\"g2f00dics0r529vs6iktr56mc5\";}}";
                                break;
                            case 'applyUserRequest':
                                $gameBets = $slotSettings->Bet;
                                $req = $_REQUEST['request'];
                                $bstep = $slotSettings->GetGameData($slotSettings->slotId . 'BetStep');
                                if( $req == 'linebetplus' ) 
                                {
                                    $bstep++;
                                    if( count($gameBets) - 1 < $bstep ) 
                                    {
                                        $bstep = 0;
                                    }
                                    $cbet = $gameBets[$bstep];
                                    $result_tmp[] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:7:"linebet";s:5:"value";i:' . ($cbet * 100) . ';}}}';
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BetStep', $bstep);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentBet', $cbet);
                                }
                                if( $req == 'maxbet' ) 
                                {
                                    $bstep++;
                                    $bstep = count($gameBets) - 1;
                                    $cbet = $gameBets[$bstep];
                                    $result_tmp[] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:7:"linebet";s:5:"value";i:' . ($cbet * 100) . ';}}}';
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BetStep', $bstep);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentBet', $cbet);
                                }
                                if( $req == 'linebetminus' ) 
                                {
                                    $bstep--;
                                    if( $bstep < 0 ) 
                                    {
                                        $bstep = count($gameBets) - 1;
                                    }
                                    $cbet = $gameBets[$bstep];
                                    $result_tmp[] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:7:"linebet";s:5:"value";i:' . ($cbet * 100) . ';}}}';
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BetStep', $bstep);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentBet', $cbet);
                                }
                                if( $req == 'linesplus' ) 
                                {
                                    $lines = $slotSettings->GetGameData($slotSettings->slotId . 'Lines');
                                    $lines += 10;
                                    if( $lines > 20 ) 
                                    {
                                        $lines = 10;
                                    }
                                    $result_tmp[] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:5:"lines";s:5:"value";i:' . $lines . ';}}}';
                                    $slotSettings->SetGameData($slotSettings->slotId . 'Lines', $lines);
                                }
                                if( $req == 'linesminus' ) 
                                {
                                    $lines = $slotSettings->GetGameData($slotSettings->slotId . 'Lines');
                                    $lines -= 10;
                                    if( $lines < 10 ) 
                                    {
                                        $lines = 20;
                                    }
                                    $result_tmp[] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:5:"lines";s:5:"value";i:' . $lines . ';}}}';
                                    $slotSettings->SetGameData($slotSettings->slotId . 'Lines', $lines);
                                }
                                break;
                            case 'setLines':
                                if( $postData['lines'] <= 0 ) 
                                {
                                    exit();
                                }
                                $slotSettings->SetGameData($slotSettings->slotId . 'Lines', $postData['lines']);
                                $result_tmp[] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:5:"lines";s:5:"value";i:' . $postData['lines'] . ';}}}';
                                break;
                            case 'setSpacebar':
                                $result_tmp[] = 'INFO$$1$$a:0:{}';
                                break;
                            case 'setQuickspin':
                                $result_tmp[] = 'INFO$$1$$a:0:{}';
                                break;
                            case 'setFeatureCountdown':
                                $result_tmp[] = 'INFO$$1$$a:0:{}';
                                break;
                            case 'noGamble':
                                $result_tmp[] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:8:"noGamble";s:5:"value";a:1:{s:17:"enforceNextAction";s:4:"wait";}}}}';
                                break;
                            case 'setSound':
                                $result_tmp[] = 'INFO$$1$$a:0:{}';
                                break;
                            case 'showInfoSite':
                                if( $_REQUEST['section'] == 'paytable' ) 
                                {
                                    $bet = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentBet');
                                    $lines = $slotSettings->GetGameData($slotSettings->slotId . 'Lines');
                                    $infoStr = "<div class=\"infoPopup webInfo info1 en\">\n\t\t\n\t<div class=\"scrollContainer\">\n\t\t<div class=\"bigHeadline\">PAYTABLE</div>\n\t\t\n\t\t<div class=\"headline\">EXTRA EGGS POWER</div>\n\t\t<div class=\"description\">Is there a winning combination with at least one 5-&#x200B;eggs-&#x200B;combination the feature is triggered.</div>\n\t\t<div class=\"seven-feature\"></div>\n\t\t<div class=\"description\">The featuecho s all visible \"eggs\" into one other random symbol with a higher value.</div>\n\t\n\t\t<div class=\"headline\">PAYOUT VALUES</div>\n\t\t<div class=\"centerColumn paytableValues\" id=\"paytableValues\">\n\t\t\t\n\t\t\t\t\t<div class=\"fl winValueBox winValues-7\" id=\"winValues-7\">\n\t\t\t\t\t<div class=\"fl symbolImg symbolImg-7\"></div>\n\t\t\t\t\t<div class=\"fr values \">\n\t\t\t\t\t\t\t\t<div class=\"winValue widthReference\" id=\"value0-5\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">5</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_7'][5] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value1-4\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">4</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_7'][4] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value2-3\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">3</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_7'][3] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"clear\"></div>\n\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"fr winValueBox winValues-6\" id=\"winValues-6\">\n\t\t\t\t\t<div class=\"fl symbolImg symbolImg-6\"></div>\n\t\t\t\t\t<div class=\"fr values \">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue widthReference\" id=\"value16-5\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">5</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_6'][5] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value17-4\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">4</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_6'][4] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value18-3\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">3</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_6'][3] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"clear\"></div>\n\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"fl winValueBox winValues-5\" id=\"winValues-5\">\n\t\t\t\t\t<div class=\"fl symbolImg symbolImg-5\"></div>\n\t\t\t\t\t<div class=\"fr values \">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue widthReference\" id=\"value19-5\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">5</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_5'][5] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value20-4\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">4</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_5'][4] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value21-3\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">3</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_5'][3] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"clear\"></div>\n\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"fr winValueBox winValues-4\" id=\"winValues-4\">\n\t\t\t\t\t<div class=\"fl symbolImg symbolImg-4\"></div>\n\t\t\t\t\t<div class=\"fr values \">\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue widthReference\" id=\"value22-5\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">5</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_4'][5] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value23-4\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">4</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_4'][4] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value24-3\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">3</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_4'][3] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"clear\"></div>\n\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"fl winValueBox winValues-3\" id=\"winValues-3\">\n\t\t\t\t\t<div class=\"fl symbolImg symbolImg-3\"></div>\n\t\t\t\t\t<div class=\"fr values \">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue widthReference\" id=\"value25-5\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">5</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_3'][5] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value26-4\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">4</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_3'][4] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value27-3\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">3</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_3'][3] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"clear\"></div>\n\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"fr winValueBox winValues-2\" id=\"winValues-2\">\n\t\t\t\t\t<div class=\"fl symbolImg symbolImg-2\"></div>\n\t\t\t\t\t<div class=\"fr values \">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue widthReference\" id=\"value28-5\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">5</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_2'][5] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value29-4\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">4</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_2'][4] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value30-3\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">3</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_2'][3] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"clear\"></div>\n\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"fl winValueBox winValues-1\" id=\"winValues-1\">\n\t\t\t\t\t<div class=\"fl symbolImg symbolImg-1\"></div>\n\t\t\t\t\t<div class=\"fr values fourLines\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue widthReference\" id=\"value18-5\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">5</span>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"multiplicator winValueText\">FEATURE</span>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue widthReference\" id=\"value18-5\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">4</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_1'][4] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value19-4\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">3</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_1'][3] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"winValue \" id=\"value20-3\">\n\t\t\t\t\t\t\t\t<span class=\"multiplicator\">2</span>\n\t\t\t\t\t\t\t\t<span class=\"winValueText\">" . sprintf('%01.2f', $slotSettings->Paytable['SYM_1'][2] * $bet) . "</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"clear\"></div>\n\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"clear\"></div>\n\t\t</div>\n\t\t\n\t\t<div class=\"headline\">PAYLINES</div>\n\t\t<div class=\"paylines\"></div>\n\t\t\n\t</div>\n\n</div>";
                                    $result_tmp[] = 'INFO$$1$$a:1:{s:7:"display";a:2:{i:0;a:2:{s:4:"type";s:7:"linebet";s:5:"value";i:' . ($bet * 100) . ';}i:1;a:2:{s:4:"type";s:4:"info";s:5:"value";s:' . strlen($infoStr) . ':"' . $infoStr . '";}}}';
                                }
                                else
                                {
                                    $result_tmp[0] = "INFO\$\$1\$\$a:1:{s:7:\"display\";a:1:{i:0;a:3:{s:4:\"type\";s:4:\"info\";s:5:\"value\";s:960:\"<div class=\"infoPopup webInfo info2 en\">\n\t\n\t\t<div class=\"settingsBox\">\n\t\t\t\t\t\t\t<div class=\"setting\">\n\t\t\t\t\t<div class=\"setGameSetting settingCheckbox active fl setting1\" id=\"settingCheckbox-1\"></div>\n\t\t\t\t\t<div class=\"settingText fl setting1\">SPIN WITH SPACEBAR</div>\n\t\t\t\t\t<div class=\"clear\"></div>\n\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"setting quickspin\">\n\t\t\t\t<div class=\"setGameSetting settingCheckbox active fl setting2\" id=\"settingCheckbox-2\"></div>\n\t\t\t\t<div class=\"settingText fl setting2\">TURBO SPIN</div>\n\t\t\t\t<div class=\"clear\"></div>\n\t\t\t</div>\n\t\t\t<div class=\"setting featureCountdown\">\n\t\t\t\t<div class=\"setGameSetting settingCheckbox active fl setting3\" id=\"settingCheckbox-3\"></div>\n\t\t\t\t<div class=\"settingText fl setting3\">\n\t\t\t\t\t<span class=\"freegame hidden\">AUTOMATIC FREE GAMES ENTRY</span>\n\t\t\t\t\t<span class=\"feature hidden\">AUTOMATIC FEATURE ENTRY</span>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"clear\"></div>\n\t\t\t</div>\n\t\t\t\t\t</div>\n\t\n\t<div class=\"closeButton\"></div>\t\n</div>\";s:14:\"isSettingsSite\";b:1;}}}";
                                }
                                break;
                            case 'triggerGameEvent':
                                $result_tmp[] = 'INFO$$1$$a:0:{}';
                                break;
                            case 'spin':
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
                                    2, 
                                    1, 
                                    1, 
                                    1, 
                                    2
                                ];
                                $linesId[6] = [
                                    2, 
                                    3, 
                                    3, 
                                    3, 
                                    2
                                ];
                                $linesId[7] = [
                                    3, 
                                    3, 
                                    2, 
                                    1, 
                                    1
                                ];
                                $linesId[8] = [
                                    1, 
                                    1, 
                                    2, 
                                    3, 
                                    3
                                ];
                                $linesId[9] = [
                                    3, 
                                    2, 
                                    2, 
                                    2, 
                                    1
                                ];
                                $linesId[10] = [
                                    1, 
                                    2, 
                                    2, 
                                    2, 
                                    3
                                ];
                                $linesId[11] = [
                                    1, 
                                    1, 
                                    2, 
                                    1, 
                                    1
                                ];
                                $linesId[12] = [
                                    3, 
                                    3, 
                                    2, 
                                    3, 
                                    3
                                ];
                                $linesId[13] = [
                                    1, 
                                    3, 
                                    1, 
                                    3, 
                                    1
                                ];
                                $linesId[14] = [
                                    3, 
                                    1, 
                                    3, 
                                    1, 
                                    3
                                ];
                                $linesId[15] = [
                                    1, 
                                    2, 
                                    1, 
                                    2, 
                                    1
                                ];
                                $linesId[16] = [
                                    3, 
                                    2, 
                                    3, 
                                    2, 
                                    3
                                ];
                                $linesId[17] = [
                                    2, 
                                    1, 
                                    2, 
                                    1, 
                                    2
                                ];
                                $linesId[18] = [
                                    2, 
                                    3, 
                                    2, 
                                    3, 
                                    2
                                ];
                                $linesId[19] = [
                                    2, 
                                    2, 
                                    1, 
                                    2, 
                                    2
                                ];
                                $linesId[20] = [
                                    2, 
                                    2, 
                                    3, 
                                    2, 
                                    2
                                ];
                                $linesId[21] = [
                                    1, 
                                    3, 
                                    2, 
                                    3, 
                                    1
                                ];
                                $linesId[22] = [
                                    3, 
                                    1, 
                                    2, 
                                    1, 
                                    3
                                ];
                                $linesId[23] = [
                                    2, 
                                    1, 
                                    3, 
                                    1, 
                                    2
                                ];
                                $linesId[24] = [
                                    2, 
                                    3, 
                                    1, 
                                    3, 
                                    2
                                ];
                                $linesId[25] = [
                                    1, 
                                    3, 
                                    3, 
                                    3, 
                                    1
                                ];
                                $linesId[26] = [
                                    3, 
                                    1, 
                                    1, 
                                    1, 
                                    3
                                ];
                                $linesId[27] = [
                                    1, 
                                    1, 
                                    3, 
                                    1, 
                                    1
                                ];
                                $linesId[28] = [
                                    3, 
                                    3, 
                                    1, 
                                    3, 
                                    3
                                ];
                                $linesId[29] = [
                                    1, 
                                    2, 
                                    2, 
                                    2, 
                                    1
                                ];
                                $lines = $slotSettings->GetGameData($slotSettings->slotId . 'Lines');
                                $betline = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentBet');
                                $allbet = $betline * $lines;
                                $postData['slotEvent'] = 'bet';
                                if( $postData['freegame'] == '1' ) 
                                {
                                    $postData['slotEvent'] = 'freespin';
                                }
                                if( $postData['slotEvent'] != 'freespin' ) 
                                {
                                    if( !isset($postData['slotEvent']) ) 
                                    {
                                        $postData['slotEvent'] = 'bet';
                                    }
                                    $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                                    $slotSettings->UpdateJackpots($allbet);
                                    $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
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
                                $balanceInCents = sprintf('%01.2f', $slotSettings->GetBalance()) * 100;
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
                                        0
                                    ];
                                    $wild = ['11'];
                                    $scatter = '12';
                                    $reels = $slotSettings->GetReelStrips('', $postData['slotEvent']);
                                    $fiveOfAKind = 0;
                                    $specFeatureSymbolPositions = 'a:0:{}';
                                    if( $winType == 'bonus' ) 
                                    {
                                        $rline = rand(1, $lines - 1);
                                        for( $jl = 1; $jl <= 5; $jl++ ) 
                                        {
                                            $jreel = $slotSettings->PutBonusToLine($jl, $linesId[$rline][$jl - 1], 1);
                                            $reels['reel' . $jl] = $jreel['reel'];
                                            $reels['rp'][$jl - 1] = $jreel['rp'];
                                        }
                                    }
                                    $reelsTmp = $reels;
                                    for( $k = 0; $k < $lines; $k++ ) 
                                    {
                                        $s[0] = $reels['reel1'][$linesId[$k][0] - 1];
                                        $s[1] = $reels['reel2'][$linesId[$k][1] - 1];
                                        $s[2] = $reels['reel3'][$linesId[$k][2] - 1];
                                        $s[3] = $reels['reel4'][$linesId[$k][3] - 1];
                                        $s[4] = $reels['reel5'][$linesId[$k][4] - 1];
                                        if( $s[0] == 1 && $s[1] == 1 && $s[2] == 1 && $s[3] == 1 && $s[4] == 1 ) 
                                        {
                                            $cherrySymArr = [
                                                [], 
                                                [], 
                                                [], 
                                                [], 
                                                []
                                            ];
                                            $cherrySym = rand(2, 7);
                                            for( $chr = 1; $chr <= 5; $chr++ ) 
                                            {
                                                if( $reels['reel' . $chr][0] == 1 ) 
                                                {
                                                    $reels['reel' . $chr][0] = $cherrySym;
                                                    $cherrySymArr[$chr - 1][] = 'i:0;i:' . $cherrySym . ';';
                                                }
                                                if( $reels['reel' . $chr][1] == 1 ) 
                                                {
                                                    $reels['reel' . $chr][1] = $cherrySym;
                                                    $cherrySymArr[$chr - 1][] = 'i:1;i:' . $cherrySym . ';';
                                                }
                                                if( $reels['reel' . $chr][2] == 1 ) 
                                                {
                                                    $reels['reel' . $chr][2] = $cherrySym;
                                                    $cherrySymArr[$chr - 1][] = 'i:2;i:' . $cherrySym . ';';
                                                }
                                            }
                                            $specFeatureSymbolPositions = 'a:5:{s:1:"a";a:' . count($cherrySymArr[0]) . ':{' . implode('', $cherrySymArr[0]) . '}s:1:"b";a:' . count($cherrySymArr[1]) . ':{' . implode('', $cherrySymArr[1]) . '}s:1:"c";a:' . count($cherrySymArr[2]) . ':{' . implode('', $cherrySymArr[2]) . '}s:1:"d";a:' . count($cherrySymArr[3]) . ':{' . implode('', $cherrySymArr[3]) . '}s:1:"e";a:' . count($cherrySymArr[4]) . ':{' . implode('', $cherrySymArr[4]) . '}}';
                                            break;
                                        }
                                    }
                                    $reels2 = $reels;
                                    $winCount = 1;
                                    $symbolAnimsArr = [];
                                    for( $k = 0; $k < $lines; $k++ ) 
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
                                                    }
                                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][2] * $betline * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $tmpStringWin = 'i:' . $winCount . ';a:6:{i:0;i:' . ($k + 1) . ';i:1;i:' . $s[0] . ';i:2;i:' . $s[1] . ';i:3;b:0;i:4;b:0;i:5;b:0;}';
                                                        $reels2['reel1'][$linesId[$k][0] - 1] = -1;
                                                        $reels2['reel2'][$linesId[$k][1] - 1] = -1;
                                                    }
                                                }
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
                                                    }
                                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][3] * $betline * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $tmpStringWin = 'i:' . $winCount . ';a:6:{i:0;i:' . ($k + 1) . ';i:1;i:' . $s[0] . ';i:2;i:' . $s[1] . ';i:3;i:' . $s[2] . ';i:4;b:0;i:5;b:0;}';
                                                        $reels2['reel1'][$linesId[$k][0] - 1] = -1;
                                                        $reels2['reel2'][$linesId[$k][1] - 1] = -1;
                                                        $reels2['reel3'][$linesId[$k][2] - 1] = -1;
                                                    }
                                                }
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
                                                    }
                                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][4] * $betline * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $tmpStringWin = 'i:' . $winCount . ';a:6:{i:0;i:' . ($k + 1) . ';i:1;i:' . $s[0] . ';i:2;i:' . $s[1] . ';i:3;i:' . $s[2] . ';i:4;i:' . $s[3] . ';i:5;b:0;}';
                                                        $reels2['reel1'][$linesId[$k][0] - 1] = -1;
                                                        $reels2['reel2'][$linesId[$k][1] - 1] = -1;
                                                        $reels2['reel3'][$linesId[$k][2] - 1] = -1;
                                                        $reels2['reel4'][$linesId[$k][3] - 1] = -1;
                                                    }
                                                }
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
                                                    }
                                                    $tmpWin = $slotSettings->Paytable['SYM_' . $csym][5] * $betline * $mpl * $bonusMpl;
                                                    if( $cWins[$k] < $tmpWin ) 
                                                    {
                                                        $cWins[$k] = $tmpWin;
                                                        $tmpStringWin = 'i:' . $winCount . ';a:6:{i:0;i:' . ($k + 1) . ';i:1;i:' . $s[0] . ';i:2;i:' . $s[1] . ';i:3;i:' . $s[2] . ';i:4;i:' . $s[3] . ';i:5;i:' . $s[4] . ';}';
                                                        $reels2['reel1'][$linesId[$k][0] - 1] = -1;
                                                        $reels2['reel2'][$linesId[$k][1] - 1] = -1;
                                                        $reels2['reel3'][$linesId[$k][2] - 1] = -1;
                                                        $reels2['reel4'][$linesId[$k][3] - 1] = -1;
                                                        $reels2['reel5'][$linesId[$k][4] - 1] = -1;
                                                        $fiveOfAKind = 1;
                                                    }
                                                }
                                            }
                                        }
                                        if( $cWins[$k] > 0 && $tmpStringWin != '' ) 
                                        {
                                            array_push($lineWins, $tmpStringWin);
                                            $totalWin += $cWins[$k];
                                            $winCount++;
                                        }
                                    }
                                    $scattersWin = 0;
                                    $scattersStr = '';
                                    $scattersCount = 0;
                                    $scPos = [
                                        'i:0;a:6:{i:0;i:0;', 
                                        'i:1;b:0;', 
                                        'i:2;b:0;', 
                                        'i:3;b:0;', 
                                        'i:4;b:0;', 
                                        'i:5;b:0;', 
                                        '}'
                                    ];
                                    for( $r = 1; $r <= 5; $r++ ) 
                                    {
                                        if( $reels['reel' . $r][0] == $scatter || $reels['reel' . $r][1] == $scatter || $reels['reel' . $r][2] == $scatter ) 
                                        {
                                            $scattersCount++;
                                            $scPos[$r] = 'i:' . $r . ';i:' . $scatter . ';';
                                        }
                                    }
                                    $scattersWin = $slotSettings->Paytable['SYM_' . $scatter][$scattersCount] * $allbet * $bonusMpl;
                                    $scattersStr = implode('', $scPos);
                                    if( $scattersCount >= 3 ) 
                                    {
                                        array_push($lineWins, $scattersStr);
                                    }
                                    $totalWin += $scattersWin;
                                    $winCountAnim = 0;
                                    for( $r = 1; $r <= 5; $r++ ) 
                                    {
                                        for( $p = 0; $p <= 2; $p++ ) 
                                        {
                                            if( $reels2['reel' . $r][$p] == -1 ) 
                                            {
                                                if( $reels['reel' . $r][$p] == 10 ) 
                                                {
                                                    $animStr = 'wild';
                                                    $symbolAnimsArr[] = 'i:' . $winCountAnim . ';a:4:{s:4:"reel";i:' . ($r - 1) . ';s:3:"pos";i:' . $p . ';s:9:"animation";s:4:"' . $animStr . '";s:6:"symbol";i:' . $reels['reel' . $r][$p] . ';}';
                                                }
                                                else
                                                {
                                                    $animStr = 'smallWin' . $reels['reel' . $r][$p];
                                                    $symbolAnimsArr[] = 'i:' . $winCountAnim . ';a:4:{s:4:"reel";i:' . ($r - 1) . ';s:3:"pos";i:' . $p . ';s:9:"animation";s:9:"' . $animStr . '";s:6:"symbol";i:' . $reels['reel' . $r][$p] . ';}';
                                                }
                                                $winCountAnim++;
                                            }
                                        }
                                    }
                                    $totalWin += $scattersWin;
                                    if( $i > 1000 ) 
                                    {
                                        $winType = 'none';
                                    }
                                    if( $i > 1500 ) 
                                    {
                                        $response = '{"responseEvent":"error","responseType":"' . $postData['slotEvent'] . '","serverResponse":"Bad Reel Strip"}';
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
                                $reels = $reelsTmp;
                                if( $totalWin > 0 ) 
                                {
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $totalWin);
                                    $slotSettings->SetBalance($totalWin);
                                }
                                $reportWin = $totalWin;
                                $actFreegame = 0;
                                $isFreeGame = 0;
                                if( $postData['slotEvent'] == 'freespin' ) 
                                {
                                    $actFreegame = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1;
                                    $isFreeGame = 1;
                                    $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') + $totalWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin') + $totalWin);
                                }
                                else
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $totalWin);
                                }
                                $fs = 0;
                                if( $scattersCount >= 3 ) 
                                {
                                    $actFreegame = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') + 1;
                                    if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') > 0 ) 
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') + $slotSettings->slotFreeCount);
                                    }
                                    else
                                    {
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeStartWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'BonusWin', $totalWin);
                                        $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', $slotSettings->slotFreeCount);
                                    }
                                    $fs = $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames');
                                }
                                if( isset($currentFireWin) ) 
                                {
                                    $totalWin -= $currentFireWin;
                                }
                                $winString = 'a:' . count($lineWins) . ':{' . implode('', $lineWins) . '}';
                                $jsSpin = '' . json_encode($reels) . '';
                                $jsJack = '' . json_encode($slotSettings->Jackpots) . '';
                                $response = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{"BetStep":' . $slotSettings->GetGameData($slotSettings->slotId . 'BetStep') . ',"Lines":' . $slotSettings->GetGameData($slotSettings->slotId . 'Lines') . ',"CurrentBet":' . $slotSettings->GetGameData($slotSettings->slotId . 'CurrentBet') . ',"totalFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') . ',"currentFreeGames":' . $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') . ',"Balance":' . $balanceInCents . ',"afterBalance":' . $balanceInCents . ',"bonusWin":' . $slotSettings->GetGameData($slotSettings->slotId . 'BonusWin') . ',"totalWin":' . $totalWin . ',"winLines":[],"Jackpots":' . $jsJack . ',"reelsSymbols":' . $jsSpin . '}}';
                                $slotSettings->SaveLogReport($response, $allbet, $lines, $reportWin, $postData['slotEvent']);
                                $reelStr = [];
                                $rsCount = [
                                    8, 
                                    12, 
                                    16, 
                                    20, 
                                    24
                                ];
                                for( $rlp = 0; $rlp < 5; $rlp++ ) 
                                {
                                    $reelStr[$rlp] = 'a:' . $rsCount[$rlp] . ':{';
                                    for( $rlp0 = 0; $rlp0 < ($rsCount[$rlp] - 4); $rlp0++ ) 
                                    {
                                        $rsym = $slotSettings->SymbolGame[rand(0, count($slotSettings->SymbolGame) - 1)];
                                        $reelStr[$rlp] .= ('i:' . $rlp0 . ';i:' . $rsym . ';');
                                    }
                                }
                                $rsym = $slotSettings->SymbolGame[rand(0, count($slotSettings->SymbolGame) - 1)];
                                $reelStr[0] .= ('i:4;i:' . $reels['reel1'][2] . ';i:5;i:' . $reels['reel1'][1] . ';i:6;i:' . $reels['reel1'][0] . ';i:7;i:' . $rsym . ';}');
                                $rsym = $slotSettings->SymbolGame[rand(0, count($slotSettings->SymbolGame) - 1)];
                                $reelStr[1] .= ('i:8;i:' . $reels['reel2'][2] . ';i:9;i:' . $reels['reel2'][1] . ';i:10;i:' . $reels['reel2'][0] . ';i:11;i:' . $rsym . ';}');
                                $rsym = $slotSettings->SymbolGame[rand(0, count($slotSettings->SymbolGame) - 1)];
                                $reelStr[2] .= ('i:12;i:' . $reels['reel3'][2] . ';i:13;i:' . $reels['reel3'][1] . ';i:14;i:' . $reels['reel3'][0] . ';i:15;i:' . $rsym . ';}');
                                $rsym = $slotSettings->SymbolGame[rand(0, count($slotSettings->SymbolGame) - 1)];
                                $reelStr[3] .= ('i:16;i:' . $reels['reel4'][2] . ';i:17;i:' . $reels['reel4'][1] . ';i:18;i:' . $reels['reel4'][0] . ';i:19;i:' . $rsym . ';}');
                                $rsym = $slotSettings->SymbolGame[rand(0, count($slotSettings->SymbolGame) - 1)];
                                $reelStr[4] .= ('i:20;i:' . $reels['reel5'][2] . ';i:21;i:' . $reels['reel5'][1] . ';i:22;i:' . $reels['reel5'][0] . ';i:23;i:' . $rsym . ';}');
                                $wwCnt = 0;
                                $wwStr = '';
                                for( $ww = 0; $ww < $lines; $ww++ ) 
                                {
                                    if( $cWins[$ww] > 0 ) 
                                    {
                                        $wwCnt++;
                                        $wwStr .= ('i:' . ($ww + 1) . ';i:' . ($cWins[$ww] * 100) . ';');
                                    }
                                }
                                if( $scattersCount >= 3 ) 
                                {
                                    $wwCnt++;
                                    $wwStr .= ('i:0;i:' . ($scattersWin * 100) . ';');
                                }
                                $wins_ = 'a:' . $wwCnt . ':{' . $wwStr . '}';
                                $balanceInCents = sprintf('%01.2f', $slotSettings->GetBalance()) * 100;
                                if( $postData['slotEvent'] == 'freespin' || $winType == 'bonus' ) 
                                {
                                    $balanceInCents = $slotSettings->GetGameData($slotSettings->slotId . 'FreeBalance');
                                }
                                if( $postData['slotEvent'] == 'freespin' && $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') <= $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') ) 
                                {
                                    $slotSettings->SetGameData($slotSettings->slotId . 'FreeGames', 0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CurrentFreeGame', 0);
                                    $isFreeGame = 0;
                                    $actFreegame = 0;
                                    $balanceInCents = sprintf('%01.2f', $slotSettings->GetBalance()) * 100;
                                }
                                $symbolAnims = 'a:' . count($symbolAnimsArr) . ':{' . implode('', $symbolAnimsArr) . '}';
                                $teaserReelsArr = [
                                    's:1:"a";b:0;', 
                                    's:1:"b";b:0;', 
                                    's:1:"c";b:0;', 
                                    's:1:"d";b:0;', 
                                    's:1:"e";b:0;'
                                ];
                                $rid = [
                                    'a', 
                                    'b', 
                                    'c', 
                                    'd', 
                                    'e'
                                ];
                                for( $r = 1; $r <= 5; $r++ ) 
                                {
                                    for( $p = 0; $p <= 2; $p++ ) 
                                    {
                                        if( $reels['reel' . $r][$p] == $scatter ) 
                                        {
                                            $teaserReelsArr[$r - 1] = 's:1:"' . $rid[$r - 1] . '";a:2:{i:0;i:' . $scatter . ';i:1;i:' . ($p + 1) . ';}';
                                        }
                                    }
                                }
                                $teaserReels = 'a:5:{' . implode('', $teaserReelsArr) . '}';
                                $result_tmp[0] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:10:"spinresult";s:5:"value";a:49:{s:8:"winCoins";i:' . ($totalWin * 100) . ';s:8:"winLines";' . $winString . 's:13:"winLinesCount";i:' . $wwCnt . ';s:10:"winPerLine";' . $wins_ . 's:12:"winFGPerLine";b:0;s:13:"bonusWinLines";b:0;s:15:"bonusWinPerLine";b:0;s:11:"bonusSymbol";b:0;s:11:"fiveOfAKind";b:' . $fiveOfAKind . ';s:9:"freeGames";b:0;s:11:"actFreegame";b:0;s:12:"maxFreegames";b:0;s:11:"freegameWin";b:0;s:10:"isFreeGame";b:0;s:12:"wonFreegames";b:0;s:11:"playerCoins";i:' . $balanceInCents . ';s:9:"winFactor";d:' . ceil($totalWin / $allbet) . ';s:3:"x25";b:0;s:11:"symbolAnims";' . $symbolAnims . 's:9:"blockAnim";b:0;s:12:"blockAnimPos";b:0;s:15:"actWinFreegames";b:0;s:19:"freeGamesAnimations";b:0;s:13:"replaceSymbol";i:3;s:9:"multiData";b:0;s:10:"totalMulti";b:0;s:8:"maxiplay";b:0;s:18:"additionalWinAnims";b:0;s:10:"multiLines";b:0;s:15:"specSymbolAnims";b:0;s:16:"extraTeaserReels";b:0;s:11:"stickyReels";b:0;s:12:"newStickyPos";b:0;s:19:"playRandomRetrigger";b:0;s:19:"wildSymbolPositions";b:0;s:12:"bonusSymbols";b:0;s:13:"isBookFeature";b:0;s:12:"actFreeRound";i:0;s:13:"maxFreeRounds";i:0;s:12:"freeRoundWin";i:0;s:13:"amountSymbols";a:5:{s:1:"a";i:6;s:1:"b";i:9;s:1:"c";i:12;s:1:"d";i:15;s:1:"e";i:30;}s:11:"teaserReels";' . $teaserReels . 's:18:"specAttentionSpins";a:1:{s:1:"e";a:3:{i:2;b:0;i:0;b:0;i:0;b:0;}}s:26:"specFeatureSymbolPositions";' . $specFeatureSymbolPositions . 's:17:"reel_a_symboldata";' . $reelStr[0] . 's:17:"reel_b_symboldata";' . $reelStr[1] . 's:17:"reel_c_symboldata";' . $reelStr[2] . 's:17:"reel_d_symboldata";' . $reelStr[3] . 's:17:"reel_e_symboldata";' . $reelStr[4] . '}}}}}';
                                break;
                            case 'initGambleCard':
                                $gambleAmount = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                if( !$slotSettings->HasGameData($slotSettings->slotId . 'CardsHistory') ) 
                                {
                                    $hst = [
                                        's:4:"club";', 
                                        's:4:"club";', 
                                        's:4:"club";', 
                                        's:4:"club";', 
                                        's:4:"club";', 
                                        's:5:"heart";', 
                                        's:5:"heart";', 
                                        's:5:"heart";', 
                                        's:5:"heart";', 
                                        's:5:"heart";'
                                    ];
                                    shuffle($hst);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'CardsHistory', $hst);
                                }
                                $cHist = $slotSettings->GetGameData($slotSettings->slotId . 'CardsHistory');
                                $result_tmp[0] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:14:"initGambleCard";s:5:"value";a:4:{s:3:"bet";i:' . ($gambleAmount * 100) . ';s:12:"collectedWin";i:0;s:7:"history";a:5:{i:0;' . $cHist[0] . 'i:1;' . $cHist[1] . 'i:2;' . $cHist[2] . 'i:3;' . $cHist[3] . 'i:4;' . $cHist[4] . '}s:13:"pulseInterval";i:545;}}}}';
                                break;
                            case 'initGambleLadder':
                                $gambleAmount = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                $bet = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentBet');
                                $LadderStep = -1;
                                $LadderStep0 = 0;
                                $LadderStep1 = 0;
                                $ladder = $slotSettings->gambleLadder;
                                $ladderStr = '';
                                for( $i = 0; $i < count($ladder); $i++ ) 
                                {
                                    if( $ladder[$i] < 0 ) 
                                    {
                                        $ladderStr .= ('i:' . $i . ';b:0;');
                                    }
                                    else
                                    {
                                        $ladderStr .= ('i:' . $i . ';i:' . ($bet * $ladder[$i] * 100) . ';');
                                        if( $bet * $ladder[$i] < $gambleAmount && $ladder[$i] >= 0 ) 
                                        {
                                            $LadderStep = -1;
                                            $LadderStep1 = $i + 1;
                                        }
                                        else if( $gambleAmount == ($bet * $ladder[$i]) && $ladder[$i] >= 0 ) 
                                        {
                                            $LadderStep = $i;
                                        }
                                    }
                                }
                                if( $LadderStep <= 4 && $LadderStep >= 0 ) 
                                {
                                    $LadderStep0 = 0;
                                    $LadderStep1 = $LadderStep + 1;
                                }
                                else if( $LadderStep <= 10 && $LadderStep >= 0 ) 
                                {
                                    if( $LadderStep == 6 ) 
                                    {
                                        $LadderStep0 = 4;
                                        $LadderStep1 = $LadderStep + 1;
                                    }
                                    else
                                    {
                                        $LadderStep0 = 6;
                                        $LadderStep1 = $LadderStep + 1;
                                    }
                                }
                                else if( $LadderStep <= 14 && $LadderStep >= 0 ) 
                                {
                                    if( $LadderStep == 12 ) 
                                    {
                                        $LadderStep0 = 10;
                                        $LadderStep1 = $LadderStep + 1;
                                    }
                                    else
                                    {
                                        $LadderStep0 = 6;
                                        $LadderStep1 = $LadderStep + 1;
                                    }
                                }
                                if( $LadderStep1 <= 4 && $LadderStep < 0 ) 
                                {
                                    $LadderStep0 = $LadderStep1 - 1;
                                }
                                else if( $LadderStep1 <= 10 && $LadderStep < 0 ) 
                                {
                                    if( $LadderStep1 == 6 ) 
                                    {
                                        $LadderStep0 = 4;
                                    }
                                    else
                                    {
                                        $LadderStep0 = $LadderStep1 - 1;
                                    }
                                }
                                else if( $LadderStep1 <= 14 && $LadderStep < 0 ) 
                                {
                                    if( $LadderStep1 == 12 ) 
                                    {
                                        $LadderStep0 = 10;
                                    }
                                    else
                                    {
                                        $LadderStep0 = $LadderStep1 - 1;
                                    }
                                }
                                $slotSettings->SetGameData($slotSettings->slotId . 'LadderStep', $LadderStep);
                                $slotSettings->SetGameData($slotSettings->slotId . 'LadderStep0', $LadderStep0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'LadderStep1', $LadderStep1);
                                $slotSettings->SetGameData($slotSettings->slotId . 'СollectedWin', 0);
                                $result_tmp[0] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:16:"initGambleLadder";s:5:"value";a:6:{s:3:"bet";i:' . ($gambleAmount * 100) . ';s:13:"allStepValues";a:15:{' . $ladderStr . '}s:10:"multiplier";i:1;s:11:"currentStep";i:' . $LadderStep . ';s:7:"winStep";i:' . $LadderStep1 . ';s:8:"loseStep";i:' . $LadderStep0 . ';}}}}';
                                break;
                            case 'splitGambleWin':
                                $gambleAmount = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                $bet = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentBet');
                                $LadderStep = $slotSettings->GetGameData($slotSettings->slotId . 'LadderStep');
                                $LadderStep0 = $slotSettings->GetGameData($slotSettings->slotId . 'LadderStep0');
                                $LadderStep1 = $slotSettings->GetGameData($slotSettings->slotId . 'LadderStep1');
                                $isLadder = $postData['inLadderMode'];
                                if( $isLadder ) 
                                {
                                    $ladder = $slotSettings->gambleLadder;
                                    $LadderStep--;
                                    if( $LadderStep == 5 || $LadderStep == 11 ) 
                                    {
                                        $LadderStep--;
                                    }
                                    if( $LadderStep == 6 ) 
                                    {
                                        $LadderStep0 = 0;
                                        $LadderStep1 = 7;
                                    }
                                    else if( $LadderStep == 12 ) 
                                    {
                                        $LadderStep0 = 6;
                                        $LadderStep1 = 13;
                                    }
                                    else
                                    {
                                        $LadderStep0 = $LadderStep - 1;
                                        $LadderStep1 = $LadderStep + 1;
                                    }
                                    $splitWinTmp = $ladder[$LadderStep];
                                    $splitWin = $gambleAmount - ($bet * $splitWinTmp);
                                    $gambleAmount = $gambleAmount - $splitWin;
                                    $slotSettings->SetGameData($slotSettings->slotId . 'LadderStep', $LadderStep);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'LadderStep0', $LadderStep0);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'LadderStep1', $LadderStep1);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $gambleAmount);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'СollectedWin', $slotSettings->GetGameData($slotSettings->slotId . 'СollectedWin') + $splitWin);
                                    $result_tmp[0] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:14:"splitGambleWin";s:5:"value";a:6:{s:3:"bet";i:' . ($gambleAmount * 100) . ';s:12:"collectedWin";i:' . ($slotSettings->GetGameData($slotSettings->slotId . 'СollectedWin') * 100) . ';s:10:"multiplier";i:1;s:11:"currentStep";i:' . $LadderStep . ';s:7:"winStep";i:' . $LadderStep1 . ';s:8:"loseStep";i:' . $LadderStep0 . ';}}}}';
                                }
                                else
                                {
                                    if( $gambleAmount <= 0.01 ) 
                                    {
                                        exit();
                                    }
                                    $splitWin = sprintf('%01.2f', $gambleAmount / 2);
                                    $gambleAmount = sprintf('%01.2f', $gambleAmount - $splitWin);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $gambleAmount);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'СollectedWin', $slotSettings->GetGameData($slotSettings->slotId . 'СollectedWin') + $splitWin);
                                    $result_tmp[0] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:14:"splitGambleWin";s:5:"value";a:2:{s:3:"bet";i:' . ($gambleAmount * 100) . ';s:12:"collectedWin";i:' . ($slotSettings->GetGameData($slotSettings->slotId . 'СollectedWin') * 100) . ';}}}}';
                                }
                                break;
                            case 'takeGambleWin':
                                $gambleAmount = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                $result_tmp[0] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:13:"takeGambleWin";s:5:"value";a:2:{s:9:"gambleWin";i:' . ($gambleAmount * 100) . ';s:11:"playerCoins";i:' . $balanceInCents . ';}}}}';
                                break;
                            case 'gambleLadder':
                                $gambleAmount = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                $bet = $slotSettings->GetGameData($slotSettings->slotId . 'CurrentBet');
                                $LadderStep = $slotSettings->GetGameData($slotSettings->slotId . 'LadderStep');
                                $LadderStep0 = $slotSettings->GetGameData($slotSettings->slotId . 'LadderStep0');
                                $LadderStep1 = $slotSettings->GetGameData($slotSettings->slotId . 'LadderStep1');
                                $dbet = $gambleAmount;
                                if( $gambleAmount > 0 ) 
                                {
                                    $slotSettings->SetBalance(-1 * $gambleAmount);
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $gambleAmount);
                                }
                                else
                                {
                                    exit();
                                }
                                $doubleWin = rand(1, 2);
                                if( $slotSettings->MaxWin < ($gambleAmount * $slotSettings->CurrentDenom) ) 
                                {
                                    $doubleWin = 0;
                                }
                                $uCard = '';
                                $ladder = $slotSettings->gambleLadder;
                                $casBank = $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''));
                                $LadderStepTmp = $LadderStep1;
                                if( $LadderStepTmp == 5 || $LadderStepTmp == 11 ) 
                                {
                                    $LadderStepTmp += 2;
                                }
                                if( $casBank < ($ladder[$LadderStepTmp] * $bet) ) 
                                {
                                    $doubleWin = 0;
                                }
                                if( $doubleWin == 1 ) 
                                {
                                    $LadderStep = $LadderStepTmp;
                                    $LadderStep0 = $LadderStep - 1;
                                    $LadderStep1 = $LadderStep + 1;
                                }
                                else
                                {
                                    $LadderStep = $LadderStep0;
                                    if( $LadderStep == 6 ) 
                                    {
                                        $LadderStep0 = 0;
                                        $LadderStep1 = 7;
                                    }
                                    else if( $LadderStep == 12 ) 
                                    {
                                        $LadderStep0 = 6;
                                        $LadderStep1 = 13;
                                    }
                                    else
                                    {
                                        $LadderStep0 = $LadderStep - 1;
                                        $LadderStep1 = $LadderStep + 1;
                                    }
                                }
                                $gambleAmount = $ladder[$LadderStep] * $bet;
                                if( $gambleAmount > 0 ) 
                                {
                                    $slotSettings->SetBalance($gambleAmount);
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $gambleAmount);
                                }
                                else
                                {
                                    $gambleAmount = -1 * $gambleAmount;
                                }
                                $slotSettings->SetGameData($slotSettings->slotId . 'LadderStep', $LadderStep);
                                $slotSettings->SetGameData($slotSettings->slotId . 'LadderStep0', $LadderStep0);
                                $slotSettings->SetGameData($slotSettings->slotId . 'LadderStep1', $LadderStep1);
                                $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $gambleAmount);
                                $result_tmp[0] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:18:"gambleLadderResult";s:5:"value";a:9:{s:10:"multiplier";i:1;s:11:"currentStep";i:' . $LadderStep . ';s:7:"winStep";i:' . $LadderStep1 . ';s:8:"loseStep";i:' . $LadderStep0 . ';s:3:"bet";i:' . ($gambleAmount * 100) . ';s:12:"collectedWin";i:' . ($slotSettings->GetGameData($slotSettings->slotId . 'СollectedWin') * 100) . ';s:3:"win";b:0;s:14:"matchedDrawOut";a:2:{i:5;b:0;i:11;b:0;}s:9:"actGamble";i:0;}}}}';
                                $response_log = '{"responseEvent":"gambleResult","serverResponse":{"totalWin":' . $gambleAmount . '}}';
                                $slotSettings->SaveLogReport($response_log, $dbet, 1, $gambleAmount, 'slotGamble2');
                                break;
                            case 'gambleCard':
                                $gambleAmount = $slotSettings->GetGameData($slotSettings->slotId . 'TotalWin');
                                $dbet = $gambleAmount;
                                $gambleChoice = $postData['choice'];
                                $doubleWin = rand(1, 2);
                                if( $slotSettings->MaxWin < ($gambleAmount * $slotSettings->CurrentDenom) ) 
                                {
                                    $doubleWin = 0;
                                }
                                $uCard = '';
                                $casBank = $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''));
                                if( $casBank < ($gambleAmount * 2) ) 
                                {
                                    $doubleWin = 0;
                                }
                                if( $doubleWin == 1 ) 
                                {
                                    $gambleAmountStr = $gambleAmount * 2;
                                    if( $gambleChoice == 'red' ) 
                                    {
                                        $uCard = 's:5:"heart";';
                                    }
                                    else
                                    {
                                        $uCard = 's:4:"club";';
                                    }
                                }
                                else
                                {
                                    if( $gambleChoice == 'black' ) 
                                    {
                                        $uCard = 's:5:"heart";';
                                    }
                                    else
                                    {
                                        $uCard = 's:4:"club";';
                                    }
                                    $gambleAmountStr = 0;
                                    $gambleAmount = -1 * $gambleAmount;
                                }
                                $cHist = $slotSettings->GetGameData($slotSettings->slotId . 'CardsHistory');
                                array_pop($cHist);
                                array_unshift($cHist, $uCard);
                                $slotSettings->SetGameData($slotSettings->slotId . 'CardsHistory', $cHist);
                                if( $gambleAmount > 0 ) 
                                {
                                    $slotSettings->SetBalance($gambleAmount);
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * $gambleAmount);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', $gambleAmount * 2);
                                }
                                else
                                {
                                    $slotSettings->SetBalance($gambleAmount);
                                    $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $gambleAmount * -1);
                                    $slotSettings->SetGameData($slotSettings->slotId . 'TotalWin', 0);
                                }
                                $response_log = '{"responseEvent":"gambleResult","serverResponse":{"totalWin":' . $gambleAmount . '}}';
                                $slotSettings->SaveLogReport($response_log, $dbet, 1, $gambleAmount, 'slotGamble');
                                $result_tmp[0] = 'INFO$$1$$a:1:{s:7:"display";a:1:{i:0;a:2:{s:4:"type";s:16:"gambleCardResult";s:5:"value";a:6:{s:4:"card";' . $uCard . 's:3:"bet";i:' . ($gambleAmountStr * 100) . ';s:12:"collectedWin";i:0;s:9:"actGamble";i:1;s:7:"history";a:5:{i:0;' . $cHist[0] . 'i:1;' . $cHist[1] . 'i:2;' . $cHist[2] . 'i:3;' . $cHist[3] . 'i:4;' . $cHist[4] . '}s:10:"quitGamble";b:0;}}}}';
                                break;
                        }
                        if( !isset($result_tmp[0]) ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"","serverResponse":"Invalid request state"}';
                            exit( $response );
                        }
                        $response = $result_tmp[0];
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
