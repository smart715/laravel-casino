<?php

namespace VanguardLTE\Games\HerculesonofZeus\PragmaticLib;

class CheckRtp
{
  private $rtp;
  private $game;

  public function __construct($rtp, $game){
    // load rtp from config
    $this->rtp = $rtp;
    $this->game = $game;
  }

  public function checkRtp($bet, $win){
    // calculate current rtp with in and out of money and save to games table
    self::formatRtp($this->game);
    $currentRTP = $this->game->rtp_stat_out > 0 && $this->game->rtp_stat_in > 0 ? $this->game->rtp_stat_out / $this->game->rtp_stat_in * 100 : 0;
    $this->game->current_rtp = $currentRTP;
    $this->game->save();
    var_dump('currentRtp='.$currentRTP.' rtp='.$this->rtp.' bet='.$bet.' win='.$win);
    // when the rtp must go down and the win is greater than bet/2
    // and when the rtp must go up and the win is less than bet*2
    // we don't allow to pass through
    if($this->rtp > $currentRTP && $bet * 0.5 > $win || $this->rtp < $currentRTP && $bet * 2 < $win)
      return false;
    return true;
  }

  private function formatRtp(&$game){
    if($game->rtp_stat_in > 8500){
      $game->rtp_stat_in = 500;
      $game->rtp_stat_out = 370;
    }
  }
}

?>