<?php

namespace VanguardLTE\Games\GatesofOlympus\PragmaticLib;

class SpinDispenser
{
    public static function getSpin($slotArea, $index, $counter, $bet, $lines, $doubleChance, $reelSet, $win, $currentLog, $user, $freeSpins){
        // если нет лога - то обычный спин, состояние Spin
        // если нет выигрыша и нет респина в предыдущем вращении - то обычный спин, состояние Spin
        // если нет выигрыша, но есть респин в предыдущем вращении - то LastRespin
        // если есть выигрыш, но нет респина в предыдущем вращении - то FirstRespin
        // если есть выигрыш, и есть респин или FirstRespin - то Respin
        // если бесплатные игры выпали - то FirstFreeSpin
        // если бесплатные игры добавились то AddFreeSpin
        // если идут бесплатные игры то FreeSpin
    }
}
