<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

echo "[READY]";
echo " Logged in as {$discord->user->tag} ({$discord->user->id})".PHP_EOL;

include_once "custom_functions.php";

$timestampSetup = time();
echo "[timestampSetup]: ";
$dt = new DateTime("now");  // convert UNIX timestamp to PHP DateTime
echo $dt->format('d-m-Y H:i:s') . PHP_EOL; // output = 2017-01-01 00:00:00

$discord->on('message', function ($message) use ($discord, $loop, $token, $stats, /*$connector,*/ $browser) { //Handling of a message
	include 'message-include.php';
}); //end small function with content

$discord->on("error", function(\Throwable $e) {
	echo '[ERROR]' . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine() . PHP_EOL;
});