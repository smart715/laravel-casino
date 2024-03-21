<?php

namespace VanguardLTE\Games\HerculesonofZeus\PragmaticLib;

class Log
{
    private $gameId,$userId, $log;

    public function __construct($gameId, $userId){
        $this->gameId = $gameId;
        $this->userId = $userId;
        $history = \VanguardLTE\GameLog::where(['game_id' => $this->gameId, 'user_id' => $this->userId])->orderBy('id', 'desc')->first('str');
        if( isset($history['str']) )
        {
            $this->log = json_decode($history['str'], true);
        }
        else
        {
            $this->log = false;
        }
    }
    public function getLog(){
        return $this->log;
    }

    public static function setLog($log, $gameId, $userId, $shopId){
        \VanguardLTE\GameLog::create([
            'game_id' => $gameId,
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'str' => json_encode($log),
            'shop_id' => $shopId
        ]);
    }

    public static function changeLog($gameId, $userId, $ms){
        $lastLog = \VanguardLTE\GameLog::where(['game_id' => $gameId, 'user_id' => $userId])->orderBy('id', 'desc')->first();
        $history = json_decode($lastLog->str, true);
        $history['ms'] = $ms;
        $history = json_encode($history);
        $lastLog->str = $history;
        $lastLog->save();
    }

    public static function setCollected($gameId, $userId, $isCollected){
        $lastLog = \VanguardLTE\GameLog::where(['game_id' => $gameId, 'user_id' => $userId])->orderBy('id', 'desc')->first();
        $history = json_decode($lastLog->str, true);
        $history['isCollected'] = $isCollected;
        $history = json_encode($history);
        $lastLog->str = $history;
        $lastLog->save();
    }
}
