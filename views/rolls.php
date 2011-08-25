<?php

/**
 *  The players rolls
 */

?>

<div id="player_rolls_<?php echo $playerName; ?>" class="dc_scoreBg dc_player_rolls dc_text1">
    <?php foreach ($rolls as $roll) { echo '<div class="dc_player_roll">' . $roll . '</div>'; } ?>
</div>