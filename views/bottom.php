<?php

/**
 *  Html for the output and main button control group
 *  
 *  - Outputs actions as they occur in the game
 *  - Shows buttons for starting a new round
 *  
 *  @author T Pierce <tyler.pierce@gmail.com>
 */

?>

<div class="dc_results"><div class="dc_results_inside">
    <div class="dc_results_output"><div class="dc_results_output_inside">
        <ul id="dc_output_list">
        </ul>
    </div></div>
    
    <div class="dc_button_newRound dc_button dc_text1" onclick=""><a href="#" onclick="startGame(); return false;">
        <div class="dc_scorePadding">START ROUND!</div>
    </a></div>
</div></div>
