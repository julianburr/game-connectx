<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
    <link rel="canonical" href="<?php echo $core->getCanonicalURL(); ?>">
    <title>Connect X</title>
    
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.11.2.min.js"></script>
    <script type="text/javascript" src="<?php echo $core->getBaseURL(); ?>files/js/main.js"></script>
    
    <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Nothing+You+Could+Do|The+Girl+Next+Door|Give+You+Glory' type='text/css'>
	<link rel='stylesheet' href='<?php echo $core->getBaseURL(); ?>files/css/main.css' type='text/css'>
</head>
<body lang="en" class="default game" data-game-id="<?php echo $core->game->getID(); ?>" data-last-action="<?php echo $core->game->getLastAction()->getID(); ?>" data-baseurl="<?php echo $core->getBaseURL(); ?>">
    <header id="head">
        <h1>Connect X</h1>
        <h2 class="subtitle">Play it like the real children</h2>
        <div class="player">
            <div class="picture"></div>
            <div class="name">
                <p>Hello <b><?php echo $core->session->me->getName(); ?></b>, how're you t'day?</p>
            </div>
    </header>
    
    <div id="panel">
    <div id="panel_inner">
    <div id="panel_inner_load">
    
        <div class="border">
            <h3>Game #<?php echo $core->game->getID(); ?><span class="small status"><?php echo $core->game->getStatus(); ?></span></h3>
        </div>
        
        <ul class="playerlist">
        <?php
        
        foreach($core->game->getPlayers() as $player){
            $class = "player";
            if($core->isMe($player)){
                $class .= " player_me";
            }
            if($core->game->getCurrentPlayerID() == $player->getID()){
                $class .= " player_current";
            }
            echo "<li class='{$class}'><div class='border'>";
            echo "{$player->getName()}";
            if($core->isMe($player)){
                echo " <span class='small status'>That's you, mate!</span>";
            } else {
                echo " <span class='small status'>{$player->getStatus()}</span>";
            }
            echo " <span class='score'>{$core->game->getScore($player)}</span>";
            echo "</div></li>";
        }
        
        ?>
        </ul>
        
        <div class="border">
            <div class="action_buttons">
            <?php
                
                $status = $core->game->getStatus();
                $base = $core->getCanonicalURL();
                if(strpos($base, "?") === false){
                    $base .= "?";
                }
                switch($status){
                    case "waiting":
                    case "stopped":
                        if($core->game->checkPlayer($core->session->me)){
                            echo "<a class='button action action_leaveGame' href='{$base}&do[]=leaveGame'>Leave Game</a>";
                            if($core->game->getPlayerCount() > 1){
                                echo "<a class='button action action_startGame' href='{$base}&do[]=startGame'>Start Game</a>";
                            }
                        } else {
                            echo "<a class='button action action_enterGame' href='{$base}&do[]=enterGame'>Enter Game</a>";
                        }
                        break;
                    case "running":
                        if($core->game->checkPlayer($core->session->me)){
                            echo "<a class='button action action_stopGame' href='{$base}&do[]=stopGame'>Stop Game</a>";
                        }
                        break;
                    case "won":
                        if($core->game->checkPlayer($core->session->me)){
                            echo "<a class='button action action_startGame' href='{$base}&do[]=startGame'>Continue Game</a>";
                        }
                        break;
                    default:
                        break;
                }
                
            ?>
            </div>
        </div>
    
    </div>
    </div>
    </div>
    
    <div id="field">
    <div id="field_inner">
    <div id="field_inner_load">
    
        <?php
        
        $field = $core->game->getFieldSet();
        foreach($field as $x => $inner){
            echo "<ul>";
            for($y=count($inner)-1; $y>=0; $y--){
                echo "<li data-field-x='{$x}' data-field-y='{$y}'>";
                if($field[$x][$y]->getPlayer()->getID()){
                    $id = $field[$x][$y]->getPlayer()->getID();
                    if($core->game->isWon() && in_array(array($x, $y), $core->game->getWinnerFields())){
                        echo "<span class='stone stone_player stone_player_{$id} stone_winner stone_winner_{$id}'>{$id}</span>";
                    } else {
                        echo "<span class='stone stone_player stone_player_{$id}'>{$id}</span>";
                    }
                } else {
                    if($core->isMe($core->game->getCurrentPlayer()) && !$core->game->isWon()){
                        $href = $core->getCanonicalUrl() . "?do[]=setStone&row={$x}";
                        echo "<a class='stone pick_stone' href='{$href}'>SET STONE</a>";
                    } else {
                        echo "<span class='stone stone_noplayer'>NULL</span>";
                    }
                }
                echo "</li>";
            }
            echo "</ul>";
        }
        
        if($core->game->isWon()){
            echo "<div id='winner_overlay'>";
            if($core->isMe($core->game->getWinner())){
                echo "<h3>Awesome</h3><p>You won! Congrats, mate!</p>";
            } else {
                echo "<h3>Arg, {$core->game->getWinner()->getName()} won</h3><p>No worries, just try again!</p>";
            }
            echo "</div>";	
        }
        
        ?>
    
    </div>
    </div>
    </div>
    
    <div id="credits">
        <p><a href="https://github.com/julianburr/game-connectx/" target="_blank">Design &amp; Code by Julian Burr</a></p>
    </div>

</body>
</html>