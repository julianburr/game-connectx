<?php

//Check core instance
if(!is_object($core)){
	throw new Exception("Core object not found!");
}

?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
    <title>Connect X - Login</title>
    
    <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Nothing+You+Could+Do|The+Girl+Next+Door|Give+You+Glory' type='text/css'>
	<link rel='stylesheet' href='<?php echo $core->getBaseURL(); ?>files/css/login.css' type='text/css'>
</head>
<body lang="en">
	<h1>Connect X</h1>
    <p class="subtitle">Play it like the real children</p>
    
    <div class="login_panel">
    
		<?php
            $response = $core->getActionResponse("playerLogin");
            if(is_array($response['messages'])){
                foreach($response['messages'] as $message){
                    echo "<div class='message message_{$message['type']}'><div class='content'>{$message['content']}</div></div>";
                }
            }
        ?>
    
    	<form name="login" method="post" action="<?php echo $core->getCanonicalUrl(); ?>">
        <input type="hidden" name="do[]" value="playerLogin">
    	<div class="row row_text row_n_username">
        	<div class="input">
            	<input type="text" name="username" class="text" value="<?php if(isset($core->post['username'])) echo $core->post['username']; ?>" placeholder="Username / Email">
            </div>
        </div>
        <div class="row row_text row_password row_n_password">
        	<div class="input">
            	<input type="password" name="password" class="text password" placeholder="Password">
            </div>
        </div>
        <div class="row row_submit">
        	<div class="input">
            	<button>Come on in :)</button>
            </div>
        </div>
        <div class="row row_submit">
        	<div class="input">
            	<a class="signup" href="/signup">Or create an account</a>
            </div>
        </div>
    </div>
    
    <div id="credits">
        <p><a href="https://github.com/julianburr/game-connectx/" target="_blank">Design &amp; Code by Julian Burr</a></p>
    </div>
</body>
</html>