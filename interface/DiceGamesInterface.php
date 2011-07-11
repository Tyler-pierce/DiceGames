<?php

/**
 *  Public interface for gamesDice
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

interface DiceGamesInterface
{
    /// PUBLIC METHODS
    public function playGame ($gameName, $diceRolls);

    public function getSuggestion ($diceRolls);

     public function getGames ();
}