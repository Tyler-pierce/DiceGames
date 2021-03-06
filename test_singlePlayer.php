<?php

/**
 *  Test script for Dice, Simple single player play
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

require_once 'config/boot_diceGames.php';


function getRoll ()
{
    $dice = array();

    for ($i = 0 ; $i < 5 ; ++$i)
    {
        $dice[] = rand(1, 8);
    }

    return $dice;
}

function testGame ($testname, $score, $rolls)
{
    echo '<b>' . $testname . ':</b> Score is ' . (is_array($score) ? print_r($score) : $score) . ' for these rolls ' . print_r($rolls, true) . '<br /><br />';
}


$games = new DiceGames();



// Test simple game
$roll = getRoll();

$score = $games->ones($roll);

testGame('ones', $score, $roll);

