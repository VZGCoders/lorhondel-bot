<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

ini_set('max_execution_time', 0);

include 'vendor/autoload.php';
include 'autoload.php'; //Needed for testing

include 'src/Lorhondel/Lorhondel.php';
ini_set('memory_limit', '-1'); 	//Unlimited memory usage

function execInBackground($cmd) { 
    if (substr(php_uname(), 0, 7) == "Windows") {
		pclose(popen("start ". $cmd, "r")); //pclose(popen("start /B ". $cmd, "r"));
    } else exec($cmd . " > /dev/null &");
}

require getcwd() . '\token.php';
$logger = new Monolog\Logger('New logger');
$logger->pushHandler(new Monolog\Handler\StreamHandler('php://stdout'));
$loop = React\EventLoop\Factory::create();
$discord_options = array(
	'loop' => $loop,
	'socket_options' => [
        'dns' => '8.8.8.8', // can change dns
	],
	'token' => "$token",
	'loadAllMembers' => true,
	'storeMessages' => true,
	'logger' => $logger,
	'intents' => \Discord\WebSockets\Intents::getDefaultIntents() | \Discord\WebSockets\Intents::GUILD_MEMBERS, // default intents as well as guild members
);
$discord = new Discord\Discord($discord_options);

$browser = new \React\Http\Browser($loop/*, $connector*/);

include 'stats_object.php';
$stats = new Stats();
$stats->init($discord);
$socket = new \React\Socket\Server(sprintf('%s:%s', '0.0.0.0', '27759'), $loop);

$options = array(
	'token' => "$token",
	'loop' => $loop,
	'browser' => $browser,
	'discord' => $discord,
	'logger' => $logger,
	'loadAllMembers' => false,
	'server' => true,
	'socket' => $socket,
);
$lorhondel = new Lorhondel\Lorhondel($options);

try{
	include 'rescue-try-include.php';
	$lorhondel->discord->on('error', function ($error) { //Handling of thrown errors
		echo "[ERROR] $error" . PHP_EOL;
		try{
			echo '[ERROR EVENT]' . $error->getMessage() . " in file " . $error->getFile() . " on line " . $error->getLine() . PHP_EOL;
		}catch(Exception $e) {
			echo '[ERROR EVENT]' . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine() . PHP_EOL;
		}
	});
	$lorhondel->discord->once('ready', function ($discord) use ($lorhondel, $loop, $token, $stats, /*$connector,*/ $browser) {
		$act  = $lorhondel->discord->factory(\Discord\Parts\User\Activity::class, [
		'name' => 'superiority',
		'type' => \Discord\Parts\User\Activity::TYPE_COMPETING
		]);
		$lorhondel->discord->updatePresence($act, false, 'online', false);
		echo "[READY]" . PHP_EOL;
		include 'ready-include.php'; //All modular event handlers
		include 'connect.php';
		$lorhondel->players->freshen();	//Import existing parts from SQL
		$lorhondel->parties->freshen();	//Import existing parts from SQL
		$lorhondel->battles->freshen();	//Import existing parts from SQL
	 });
	$lorhondel->discord->run();
}catch (Throwable $e) { //Restart the bot
	include 'rescue-catch-include.php';
}