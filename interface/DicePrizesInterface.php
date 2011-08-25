<?php

/**
 *  Prize awarding/getting interface.  Methods are passed the game key (unique id for
 *  a game match) and the player so that any situation based prizing can be done.
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

interface DicePrizesInterface
{
    /// PUBLIC METHODS
    // cost to play a game
    public function getPlayCost ($gameKey, $player);

    // mini prize for a round
    public function getRoundPrize ($gameKey, $player);

    // grand prize for the game (race to x wins finished)
    public function getGamePrize ($gameKey, $player);
}

