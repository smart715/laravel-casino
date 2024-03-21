<?php 
namespace VanguardLTE\Games\SuperBallKenoIB
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
                    $jData = json_decode(trim(file_get_contents('php://input')), true);
                    $balanceInCents = round(sprintf('%01.2f', $slotSettings->GetBalance()) * 100);
                    $result_tmp = [];
                    $aid = '';
                    if( $postData['command'] == 'get' ) 
                    {
                        $gameData = json_decode(trim(file_get_contents('php://input')), true);
                        $lines = 1;
                        $betline = $gameData['bet'];
                        $line = $gameData['line'];
                        if( isset($gameData['dkf']) ) 
                        {
                            $dkf = $gameData['dkf'] / 100;
                        }
                        else
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid denomination"}';
                            exit( $response );
                        }
                        if( $lines <= 0 || $betline <= 0.0001 || $dkf <= 0.01 || $line < 2 || $line > 10 ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid bet state"}';
                            exit( $response );
                        }
                        if( $slotSettings->GetBalance() < ($lines * $betline * $dkf) ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid balance"}';
                            exit( $response );
                        }
                        if( $slotSettings->GetGameData($slotSettings->slotId . 'FreeGames') < $slotSettings->GetGameData($slotSettings->slotId . 'CurrentFreeGame') && $postData['bet']['bonus'] == 'true' ) 
                        {
                            $response = '{"responseEvent":"error","responseType":"' . $postData['command'] . '","serverResponse":"invalid bonus state"}';
                            exit( $response );
                        }
                    }
                    $aid = (string)$postData['command'];
                    switch( $aid ) 
                    {
                        case 'init':
                            $gameBets = [];
                            foreach( $slotSettings->Bet as $b ) 
                            {
                                if( $b >= 0.1 ) 
                                {
                                    $gameBets[] = $b;
                                }
                            }
                            $dkf = $jData['dkf'];
                            if( $dkf > 500 || $dkf < 1 ) 
                            {
                                $dkf = 100;
                            }
                            $slotSettings->CurrentDenom = $dkf / 100;
                            $slotSettings->SetGameData('SuperBallKenoIBCurrentDenom', $slotSettings->CurrentDenom);
                            $balanceInCents = round(sprintf('%01.2f', $slotSettings->GetBalance()) * 100);
                            $result_tmp[0] = '{"result":{"spin":null,"message":[],"free_spin":null,"extra":{"total_win":0,"subgame":0,"spin_id":null,"line":1,"game":"k_dpk","double_step":0,"dkf":1,"dealer_card":null,"bet":1},"currency":"' . $slotSettings->slotCurrency . '","credits":' . ceil($balanceInCents / 100) . ',"bets":null,"balance":' . $balanceInCents . '},"error":null,"code":200}';
                            break;
                        case 'GetBalance':
                            $balanceInCents = round(sprintf('%01.2f', $slotSettings->GetBalance()) * 100);
                            $result_tmp[] = '{"Status":{"ErrCode":0,"InitialErrCode":0,"ErrType":0,"UniqueErrorCode":0,"Balance":' . $balanceInCents . ',"RoundsLeft":0}}';
                            break;
                        case 'get':
                            $dkf = $jData['dkf'];
                            if( $dkf > 500 || $dkf < 1 ) 
                            {
                                $dkf = 100;
                            }
                            $slotSettings->CurrentDenom = $dkf / 100;
                            $slotSettings->SetGameData('SuperBallKenoIBCurrentDenom', $slotSettings->CurrentDenom);
                            $lines = 10;
                            $betline = $gameData['bet'];
                            $line = $gameData['line'];
                            $allbet = $betline * $lines;
                            $postData['slotEvent'] = 'bet';
                            $balanceInCents = round(sprintf('%01.2f', $slotSettings->GetBalance()) * 100);
                            if( $postData['slotEvent'] != 'freespin' ) 
                            {
                                if( !isset($postData['slotEvent']) ) 
                                {
                                    $postData['slotEvent'] = 'bet';
                                }
                                $slotSettings->SetBalance(-1 * $allbet, $postData['slotEvent']);
                                $bankSum = $allbet / 100 * $slotSettings->GetPercent();
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), $bankSum, $postData['slotEvent']);
                                $slotSettings->UpdateJackpots($allbet);
                            }
                            $balanceInCents = round(sprintf('%01.2f', $slotSettings->GetBalance()) * 100);
                            $bank = $slotSettings->GetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''));
                            $balls = [];
                            for( $b = 0; $b < 80; $b++ ) 
                            {
                                $balls[] = $b + 1;
                            }
                            shuffle($balls);
                            $ballSelected = [];
                            for( $lb = 0; $lb < $line; $lb++ ) 
                            {
                                $ballSelected[] = $balls[$lb];
                            }
                            for( $i = 0; $i <= 5000; $i++ ) 
                            {
                                $totalWin = 0;
                                shuffle($balls);
                                $matchNumbers = [];
                                $drawnNumbers = [];
                                $drawnNumbersStr = [];
                                $magicNumbers = [];
                                $magicNumbersStr = [];
                                $winMultiplier = 0;
                                for( $a = 0; $a < 20; $a++ ) 
                                {
                                    $drawnNumbers[] = $balls[$a];
                                }
                                shuffle($balls);
                                $magicNumbers[0] = $balls[0];
                                $magicNumbers[1] = $balls[1];
                                $magicNumbers[2] = $balls[2];
                                for( $b = 0; $b < count($ballSelected); $b++ ) 
                                {
                                    $curBall = $ballSelected[$b];
                                    if( in_array($curBall, $drawnNumbers) ) 
                                    {
                                        $matchNumbers[] = $curBall;
                                    }
                                }
                                $curPays = $slotSettings->Paytable[count($ballSelected)];
                                $winMultiplier = $curPays[count($matchNumbers)];
                                $totalWin = $betline * $curPays[count($matchNumbers)];
                                $magicMplArr = [1];
                                $magicMpl = 0;
                                $magicWin = 0;
                                $magicMatchCount = 0;
                                for( $i = 0; $i < count($magicNumbers); $i++ ) 
                                {
                                    if( in_array($magicNumbers[$i], $drawnNumbers) ) 
                                    {
                                        $magicMatchCount++;
                                    }
                                }
                                if( $magicMatchCount >= 3 ) 
                                {
                                    $magicMpl = $magicMplArr[0];
                                    $magicWin = $magicMpl * $betline;
                                }
                                if( $totalWin + $magicWin <= $bank ) 
                                {
                                    break;
                                }
                            }
                            $IsWin = 'false';
                            if( $totalWin > 0 ) 
                            {
                                $slotSettings->SetBank((isset($postData['slotEvent']) ? $postData['slotEvent'] : ''), -1 * ($totalWin + $magicWin));
                                $slotSettings->SetBalance($totalWin + $magicWin);
                                $IsWin = 'true';
                            }
                            $reportWin = $totalWin + $magicWin;
                            $response = '{"responseEvent":"spin","responseType":"' . $postData['slotEvent'] . '","serverResponse":{"slotLines":' . $lines . ',"slotBet":' . $betline . ',"totalFreeGames":0,"currentFreeGames":0,"Balance":' . $slotSettings->GetBalance() . ',"afterBalance":' . $slotSettings->GetBalance() . ',"bonusWin":0,"totalWin":' . ($totalWin + $magicWin) . ',"winLines":[],"Jackpots":[],"reelsSymbols":[]}}';
                            $slotSettings->SaveLogReport($response, $allbet, $lines, $reportWin, $postData['slotEvent']);
                            $winstring = '';
                            $slotSettings->SetGameData('SuperBallKenoIBTotalWin', $totalWin);
                            $slotSettings->SetGameData('SuperBallKenoIBGambleStep', 5);
                            $hist = $slotSettings->GetGameData('SuperBallKenoIBCards');
                            $numStr = [];
                            for( $i = 0; $i < count($drawnNumbers); $i++ ) 
                            {
                                if( in_array($drawnNumbers[$i], $matchNumbers) ) 
                                {
                                    $numStr[] = '"' . $drawnNumbers[$i] . '":true';
                                }
                                else
                                {
                                    $numStr[] = '"' . $drawnNumbers[$i] . '":false';
                                }
                            }
                            for( $i = 0; $i < count($magicNumbers); $i++ ) 
                            {
                                if( in_array($magicNumbers[$i], $drawnNumbers) ) 
                                {
                                    $magicNumbersStr[] = '"' . $magicNumbers[$i] . '":true';
                                }
                                else
                                {
                                    $magicNumbersStr[] = '"' . $magicNumbers[$i] . '":false';
                                }
                            }
                            $result_tmp[0] = '{"result":{"spin":{"win_scatters":[],"win_other":[],"win_lines":[],"win":' . $totalWin . ',"total_win":' . ($totalWin + $magicWin) . ',"respin":null,"reels_add":null,"reels":[' . count($matchNumbers) . ',1],"hotswap":null,"freegames":null,"extra":{},"e":"","bonus3":null,"bonus2":null,"bonus1":null},"message":[],"free_spin":null,"extra":{"total_win":0,"subgame":0,"spin_id":51288850063,"line":' . $line . ',"game":"k_dpk","double_step":0,"dkf":100,"dealer_card":null,"bet":1},"currency":"' . $slotSettings->slotCurrency . '","credits":' . ceil($balanceInCents / 100) . ',"bets":null,"balance":' . $balanceInCents . '},"error":null,"code":200}';
                            break;
                        case 'take':
                            $gameData = json_decode(trim(file_get_contents('php://input')), true);
                            $lines = 10;
                            $betline = $gameData['bet'];
                            $line = $gameData['line'];
                            $dkf = $jData['dkf'];
                            if( $dkf > 500 || $dkf < 1 ) 
                            {
                                $dkf = 100;
                            }
                            $slotSettings->CurrentDenom = $dkf / 100;
                            $slotSettings->SetGameData('SuperBallKenoIBCurrentDenom', $slotSettings->CurrentDenom);
                            $balanceInCents = round(sprintf('%01.2f', $slotSettings->GetBalance()) * 100);
                            $result_tmp[0] = '{"result":{"spin":null,"message":[],"free_spin":null,"extra":{"total_win":0,"subgame":0,"spin_id":null,"line":' . $line . ',"game":"k_dpk","double_step":0,"dkf":100,"dealer_card":null,"bet":1},"currency":"' . $slotSettings->slotCurrency . '","credits":' . ceil($balanceInCents / 100) . ',"bets":null,"balance":' . $balanceInCents . '},"error":null,"code":200}';
                            break;
                    }
                    $response = $result_tmp[0];
                    $slotSettings->SaveGameData();
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
