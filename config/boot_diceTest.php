<?php

/**
 *  Boot a test complete with persistance and a game set up.
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

require_once 'config/boot_diceGames.php';


chdir('../memcache/');

require_once 'config/boot_memoryList.php';

chdir('../DiceGames/');


require_once 'abstractions/XRandomRules.php';

require_once 'abstractions/SaveDice.php';

require_once 'abstractions/DicePrizes.php';

require_once 'abstractions/PlayerBank.php';