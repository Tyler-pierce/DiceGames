<?php

/**
 *  X random rules is a dice game played by 2 or more players (or 1 for testing/foolin),
 *  X number of rules are chosen from the dice games library of rules, and it is scored by the sum
 *  of your totals from all X rules.
 *  
 *  @author T Pierce <tyler.pierce@gmail.com> 
 */

class XRandomRules extends PlayDice
{
    /**
     *  Can set to different numbers of rules to use
     */
    private $_x = 3;


    public function setX ($x) { $this->_x = (int) $x; return $this; }

    /**
     *  Play a round of 3 random rules.
     */
    public function playRound ()
    {
        $games = $this->getGameLib()->getGames();

        $amountGames = count($games);

        $games = $this->_chooseGames();
        
        foreach ($games as $gameName)
        {
            $result = $this->setGameName($gameName)->playerTurns();
        }

        return $this->getPlayers();
    }

    /**
     *  Selects x amount of games from the full list of games available in the 
     *  dice games lib.
     *  
     *  @return array of game names
     */
    private function _chooseGames ()
    {
        return array_rand($this->getGameLib()->getGames(), $this->_x);
    }
}

