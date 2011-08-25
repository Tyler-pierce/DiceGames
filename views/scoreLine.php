<?php

/**
 *  Html for a single score line
 *  
 *  Params
 *  - scoreLeft
 *  - rule (the rule these scores apply to in this game)
 *  - scoreRight (if any)
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

?>

<span class="dc_text1">
    <div class="dc_scoreBg"><div id="dc_player_scores_<?php echo $playerNameLeft; ?>_<?php echo $i; ?>" class="dc_scorePadding dc_allScores">
        <?php echo $scoreLeft; ?>
    </div></div>
    
    <div class="dc_scoreBg dc_scoreRule"><div id="dc_rule_<?php echo $i; ?>" class="dc_scorePadding">
        <?php echo $rule; ?>
    </div></div>

    <div class="dc_scoreBg"><div id="dc_player_scores_<?php echo $playerNameRight; ?>_<?php echo $i; ?>" class="dc_scorePadding dc_allScores">
        <?php echo $scoreRight; ?>
    </div></div>
</span>
