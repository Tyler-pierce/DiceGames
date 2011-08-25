<?php

/**
 *  An interface defining how saving is handled.  Either a dice game is not
 *  persistant, or it implements this interface and can be persistant across rounds
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

interface DiceGamesSaveInterface
{
    /// PUBLIC METHODS
    public function getGame ($gameKey, $maxRounds);

    public function startGame($gameKey);

    public function startRound ($gameKey, $maxRounds);

    public function saveRound ($gameKey, $winningPlayer = false);

    public function saveResult ($gameKey, $msg, $winningPlayer);
}