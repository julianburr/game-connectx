<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
    <title>Connect X - You've seen nothing!</title>
    
    <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Nothing+You+Could+Do|The+Girl+Next+Door|Give+You+Glory' type='text/css'>
	<link rel='stylesheet' href='<?php echo $core->getBaseURL(); ?>files/css/exception.css' type='text/css'>
</head>
<body lang="en">
	<h1>Bloody hell</h1>
    <p class="subtitle">Seems like something went wrong ... sorry mate</p>
    <div class="exception_panel">
    <div class="inner">
    	<p class="message"><?php echo $e->getMessage(); ?></p>
    	<pre class="trace"><?php echo $e->getTraceAsString(); ?></pre>
    </div>
    </div>
    
    <div id="credits">
        <p><a href="https://github.com/julianburr/game-connectx/" target="_blank">Design &amp; Code by Julian Burr</a></p>
    </div>
</body>
</html>