<?php

/**
 *  Bank interface for players to be awarded goods and currencies and pay for round play fees
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

interface DiceBankInterface
{
    /// PUBLIC METHODS

    public function deposit ($gameKey, $player, $item);

    public function withdraw ($gameKey, $player, $amount);
}

