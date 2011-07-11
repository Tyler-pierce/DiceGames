<?php

/**
 *  Public interface for PlayDice
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

interface PlayDiceInterface
{
    /* public interface */

    public function addPlayer ($name);

    public function removePlayer ($name);

    public function getGameLib ();
}