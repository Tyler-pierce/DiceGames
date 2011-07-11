<?php

/**
 *  Dice Class,
 *  Implements a die, the die can be 6 sided, 8 sided... any amount of values 4 or more (any less
 *  and its either an object that doesn't make sense, or a coin).
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

class Dice implements DiceInterface
{
    /**
     *  Sets to amount of dice in the set
     */
    private $_amountDice = 1;

    /**
     *  Sets to the amount of sides on the die, never less than 4.
     */
    private $_sides = 4;


    /**
     *  Constructor
     */
    public function __construct (){}

    /**
     *  Set the amount of dice in play and return an instance of this class.
     *  
     *  @param int numDice
     *  @return instance of Dice if successful
     */
    public function setAmountDice ($amount)
    {
        if ((int) $amount > 0)
        {
            $this->_amountDice = $amount; 
        }
        else
        {
            throw new Exception ('Amount of dice must be integer greater than 0.');
        }

        return $this;
    }

    /**
     *  Set the amount of sides to the dies.
     *  
     *  @param int sides
     *  @return instance of Dice if successful
     */
    public function setSides ($amountSides)
    {
        if ((int) $amountSides > 3)
        {
            $this->_sides = $amountSides;
        }
        else
        {
            throw new Exception ('Amount of sides on a die must be integer 4 or more.');
        }

        return $this;
    }

    /**
     *  Roll a single die  
     *  
     *  @return integer value of roll
     */
    public function roll ()
    {
        return mt_rand(1, $this->_sides);
    }

    /**
     *  Roll all dice in the set
     *  
     *  @return array of roll values
     */
    public function rollAll ()
    {
        $rolls = array();

        for ($i = 0 ; $i < $this->_amountDice ; ++$i)
        {
            $rolls[] = $this->roll();
        }

        return $rolls;
    }
}