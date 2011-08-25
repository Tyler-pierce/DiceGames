<?php

/**
 *  Starts a new dice game.  If playRound is passed a round of dice will be played
 *  if a game has been started already.
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

/*
 * Seed and check data
 */
$playRound = (isset($_REQUEST['playRound']) ? (bool) $_REQUEST['playRound'] : false);

$data = array(
    'result' => false,
);


/*
 * Bootstrap dice
 */
chdir('../');

require_once 'config/boot_diceTest.php';

$game = new XRandomRules();


/*
 * Run Logic and print results in json
 */
if ($playRound)
{
    list($players, $results, $msg, $extra) = $game->playRound();

    $data['result']    = (bool) count($results);
    $data['msg']       = $msg;
    $data['round']     = $results;
    $data['players']   = $players;
    $data['roundOver'] = ($extra ? true : false);

    if ($extra && is_array($extra))
    {
        $data['msgs'] = $extra;
    }
}
else
{
    // check / start game
    list($result, $rules) = $game->beginRounds();
    
    $data['result'] = $result;
    
    if ($data['result'])
    {
        $data['rules'] = $rules;
    }
    else
    {
        $data['log'] = $game->getGameLog();
    }
}

echo json_encode($data);

