// @author T Pierce <tyler.pierce@gmail.com>
//
// Dice Control Central.  Manages calls to the server for game actions and
// modifies the client to show output.
// @see http://jquery.com

/* Game Control */

function startGame () {

    $.ajax({
        url: 'ajax/playRound.php',
        dataType: 'json',
        cache: false,
        async: true,
        success: function (data){
            
            if (data.result) {
                
                addOutputItem('Hit Roll to Play!');
                showRollButtons();
                hideStartButton();
                showRules(data.rules);
                zeroScores();
            } else {
            
                addOutputItem(data.log);
            }
        }
    });
}

function playRound () {

    $.ajax({
        url: 'ajax/playRound.php',
        data: {playRound: true},
        dataType: 'json',
        cache: false,
        async: true,
        success: function (data){
        
            if (data.result) {            
                showPlayerOutput(data.round, data.players);
                addOutputItem(data.msg);
                
                if (data.roundOver) {
                
                    hideRollButtons();
                    showStartButton();
                    
                    if (data.msgs) {
                        for (i in data.msgs) {
                            addDelayedOutputItem(data.msgs[i], (i + 1) * 2000);
                        }
                    }
                }
            } else {
                addOutputItem(data.msg);
            }
        }
    });
}


/* UI */

function showRollButtons () {

    $('.dc_button_roll').fadeIn();
}

function hideRollButtons () {

    $('.dc_button_roll').fadeOut();
}

function hideStartButton () {

    $('.dc_button_newRound').fadeOut('fast');
}

function showStartButton () {

    $('.dc_button_newRound').fadeIn('fast');
}

function addDelayedOutputItem (text, delay) {

    setTimeout(function(){addOutputItem(text)}, delay);
}

function addOutputItem (text) {

    outputList = $('#dc_output_list');
    
    outputList.prepend('<li>' + text + '</li>');
    
    if (outputList.children('li').length > 3) {
    
        outputList.children('li').last().remove();
    }
    
    outputList.children('li').first().css('background-color', '#E6C55A');
    outputList.children('li:not(:first-child)').css('background-color', '#fff');
    
    $('.dc_results_output').fadeIn();
}

function updateRolls (parentElement, rolls) {

    $(parentElement + ' .dc_player_roll').each(function (index) {

        $(this).text(rolls[index]);
    });
}

function zeroScores () {

    $('.dc_allScores').text('0');
}

function updateScores (parentElement, scores) {
    
    for (var i = scores.length - 1 ; i >= 0 ; --i) {
    
        $(parentElement + '_' + i).text(scores[i]);
    }
}

function showPlayerOutput (roundAction, players) {

    for (i in roundAction) {
    
        playerName = roundAction[i].name;
        
        updateRolls('#player_rolls_' + playerName, players[playerName].current.roll);
        updateScores('#dc_player_scores_' + playerName, players[playerName].current.rounds);
    }
}

function showRules (rules) {

    for (i in rules) {
    
        $('#dc_rule_' + i).text(rules[i].desc);
    }
}