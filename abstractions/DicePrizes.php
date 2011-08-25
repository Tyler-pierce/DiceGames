<?php

/**
 *  Controls dice game prize winnings.
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

class DicePrizes implements DicePrizesInterface
{
    /**
     *  Constructor
     */
    function __construct ()
    {
    }

    /**
     *  @param string gameKey
     *  @param string player
     */
    public function getPlayCost ($gameKey, $player)
    {
        return array('credits', 1000);
    }

    /**
     *  @param string gameKey
     *  @param string player
     */
    public function getRoundPrize ($gameKey, $player)
    {
        return array('credits', 100);
    }

    /**
     *  @param string gameKey
     *  @param string player
     */
    public function getGamePrize ($gameKey, $player)
    {
        return array('item', 'Banana');
    }

    /**
     *  Formats prize items in human readable text
     *  
     *  @param array item
     *  @return string
     */
    public function getPrizeDescription ($item)
    {
        switch ($item[0])
        {
            case 'credits' :
                return $item[1] . ' credits';
                break;

            case 'item' :
                return 'a <strong>' . $item[1] . '</strong>';
        }

        return '';
    }
}