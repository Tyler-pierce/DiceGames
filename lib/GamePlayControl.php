<?php

/**
 *  Dice Player Class, intent is to use the dice games/rules delegate to play
 *  a real round of adversarial dice, tracking the points and outcome.  Main
 *  task of class is to maintain and manipulate the _players data structure.
 *  
 *  @author T Pierce
 */

abstract class GamePlayControl
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
     *  Tracks whether a turn has been played, and resets when the round is calculated
     */
    private $_isTurnsPlayed = false;

    /**
     *  Populates with any results that are filled out at the end of a round
     */
    private $_currentResults = array();

    /**
     *  Sets to the gamekey so it doesnt have to be recalculated every time. The gamekey
     *  is this games unique identifier
     */
    private $_gameKey = false;

    private $_gameLog = '';


    /**
     *  Sets to true if saving/prizes/ has been activated.  These should be set by abstractions
     *  constructor so that $this constructor can verify interface, else will not be activated
     */
    protected $save   = null;
    protected $prizes = null;
    protected $bank   = null;

    // these should only be set true if saving or prizes respectively is known to be active
    private $_save   = false;
    private $_prizes = false;

    /**
     *  Can be set to have multiple rounds for a game type
     */
    protected $maxRounds = 1;


    /**
     *  Constructor
     *  
     *  @param Dice dice
     *  @param Object save (Instance of DiceGamesSaveInterface)
     */
    function __construct (Dice $dice)
    {
        if ($dice instanceof DiceInterface)
        {
            // init the dic
            $this->_games = new DiceGames();

            $this->_dice = $dice;

            // check persistance library for interface
            if (is_object($this->save))
            {
                if (!$this->save instanceof DiceGamesSaveInterface)
                {
                    throw new Exception ('Dice save does not have the required interface to serve PlayDice.');
                }

                $this->_save = true;
            }

            // check prizes/bank for interface
            if (is_object($this->prizes) && is_object($this->bank))
            {
                if (!$this->prizes instanceof DicePrizesInterface || !$this->bank instanceof DiceBankInterface)
                {
                    throw new Exception ('Dice Prizes or Bank do not conform to necessary interface.');
                }

                $this->_prizes = true;
            }
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
    public function getSave () { return $this->save; }
    public function getGameLog () { return $this->_gameLog; }

    public function setGameName ($gameName) { if (is_string($gameName)) $this->_gameName = $gameName; return $this; }

    /**
     *  Starts a new round if all parameters are good.  If save is set will start
     *  a persistant session to indicate game is going on.
     */
    public function beginRounds ()
    {
        $gameUnderway = false;
        $gameStarted  = false;

        if ($this->_save && count($this->_players) > 1)
        {
            $game = $this->save->getGame($this->getGameKey(), $this->maxRounds);

            $result = true;

            // it will pick up where the save left off, or go in here and start a new game
            if (!$game)
            {
                if ($this->_prizes)
                {
                    $activePlayer = $this->_getActivePlayer('name');
                    $cost = $this->prizes->getPlayCost($this->getGameKey(), $activePlayer);

                    $result = $this->bank->withdraw($this->getGameKey(), $activePlayer, $cost);
                }
                
                if ($result)
                {
                    // if additional players are added afterward they will not be saved
                    // so the players should already be added by now.
                    $this->save->startGame($this->getGameKey());

                    $gameStarted = true;
                }
                else
                {
                    $this->_gameLog = 'Player ' . $activePlayer . ' did not have enough funds.';
                }
            }
            
            $gameUnderway = ($result ? true : false);
        }
        else if (count($this->_players) > 1)
        {
            $gameUnderway = true;
        }

        return array($gameUnderway, $gameStarted);
    }

    /**
     *  Form the game key that will hold a unique value (based on the fact that
     *  a game cannot be had by 2 of the same player at once).
     */
    public function getGameKey ()
    {
        // playerName0-playerName1-playerName2-...
        // Make sure playerNames are unique in some manner
        return ($this->_gameKey ? $this->_gameKey : md5('8-' . implode('-', array_keys($this->_players))));
    }

    /**
     *  Add a fresh player to the list of contestants who will be participating in each game.
     *  Keeps some basic stats.
     *  
     *  @param string name
     *  @return instace if added throws exception if problem
     */
    public function addPlayer ($name, $active = false)
    {
        if (is_string($name) && !isset($this->_players[$name]))
        {
            $this->_players[$name] = array(
                /* static player data */
                'name'      => $name,
                'active'    => $active, // set to true if they are the controlling player of the round
                /* real time data ('this' game, changes each game) */
                'current'   => array(
                                'roll'   => array(),
                                'score'  => 0,
                                'rounds' => array(),
                                'log'    => '', // string or number representing result of last round
                               ),
                /* meant for persistant statistics, can be reset after round over */
                'stats'     => array(
                                'rounds' => 0,
                                'wins'   => 0,
                                'ties'   => 0,
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
     *  Sets save on with the passed interface.
     *  
     *  @param object save
     *  @return isntance of PlayDice
     */
    public function save ($saveInterface)
    {
        if ($this->save instanceof DiceSaveInterface)
        {
            $this->save = $save;
        }
        
        return $this;
    }

    /**
     *  Play through each players turn with their rolls for this round.
     *  
     *  Can be told explicitely not to save.  This could be for practice rounds,
     *  or because you want to call this multiple times for one round.
     *  
     *  @param int score
     *  @param array roll
     *  @return true on if round was played false if game is over so wasn't played
     */
    public function playerTurns ($reuseRoll = false, $save = true)
    {
        // a new round is started, current results are emptied to be repopulated when round declared over
        $this->_currentResults = array();
        
        $gameOver = false;

        if ($this->_save && $save)
        {
            list($gameOver, $playerStats) = $this->save->startRound($this->getGameKey(), $this->maxRounds);

            foreach ($playerStats as $playerName => $player)
            {
                $this->_players[$playerName]['stats']['wins'] = $player['wins'];
            }
        }
        
        if ($gameOver)
        {
            return false;
        }

        array_walk($this->_players, array($this, '_playTurn'), $reuseRoll);

        $this->_isTurnsPlayed = true;

        return true;
    }

    /**
     *  End the round for all players, calculating places and whatnot
     *  
     *  @return array of results ordered by best to worst score
     */
    public function endRound ()
    {
        array_walk($this->_players, array($this, '_calculateTurn'));

        $this->_updateTotals();

        $roundOver = false;
        
        $extraMsg = false;

        if ($this->_save)
        {
            foreach ($this->_players as $playerName => $player)
            {
                if (strpos($player['current']['log'], 'won round') !== false)
                {
                    $this->save->saveRound($this->getGameKey(), $playerName);

                    if ($player['stats']['wins'] >= $this->maxRounds)
                    {
                        $msg = "{$playerName} won a game of " . (is_string($this->getGameName()) ? $this->getGameName() : '[???]');

                        if ($this->_prizes)
                        {
                            $item = $this->prizes->getGamePrize($this->getGameKey(), $playerName);
                            
                            $this->bank->deposit($this->getGameKey(), $playerName, $item);
                            
                            $desc = $this->prizes->getPrizeDescription($item);
                            
                            $msg .= " and got {$desc}";
                        }
                        
                        $msg .= '!';

                        $roundOver     = true;
                        $winningPlayer = $playerName;
                    }

                    break;
                }
            }

            if ($this->_save && $roundOver)
            {
                $this->save->saveResult($this->getGameKey(), $msg, $winningPlayer);

                $extraMsg = $msg;
            }
        }

        return array($this->_currentResults, $extraMsg);
    }

    /**
     *  Play through a turn for a single passed in player, altering their score
     *  
     *  @param array player
     *  @param string playerName
     *  @param boolean resuseRoll
     *  @return array /2 player name and score
     */
    private function _playTurn (&$player, $playerName, $reuseRoll = false)
    {
        if ($this->_gameName)
        {
            $roll = ($reuseRoll && count($player['current']['roll']) > 0 ? $player['current']['roll'] : $this->_dice->rollAll());

            $score = $this->_games->playGame($this->_gameName, $roll);

            $player['current']['roll']   = $roll;
            // unless the score was asked to reset by calling calculate, it accumulates
            $player['current']['score'] += $score;
            $player['current']['rounds'][] = $score;

            return array($playerName, $score);
        }
        else
        {
            throw new Exception ('No Game Name set. setGameName in PlayDice to play round.');
        }
    }

    /**
     *  Reset a players values and populate a list of results ordered by top score (winner in position [0]
     *  with a tie element set if tied with someone)
     *  
     *  @param array player
     *  @param sting playerName
     *  @param array results
     */
    private function _calculateTurn (&$player, $playerName)
    {
        if ($this->_isTurnsPlayed)
        {
            if (isset($this->_currentResults[0]))
            {
                $newResults = array();
                $count      = count($this->_currentResults);
                $placed     = 0;
                
                // count 1 extra for the new entry that will be added
                for ($i = 0 ; $i + $placed <= $count ; ++$i)
                {
                    if (!isset($this->_currentResults[$i]))
                    {
                        $newResults[$i] = array('score' => $player['current']['score'], 'name' => $playerName);
                        break;
                    }
                    if (!$placed && $player['current']['score'] > $this->_currentResults[$i]['score'])
                    {
                        $newResults[$i] = array('score' => $player['current']['score'], 'name' => $playerName);
                        $placed         = 1;
                    }
                    else if (!$placed && $player['current']['score'] == $this->_currentResults[$i]['score'])
                    {
                        $newResults[$i]     = array('score' => $player['current']['score'], 'name' => $playerName, 'tie' => true);
                        $this->_currentResults[$i]['tie'] = true;
                        $placed             = 1;
                    }
                    else if (isset($this->_currentResults[$i - 1]) && $this->_currentResults[$i]['score'] == $this->_currentResults[$i - 1]['score'])
                    {
                        $results[$i]['tie'] = true;
                    }
                    
                    $newResults[$i + $placed] = $this->_currentResults[$i];
                }

                $this->_currentResults = $newResults;
            }
            else
            {
                $this->_currentResults[] = array('score' => $player['current']['score'], 'name' => $playerName);
            }
        }
        else
        {
            throw new Exception ('Turns were not played. Call playerTurns at least once to calculate a new round.');
        }
    }

    /**
     *  Update the players round total based on the results array from
     *  end round.
     */
    private function _updateTotals ()
    {
        $i = 0;

        foreach ($this->_currentResults as $result)
        {
            if ($i == 0 && isset($result['tie']) && $result['tie'])
            {
                ++$this->_players[$result['name']]['stats']['ties'];
            }
            else if ($i == 0)
            {
                ++$this->_players[$result['name']]['stats']['wins'];
                ++$i; // only incrememnt if winner found so we gather up all the ties first
                $this->_players[$result['name']]['current']['log'] = 'won round';

                if ($this->_prizes)
                {
                    $item = $this->prizes->getRoundPrize($this->getGameKey(), $result['name']);

                    $this->bank->deposit($this->getGameKey(), $result['name'], $item);

                    $this->_players[$result['name']]['current']['log'] .= '|wins ' . $this->prizes->getPrizeDescription($item) . '!';
                }
            }

            ++$this->_players[$result['name']]['stats']['rounds'];
        }
    }

    /**
     *  It may be useful to clear the player totals in case multiple rounds are run
     *  in the lifetime of a script
     */
    public function _clearPlayers ()
    {
        foreach (array_keys($this->_players) as $playerName)
        {
            $this->_clearPlayer($playerName);
        }
    }

    /**
     *  Clear a player of their current round stats.
     *  
     *  @param string playerName
     *  @return true if cleared, throw exception if there is a problem
     */
    private function _clearPlayer ($playerName)
    {
        if (isset($this->_players[$playerName]))
        {
            $this->_players[$playerName]['current']['score']  = 0;
            $this->_players[$playerName]['current']['roll']   = array();
            $this->_players[$playerName]['current']['rounds'] = array();
            
            return true;
        }
       
        throw new Exception ('Player does not exist [' . $playerName . ']. Could not clear player.');
    }

    /**
     *  Return the active player (think of it as the player behind the monitor) while the code
     *  is being executed.
     */
    private function _getActivePlayer ($field = false)
    {
        foreach ($this->_players as $player)
        {
            if ($player['active'])
            {
                return ($field ? $player[$field] : $player);
            }
        }

        return ($field ? $this->_players[0][$field] : $this->_players[0]);
    }

    /**
     *  Play a round of dice. Extension of this class will use this method
     *  to determine rules of the game using the available dice games and methods
     *  and play it all out, returning the updated player array.
     *  
     *  @return array of player data
     */
    abstract public function playRound ();

    /**
     *  Abstraction must provide a method to retrive the game name
     *  
     *  @return string representing game name
     */
    abstract public function getGameName ();
}
