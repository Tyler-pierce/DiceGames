<?php

/**
 *  Dice game controller
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

class DiceController extends Controller
{
    /**
     *  Show the playing interface
     */
    function showGame ()
    {
        /// Score Lines
        $scoreLines = $this->_getScoreLines();


        /// Players
        list($playerLeft, $playerRight,
            $playerLeftRolls, $playerRightRolls,
            $playerLeftRollBut, $playerRightRollBut) = $this->_getPlayerViews();


        /// Players part of page
        $topArray = array(
            'playerLeft'         => $playerLeft,
            'scores'             => $scoreLines,
            'playerRight'        => $playerRight,
            'playerLeftRolls'    => $playerLeftRolls,
            'playerRightRolls'   => $playerRightRolls,
            'playerLeftRollBut'  => $playerLeftRollBut,
            'playerRightRollBut' => $playerRightRollBut,
        );

        $top = $this->loadView('top', $topArray, true);


        /// Results and Main Play Controls
        $results = $this->loadView('bottom', array(), true);

        $content = array('content' => $top . $results);

        $this->loadView('html', $content);
    }

    /**
     *  Retrieve the game rule lines and the scores for each game for 2
     *  players.
     *  
     *  @return html formated string
     */
    private function _getScoreLines ()
    {
        $scores1 = array(
            'scoreLeft'  => 0,
            'scoreRight' => 0,
            'playerNameLeft'  => 525065020,
            'playerNameRight' => 676205437,
            'rule'       => 'Start new',
            'i'          => 0,
        );

        $scores2 = array(
            'scoreLeft'  => 0,
            'scoreRight' => 0,
            'playerNameLeft'  => 525065020,
            'playerNameRight' => 676205437,
            'rule'       => 'round to',
            'i'          => 1,
        );

        $scores3 = array(
            'scoreLeft'  => 0,
            'scoreRight' => 0,
            'playerNameLeft'  => 525065020,
            'playerNameRight' => 676205437,
            'rule'       => 'begin!',
            'i'          => 2,
        );

        return $this->loadView('scoreLine', $scores1, true)
            .   $this->loadView('scoreLine', $scores2, true)
            .   $this->loadView('scoreLine', $scores3, true);
    }

    /**
     *  Retrieve the player pictures/names and rolls for 2 players.
     *  
     *  @return html formated string
     */
    private function _getPlayerViews ()
    {
        $player1 = array(
            'side'  => 'left',
            'image' => 'http://graph.facebook.com/buggs.construction/picture?type=normal',
        );
        
        $player2 = array(
            'side'  => 'right',
            'image' => 'http://graph.facebook.com/676205437/picture?type=normal',
        );

        $player1Rolls = array(
            'playerName' => '525065020',
            'rolls'      => array(1, 1, 1, 1, 1),
        );

        $player2Rolls = array(
            'playerName' => '676205437',
            'rolls'      => array(1, 1, 1, 1, 1),
        );

        $player1RollButton = array(
            'playerName' => '525065020',
        );

        $player2RollButton = array(
            'playerName' => '676205437',
        );

        return array(
            $this->loadView('player', $player1, true),
            $this->loadView('player', $player2, true),
            $this->loadView('rolls', $player1Rolls, true),
            $this->loadView('rolls', $player2Rolls, true),
            $this->loadView('roll_button', $player1RollButton, true),
            '', // no roll button here, adversary (left player is playing player)
        );
    }
}