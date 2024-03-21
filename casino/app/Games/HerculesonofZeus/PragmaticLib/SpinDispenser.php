<?php

namespace VanguardLTE\Games\HerculesonofZeus\PragmaticLib;

class SpinDispenser
{
    public static function getSpin($slotArea, $index, $counter, $bet, $lines, $doubleChance, $reelSet, $win, $currentLog, $user, $freeSpins){
        // if there is no log, then the usual spin, Spin state
         // if there is no win and no respin in the previous rotation - then the usual spin, Spin state
         // if there is no win, but there is a respin in the previous spin - then LastRespin
         // if there is a win, but there is no respin in the previous spin - then FirstRespin
         // if there is a win, and there is a respin or FirstRespin - then Respin
         // if free games are dropped - then FirstFreeSpin
         // if free games are added then AddFreeSpin
         // if there are free games then FreeSpin
    }
}
