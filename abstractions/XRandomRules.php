<?php

/**
 *  X random rules is a dice game played by 2 or more players (or 1 for testing/foolin),
 *  X number of rules are chosen from the dice games library of rules, and it is scored by the sum
 *  of your totals from all X rules.
 *  
 *  @author T Pierce <tyler.pierce@gmail.com> 
 */

class XRandomRules extends GamePlayControl
{
    /**
     *  Can set to different numbers of rules to use
     */
    private $_x = 3;


    /**
     *  Constructor
     */
    public function __construct ()
    {
        $dice = new Dice();

        $dice->setSides(6)->setAmountDice(5);

        // test players obviously hardcoded for example
        $this->addPlayer('525065020', true)->addPlayer('676205437');

        // set up saving feature (note that we are using chdir as kind of a hack for the settings.
        // we are not using a good directory/project structure for this test.
        chdir('../memcache/');

        $this->save = new SaveDice();

        chdir('../DiceGames/');

        $this->prizes = new DicePrizes();
        $this->bank   = new PlayerBank();

        parent::__construct($dice);

        $this->maxRounds = 3;
    }

    /**
     *  Return game name
     */
    public function getGameName () { return 'Threezies'; }


    public function setX ($x) { $this->_x = (int) $x; return $this; }

    /**
     *  Play a round of 3 random rules.
     *  @see parent::playRound
     */
    public function playRound ()
    {
        $games = $this->getGameLib()->getGames();

        $amountGames = count($games);

        $msg = false;

        $games = $this->_getGames();
        
        $save = true;
        

        foreach ($games as $gameName)
        {
            $result = $this->setGameName($gameName)->playerTurns(true, $save);

            $save = false;

            // exit out if tried to play an extra round
            if (!$result)
            {
                return array(array(), array(), 'The game has ended.', false);
            }
        }

        list($results, $extra) = $this->endRound();


        foreach ($this->getPlayers() as $playerName => $player)
        {
            if (strpos($player['current']['log'], 'won round') !== false)
            {
                $msg = "{$playerName} won the round with " . $player['current']['score'] . " points!";

                $msgParts = explode('|', $player['current']['log']);

                if (isset($msgParts[1]))
                {
                    $msg .= " {$playerName} " . $msgParts[1];
                }
                
                break;
            }
        }


        return array($this->getPlayers(), $results, ($msg ? $msg : "The round ended in a tie!"), ($extra ? array($extra) : false));
    }

    /**
     *  Override for when a round is begun, a xRand specific entry need be added
     *  for showing what 3 games will be played for the duration of all rounds.
     */
    public function beginRounds ()
    {
        list($gameUnderway, $gameStarted) = parent::beginRounds();
        
        $gameMap = $this->getGameLib()->getGames();

        $gamesInfo = array();

        if ($gameStarted)
        {
            $games = array_reverse($this->_chooseGames());
        }
        else
        {
            $games = $this->getSave()->getRules($this->getGameKey(), $this->_x);
        }

        foreach ($games as $game)
        {
            $gamesInfo[] = (object) array('rule' => $game, 'desc' => $gameMap[$game]['description']); 
        }
        
        return  array($gameUnderway, $gamesInfo);
    }

    /**
     *  Selects x amount of games from the full list of games available in the 
     *  dice games lib.
     *  
     *  @return array of game names
     */
    private function _chooseGames ()
    {
        $rules = array_rand($this->getGameLib()->getGames(), $this->_x);

        $this->getSave()->saveRules($this->getGameKey(), $rules);

        return $rules;
    }

    /**
     *  Retrieve the rules that were saved previously
     *  
     *  @return array of rules
     */
    private function _getGames()
    {
        $rules = $this->getSave()->getRules($this->getGameKey(), $this->_x);

        return $rules;
    }
}

