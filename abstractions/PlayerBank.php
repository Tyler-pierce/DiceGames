<?php

/**
 *  Controls a players assets (deposits and withdrawals)
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

class PlayerBank implements DiceBankInterface
{
    /**
     *  Sets to a memory store api
     */
    private $_memory = null;


    /**
     *  Constructor
     */
    function __construct ()
    {
        $this->_memory = new MemoryList();
    }

    /**
     *  @param string gameKey
     *  @param string player
     *  @param int amount
     *  @param mixed item
     */
    public function deposit ($gameKey, $player, $item)
    {
        switch ($item[0])
        {
            case 'credits' :
                return $this->_memory->setName("{$player}-credits")->insert('credits', $item[1]);

            case 'debits' :
                return $this->_memory->setName("{$player}-credits")->insert('debits', $item[1]);

            case 'item' :
                return $this->_memory->setName("{$player}-prize")->insert($item[1], 1);
        }

        return false;
    }

    /**
     *  @param string gameKey
     *  @param string player
     *  @param int amount
     *  @return true if succeeded (as a positive value key from the deposit insert) or false if failed
     */
    public function withdraw ($gameKey, $player, $item)
    {
        if ($item[0] == 'credits' && is_numeric($item[1]))
        {
            $totalInBank = $this->_getAccountBalance($player);

            if ($item[1] <= $totalInBank)
            {
                return $this->deposit($gameKey, $player, array('debits', $item[1]));
            }
        }

        return false;
    }

    /**
     *  Retrieve total account balance, 0 if no activity
     *  
     *  @param string player
     */
    private function _getAccountBalance ($player)
    {
        $balance = 0;

        $transactionHistory = $this->_memory->setName("{$player}-credits")->query();

        foreach ($transactionHistory as $transaction)
        {
            if ($transaction['KEY'] == 'credits')
            {
                $balance += $transaction['VAL'];
            }
            else
            {
                $balance -= $transaction['VAL'];
            }
        }

        return $balance;
    }
}