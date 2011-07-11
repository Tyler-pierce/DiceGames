<?php

/**
 *  Dice Player Class, intent is to use the dice games/rules delegate to play
 *  a real round of adversarial dice, tracking the points and outcome.  Main
 *  task of class is to maintain and manipulate the _players data structure.
 *  
 *  @author T Pierce
 */

abstract class PlayDice implements PlayDiceInterface
{
    /**
     *  Constructed to an object implementing DiceGamesInterface (dice rules/games)
     */
    private $_games = null;

    /**
     *  Builds to be a list of all players and their current status (score, rounds etc..)
     */
    private $_players = array();

    /**
     *  Sets to an instance of Dice (and must implement the dice interface)
     */
    private $_dice = null;

    /**
     *  Sets to the current game rule set by name.  Must set to play
     */
    private $_gameName = false;


    /**
     *  Constructor
     */
    function __construct ($dice)
    {
        if ($dice instanceof DiceInterface)
        {
            $this->_games = new DiceGames();

            $this->_dice = $dice;
        }
        else
        {
            throw new Exception ('Dice does not implement dice interface');
        }
    }

    /** Sets and Gets **/

    public function getGameLib () { return $this->_games; }
    public function getDice () { return $this->_dice; }
    public function getPlayers () { return $this->_players; }
    
    public function setGameName ($gameName) { if (is_string($gameName)) $this->_gameName = $gameName; return $this; }


    /**
     *  Add a fresh player to the list of contestants who will be participating in each game.
     *  Keeps some basic stats.
     *  
     *  @param string name
     *  @return instace if added throws exception if problem
     */
    public function addPlayer ($name)
    {
        if (is_string($name) && !isset($this->_players[$name]))
        {
            $this->_players[$name] = array(
                /* static player data */
                'name'      => $name,
                /* real time data ('this' game, changes each game) */
                'current'   => array(
                                'roll'  => array(),
                                'score' => 0,
                               ),
                /* meant for persistant statistics, can be reset after round over */
                'stats'     => array(
                                'rounds' => 0,
                                'won'    => 0,
                               ),
            );

            return $this;
        }

        throw new Exception ('Player was added twice or name was not string value.');
    }

    /**
     *  Remove a player from the game.
     *  
     *  @param string name
     *  @return true if a player was removed or false otherwise
     */
    public function removePlayer ($name)
    {
        if (isset($this->_players[$name]))
        {
            unset($this->_players[$name]);

            return true;
        }

        return false;
    }

    /**
     *  Add a score to a player given the dice roll and game score.
     *  
     *  @param int score
     *  @param array roll
     *  @return true on success or false if unsuccessful (wrong name)
     */
    public function playerTurns ()
    {
        array_walk($this->_players, array($this, '_playTurn'));
    }

    /**
     *  Uses the current scores to determine a winner.
     */
    public function calculateRound ()
    {
    }

    /**
     *  Play through a turn for a single passed in player, altering their score
     */
    private function _playTurn (&$player)
    {
        if ($this->_gameName)
        {
            $roll = $this->_dice->rollAll();

            $score = $this->_games->playGame($this->_gameName, $roll);

            $player['current']['roll']  = $roll;
            $player['current']['score'] = $score;

            return array($player['name'] => $score);
        }
        else
        {
            throw new Exception ('No Game Name set. setGameName in PlayDice to play round.');
        }
    }

    /**
     *  Play a round of dice. Extension of this class will use this method
     *  to determine rules of the game using the available dice games and methods
     *  and play it all out, returning the updated player array.
     *  
     *  @return array of player data
     */
    abstract public function playRound ();
}
