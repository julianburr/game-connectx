<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
    <link rel="canonical" href="<?php echo $core->getCanonicalURL(); ?>">
    <title>Connect X</title>
    
	<script type="text/javascript" src="<?php echo $core->getBaseURL(); ?>files/js/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo $core->getBaseURL(); ?>files/js/main.js"></script>
    
    <link rel='stylesheet' href='<?php echo $core->getBaseURL(); ?>files/css/fonts.css' type='text/css'>
	<link rel='stylesheet' href='<?php echo $core->getBaseURL(); ?>files/css/main.css' type='text/css'>
</head>
<body lang="en" class="default game" data-baseurl="<?php echo $core->getBaseURL(); ?>">
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
            <h3>Current Games</h3>
        </div>
        
        <ul class="gamelist">
        <?php
        
		$gamecnt = 0;
        foreach($core->getGames() as $game){
            echo "<li><a href='{$game->getCanonicalUrl()}'><div class='border'>";
            echo "Game #{$game->getID()}";
            echo " <span class='small status'>{$game->getStatus()}</span>";
            echo " <span class='playercnt'>{$game->getPlayerCount()}</span>";
            echo "</div></a></li>";
			$gamecnt++;
        }
		
		if($gamecnt == 0){
			echo "<li><div class='border noresult'>No games found!</div></li>";
		}
        
        ?>
        </ul>
        
        <div class="border">
            <div class="action_buttons">
            	<?php
                
				$base = $core->getCanonicalURL();
				if(strpos($base, "?") === false){
                    $base .= "?";
                }
				
				?>
            	<a class='button action action_newGame' href='<?php echo $base; ?>&do[]=createGame'>Create New Game</a>
            </div>
        </div>
    
    </div>
    </div>
    </div>
    
    <div id="field">
    <div id="field_inner">
    <div id="field_inner_load">
    
        <p>Select a game and start playing!</p>
    
    </div>
    </div>
    </div>
    
    <div id="credits">
        <p><a href="https://github.com/julianburr/game-connectx/" target="_blank">Design &amp; Code by Julian Burr</a></p>
    </div>

</body>
</html>