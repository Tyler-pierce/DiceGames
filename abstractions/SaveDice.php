<?php

/**
 *  Save interface that relies soley on memcached.  Saves each game as a stream of events.
 *  
 *  Keys important events under users stream so it can be queried easily, possibly by friends.
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

class SaveDice implements DiceGamesSaveInterface
{
    /**
     *  Constructs to a memory handling structure.
     */
    private $_memory = null;

    /**
     *  Constructs to the maximum rounds in a save game
     */
    private $_maxRounds = 0;

    /**
     *  Will set to a number indicating the game started, this makes
     *  the actions of each game unique and indexable through the amount
     *  of times game started has occured
     */
    private $_gameKeyPostfix = '0';


    /**
     *  Constructor
     *  
     *  Sets memory client using MemoryList
     *  @see https://github.com/Tyler-pierce/MemoryList
     */
    function __construct ($maxRounds = 5)
    {
        $this->_memory = new MemoryList();

        $this->_maxRounds = $maxRounds;
    }

    /**
     *  Checks to see if a game is set by running quick query on it.
     *  
     *  @param string gameKey
     *  @return true if game is set or false otherwise.
     */
    public function getGame ($gameKey, $maxRounds)
    {
        $result = $this->startRound($gameKey, $maxRounds);

        return !$result[0];
    }

    /**
     *  Start a game by inserting the appropriate information.
     *  
     *  @param string gameKey
     *  @return true if game started successfully or false otherwise
     */
    public function startGame($gameKey)
    {
        return $this->_memory->setName($gameKey)->insert('started game');
    }

    /**
     *  Does the work necessary to start a round, and returns false if we have
     *  hit the maximum rounds.  The work includes aggregating wins and checking
     *  them against max rounds.
     *  
     *  @param string gameKey
     *  @param array players
     *  @param int maxRounds
     */
    public function startRound ($gameKey, $maxRounds)
    {
        $roundsPlayed = array();

        $gameActions = $this->_memory->setName($gameKey)->reverse(true)->query();

        $playerStats = array();
        
        $gameOver = true;

        foreach ($gameActions as $action)
        {
            if (strpos($action['KEY'], 'win') !== false)
            {
                list($__result, $playerName) = explode('-', $action['KEY'], 2);

                $playerStats[$playerName]['wins'] = (isset($playerStats[$playerName]['wins']) ? $playerStats[$playerName]['wins'] + $action['VAL'] : $action['VAL']);
            }
            else if (strpos($action['KEY'], 'started game') !== false)
            {
                $gameOver = false;
                break;
            }
        }

        foreach ($playerStats as $playerName => $player)
        {
            if ($player['wins'] >= $maxRounds)
            {
                $gameOver = true;
            }
        }

        return array($gameOver, $playerStats);
    }

    /**
     *  Save a round just completed by tallying a win for the winning player. Pass false for
     *  the player to indicate a tie.
     *  
     *  @param string gameKey
     *  @param mixed winningPlayer
     */
    public function saveRound ($gameKey, $winningPlayer = false)
    {
        if ($winningPlayer)
        {
            return $this->_memory->setName($gameKey)->insert("win-{$winningPlayer}");
        }
        else
        {
            return $this->_memory->setName($gameKey)->insert('tie');
        }
    }

    /**
     *  Save a message indicating what the final result was.
     *  
     *  @param string gameKey
     *  @param string winningPlayer
     *  @param string msg
     */
    public function saveResult ($gameKey, $msg, $winningPlayer)
    {
        return $this->_memory->setName($winningPlayer)->insert($msg);
    }

    /**
     *  Save the rules of the game being used for this round.
     *  
     *  @param string gameKey
     *  @param array rules
     *  @return true if ok false if failed
     */
    public function saveRules ($gameKey, $rules)
    {
        foreach ($rules as $ruleName)
        {
            // add as a rule to memory under this game key
            $this->_memory->setName("{$gameKey}-rule")->insert($ruleName);
        }

        return true;
    }

    /**
     *  Retrieve rules from memory for the current round
     *  
     *  @param string gameKey
     */
    public function getRules ($gameKey, $amountRules)
    {
        $result = $this->_memory->setName("{$gameKey}-rule")->limit($amountRules)->query();

        $rules = array();

        foreach ($result as $rule)
        {
            $rules[] = $rule['KEY'];
        }

        return $rules;
    }

    public function getAll ($gameKey)
    {
        return $this->_memory->setName($gameKey)->query();
    }
}

