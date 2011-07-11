<?php

/**
 *  Public interface for Dice
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

interface DiceInterface
{
    /// PUBLIC METHODS
    public function setAmountDice ($amount);

    public function roll ();

    public function rollAll ();
}