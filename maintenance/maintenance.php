<?php
// get the command to run and the wiki to run it on
$sub = $argv[1]; 
$command = $argv[2];

// remove the first two arguments, so we don't confuse the script to be called
$args = "";
for ($counter = 3; $counter < $argc; $counter++){
	$argv[$counter-2] = $argv[$counter];
	$args = $args." ".$argv[$counter-2];
}

// set the command
$commandarray = explode(".",$command);
$command = $commandarray[0];
$command = $command.".php";
	
// set domain name
$_SERVER['SERVER_NAME'] = $sub . '.opensuse.org';
 
echo '--------------------------------------
Running '. $command . $args .' for '. $sub .'.opensuse.org
--------------------------------------
';
include("./".$command);
