<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

echo '[ERROR]' . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine() . PHP_EOL;

//Rescue global variables
$GLOBALS["RESCUE"] = true;
$blacklist_globals = array (
	"GLOBALS",
	"loop",
	"discord",
	"restcord"
);
echo "Skipped: ";
foreach($GLOBALS as $key => $value) {
	$temp = array($value);
	if (!in_array($key, $blacklist_globals)) {
		try{
			VarSave("_globals", "$key.php", $value);
		}catch (Throwable $e) { //This will probably crash the bot
			echo "$key, ";
		}
	} else {
		echo "$key, ";
	}
}
echo PHP_EOL;

//Use Restcord to send a message
if ($restcord) {
	$guild = $restcord->guild->getGuild(['guild.id' => 116927365652807686]);
	try {
		$restcord->channel->createMessage([
			'channel.id' => 315259546308444160,
			'content'    => '<@116927250145869826> I just tried to restart due to an error!',
		]);
	} catch (GuzzleHttp\Command\Exception\CommandClientException $e) {
		var_dump($e->getResponse()->getBody()->getContents());
	}
}
//sleep(300);

echo "RESTARTING BOT" . PHP_EOL;
$discord = null;
$restart_cmd = 'cmd /c "'. __DIR__  . '\run.bat"'; //echo $restart_cmd . PHP_EOL;
//system($restart_cmd);
execInBackground($restart_cmd);
exit();
?>