<?php

/**
 *  Dice on a Yacht! 
 *  
 *  Games that can be played with 5 8-sided dice.
 *  
 *
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

class GamesDice implements GamesDiceInterface {

    /**
     *  An array of games and the rules that apply to them.  The order of rules matter.
     *  Rules will be evaluated in sequence until false is returned in which case score 
     *  returned is 0.
     */
    protected $games = array(
        'ones'         =>   array(
                                // Rule name and then its arguments (not including dice)
                                array('dupesOnly', 1),
                            ),
        'twos'         =>   array(
                                array('dupesOnly', 2),
                            ),
        'threes'       =>   array(
                                array('dupesOnly', 3),
                            ),
        'fours'        =>   array(
                                array('dupesOnly', 4),
                            ),
        'fives'        =>   array(
                                array('dupesOnly', 5),
                            ),
        'sixes'        =>   array(
                                array('dupesOnly', 6),
                            ),
        'sevens'       =>   array(
                                array('dupesOnly', 7),
                            ),
        'eights'       =>   array(
                                array('dupesOnly', 8),
                            ),
        'allSame'      =>   array(
                                array('uniqueRolls', 1),
                                array('hardScore',   50),
                            ),
        'allDifferent' =>   array(
                                array('uniqueRolls', 5),
                                array('hardScore',   40),
                            ),
        'threeOfAKind' =>   array(
                                array('xOfAKind', 3, false),
                                array('sum'),
                            ),
        'fourOfAKind'  =>   array(
                                array('xOfAKind', 4, false),
                                array('sum'),
                            ),
        'smallStraight'=>   array(
                                array('sequence',  4),
                                array('hardScore', 30),
                            ),
        'largeStraight'=>   array(
                                array('sequence',  5),
                                array('hardScore', 40),
                            ),
        'chance'       =>   array(
                                array('sum'),
                            ),
        'fullHouse'    =>   array(
                                array('uniqueRolls', 2),
                                array('xOfAKind',  3, true),
                                array('xOfAKind',  2, true),
                                array('hardScore', 25),
                            ),
    );


    /** 
     *  Constructor
     */
    function __construct () {
    }

    /**
     *  Magic method __call
     *  If no public method exists, object defaults to calling here with name of method call
     *  and arguments.
     *  
     *  @param string name
     *  @param array args
     *  @return mixed result, usually a score
     */
    public function __call ($name, $args) {

        return $this->playGame($name, reset($args));
    }   

    /// PUBLIC INTERFACE

    /**
     *  Find the rules for a game and then play it through.
     *  
     *  @param string gameName
     *  @return integer score
     */
    public function playGame ($gameName, $diceRolls) {

        if (!array_key_exists($gameName, $this->games)) {

            throw new Exception ('Game does not exist');
        }

        $rules  = $this->games[$gameName];
        // will set to false when a rule evaluates to no chance of having any score
        $result = true;
    
        // go through each rule
        foreach ($rules as $rule) {

            $ruleFunc = array_shift($rule);
  
            $method = array($this, '_' . $ruleFunc);
            
            if ($result !== false) {

                $rule[] = $diceRolls;

                $result = call_user_func_array($method, $rule);
            } else {
                // if the rules weren't completed successfully they scored 0
                return 0;
            }
        }

        if (!is_numeric($result)) {
        
            throw new Exception ('Last game rule did not return score [' . $result . '].');
        }

        return $result;
    }

    /**
     *  Play every game and return the game(s) that resulted in the highest score.
     *  
     *  @param array diceRolls
     *  @return string name of best game or array of tied best game strings
     */
    public function getSuggestion ($diceRolls) {
        
        // sets to a hash of scores keyed by game name
        $scores = array();
        
        foreach ($this->games as $gameName => $rules) {

            $scores[$gameName] = $this->playGame($gameName, $diceRolls);
        }

        arsort($scores);

        $highestScore = 0;

        $scoresArray = array();

        foreach ($scores as $gameName => $score) {
        
            if ($score >= $highestScore) {
            
                $scoresArray[] = $gameName;

                $highestScore = $score;
            } else {
                break;
            }
        }

        return (count($scoresArray) == 1 ? $scoresArray[0] : $scoresArray);
    }

    
    /// PRIVATE METHODS

    /**
     *  Sum of all rolls.
     *  
     *  @param array diceRolls
     *  @return integer score
     */
    private function _sum ($diceRolls) {

        return array_sum($diceRolls);
    }

    /**
     *  Scores the value of the roll if it's a duplicate of the desired value
     *  
     *  @param int x
     *  @param array diceRolls
     *  @return integer score
     */
    private function _dupesOnly ($dupe, $diceRolls) {
    
        $score = 0;

        foreach ($diceRolls as $roll) {

            $score += ($roll == $dupe ? $roll : 0);
        }

        return $score;
    }

    /**
     *  Returns true if there are x of the same any 1 value in 
     *  the dice rolls.
     *  Use exact true if constaint exists that 4 of a kind doesn't equal 3 of a kind.
     *  
     *  @param int x
     *  @param boolean exact
     *  @param array diceRolls

     *  @return true or false
     */
    private function _xOfAKind ($x, $exact, $diceRolls) {

        sort($diceRolls);

        // if we flip the sorted roll array we'll know what index the last of the current roll
        // is at.
        $rollKeys = array_flip($diceRolls);
        
        // if there are more than 3 keys after the flip no point in working harder
        // (array flip doesnt keep dupe keys so its as saying there are 4+ unique rolls
        if (count($rollKeys) <= $x) { 
            
            $i = 0;
            
            // note the flipped roll of the key, noting that it is the key in context to $diceRolls
            foreach ($rollKeys as $roll => $rollKey) {
            
                $copiesOfRoll = ($rollKey + 1) - $i;
                // if the index of last value of roll is more than x away, we have enough of them.
                // (think of this loop as iterating through diceRolls, skipping vals on the way).
                if ($copiesOfRoll >= $x) {
                    
                    if (!$exact || $copiesOfRoll == $x) {
                        return true;
                    }
                }
                    
                $i += $copiesOfRoll; // will be at least 1
            }
        }

        return false;
    }

    /**
     *  Checks for x amount of unique rolls among the 5 die
     *  
     *  @param int x
     *  @param array diceRolls
     */
    private function _uniqueRolls ($x, $diceRolls) {
    
        $uniqueRolls = count(array_unique($diceRolls));

        return ($uniqueRolls == $x ? true : false);
    }

    /**
     *  Simply return the score given.  Dicerolls is passes for uniformity and clarity for now.
     *  
     *  @param int score
     *  @param array diceRolls
     *  @return integer score
     */
    private function _hardScore ($score, $diceRolls) {
    
        return $score;
    }

    /**
     *  Return true if there is a sequence of length x in the dice rolls.
     *  
     *  @param int x
     *  @param array diceRolls
     *  @return true or false
     */
    private function _sequence ($x, $diceRolls) {

        $uniqueRolls = array_unique($diceRolls);
        
        $result = -1;
        
        // no point continuing if there arent enough uniques to make a sequence
        if (count($uniqueRolls) >= $x) {
            
            sort($uniqueRolls);
            
            $i = 0;
            $y = $x - 1; // the difference between the first and last roll in the sequence
            
            // iterate until end of array is reached at sequence length
            while (isset($uniqueRolls[($i + $y)])) {
            
                // this works because its known the array is unique and in order
                if (($uniqueRolls[$i + $y] - $uniqueRolls[$i]) == $y) {
                    return true;
                }
                
                ++$i;
            }
        }

        return false;
    }
}

