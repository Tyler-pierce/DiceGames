<?php

/**
 *  Test script for Dice, trying a round using the adversarial libraries
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

require_once 'config/boot_diceGames.php';

require_once 'abstractions/XRandomRules.php';


$dice = new Dice();

$dice->setSides(6)->setAmountDice(5);

// Playing a round of XRandomRules (default 3 random rules picked) 
$games = new XRandomRules($dice);

print_r($games->addPlayer('Harvey')->addPlayer('PussyGalore')->playRound());
echo 'end test';

