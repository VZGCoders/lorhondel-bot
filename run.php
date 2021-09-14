<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

ini_set('max_execution_time', 0);
//This file was written by Valithor#5947 <@116927250145869826>
//Special thanks to keira#7829 <@297969955356540929> for helping me get this behemoth working after converting from DiscordPHP

//This bot is based off of a much older version of Palace Bot#9203 <@662093882795753482>
//Updates are made on an "as needed" basis and therefore may contain bugs
//It is private and only accessible from the official ValZarGaming discord server
//It provides SQL access to ValZarGaming's servers, so we need to log and regulate who has access to it

include 'vendor/autoload.php';
include 'src/Lorhondel/Lorhondel.php';
ini_set('memory_limit', '-1'); 	//Unlimited memory usage

function execInBackground($cmd) { 
    if (substr(php_uname(), 0, 7) == "Windows") {
		pclose(popen("start ". $cmd, "r")); //pclose(popen("start /B ". $cmd, "r"));
    }else exec($cmd . " > /dev/null &");
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

$options = array(
	'loop' => $loop,
	'browser' => $browser,
	'discord' => $discord,
);

$lorhondel = new Lorhondel\Lorhondel($options);

function webapiFail($part, $id)
{
	//logInfo('[webapi] Failed', ['part' => $part, 'id' => $id]);
	//return new \GuzzleHttp\Psr7\Response(($id ? 404 : 400), ['Content-Type' => 'text/plain'], ($id ? 'Invalid' : 'Missing').' '.$part.PHP_EOL);
	$results = array();
	$results['message'] = '404: Not Found';
	$results['code'] = 0;
	return new \GuzzleHttp\Psr7\Response(404, ['Content-Type' => 'application/json'], json_encode($results));
}
function webapiSnow($string)
{
	return preg_match('/^[0-9]{16,18}$/', $string);
}

$webapi = new \React\Http\Server($loop, function (\Psr\Http\Message\ServerRequestInterface $request) use ($discord) {
	$path = explode('/', $request->getUri()->getPath());
	$ver = (isset($path[1]) ? (string) $path[1] : false);
	$sub = (isset($path[2]) ? (string) $path[2] : false);
	$id = (isset($path[3]) ? (string) $path[3] : false);
	$id2 = (isset($path[4]) ? (string) $path[4] : false);
	$ip = (isset($path[5]) ? (string) $path[5] : false);
	
	if ($ip) echo '[REQUESTING IP] ' . $ip . PHP_EOL ;
	//if (substr($request->getServerParams()['REMOTE_ADDR'], 0, 6) != '10.0.0')
	echo "[REMOTE_ADDR]" . $request->getServerParams()['REMOTE_ADDR'].PHP_EOL;

	$array = array();
	$array['message'] = '404: Not Found';
	$array['code'] = 0;
	$return = json_encode($array);
	
	//logInfo('[webapi] Request', ['path' => $path]);

	switch ($sub) {
		case 'channel':
			if (!$id || !webapiSnow($id) || !$return = $discord->getChannel($id))
				return webapiFail('channel_id', $id);
			break;

		case 'guild':
			if (!$id || !webapiSnow($id) || !$return = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			break;
			
		case 'bans':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			$return = $guild->bans;
			break;
			
		case 'channels':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			$return = $guild->channels;
			break;
			
		case 'members':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			$return = $guild->members;
			break;
			
		case 'emojis':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			$return = $guild->emojis;
			break;
		
		case 'invites':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			$return = $guild->invites;
			break;
		
		case 'roles':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			$return = $guild->roles;
			break;

		case 'guildMember':
		case 'member':
			if (!$id || !webapiSnow($id) || !$guild = $discord->guilds->offsetGet($id))
				return webapiFail('guild_id', $id);
			if (!$id2 || !webapiSnow($id2) || !$return = $guild->members->offsetGet($id2))
				return webapiFail('user_id', $id2);
			break;

		case 'user':
			if (!$id || !webapiSnow($id) || !$return = $discord->users->offsetGet($id)) {
				return webapiFail('user_id', $id);
			}
			break;

		case 'userName':
			if (!$id || !$return = $discord->users->get('name', $id))
				return webapiFail('user_name', $id);
			break;

		case 'restart':
			if (substr($request->getServerParams()['REMOTE_ADDR'], 0, 6) != '10.0.0') {
				echo '[REJECT]' . $request->getServerParams()['REMOTE_ADDR'] . PHP_EOL;
				return new \GuzzleHttp\Psr7\Response(501, ['Content-Type' => 'text/plain'], 'Reject'.PHP_EOL);
			}
			$return = 'restarting';
			//execInBackground('cmd /c "'. __DIR__  . '\run.bat"');
			//exec('/home/outsider/bin/stfc restart');
			break;

		case 'lookup':
			return new \GuzzleHttp\Psr7\Response(501, ['Content-Type' => 'text/plain'], 'Reject'.PHP_EOL); //Restcord is deprecated
			if (substr($request->getServerParams()['REMOTE_ADDR'], 0, 6) != '10.0.0') { //This can be abused to cause 429's with Restcord and should only be used by the website. All other cases should use 'user'
				echo '[REJECT]' . $request->getServerParams()['REMOTE_ADDR'] . PHP_EOL;
				return new \GuzzleHttp\Psr7\Response(501, ['Content-Type' => 'text/plain'], 'Reject'.PHP_EOL);
			}
			if (!$id || !webapiSnow($id) || !$return = $discord->user->fetch($id))
				return webapiFail('user_id', $id);
			break;

		case 'owner':
			if (substr($request->getServerParams()['REMOTE_ADDR'], 0, 6) != '10.0.0') {
				echo '[REJECT]' . $request->getServerParams()['REMOTE_ADDR'] . PHP_EOL;
				return new \GuzzleHttp\Psr7\Response(501, ['Content-Type' => 'text/plain'], 'Reject'.PHP_EOL);
			}
			if (!$id || !webapiSnow($id))
				return webapiFail('user_id', $id);
			$return = false;
			if ($user = $discord->users->offsetGet($id)) { //Search all guilds the bot is in and check if the user id exists as a guild owner
				foreach ($discord->guilds as $guild) {
					if ($id == $guild->owner_id) {
						$return = true;
						break 1;
					}
				}
			}
			break;
			
		case 'avatar':
			if (!$id || !webapiSnow($id)) {
				return webapiFail('user_id', $id);
			}
			if (!$user = $discord->users->offsetGet($id)) {
				$discord->users->fetch($id)->done(
					function ($user) {
						$return = $user->avatar;
						return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'text/json'], json_encode($return));
					}, function ($error) {
						return webapiFail('user_id', $id);
					}
				);
				$return = 'https://cdn.discordapp.com/embed/avatars/'.rand(0,4).'.png';
			} else {
				$return = $user->avatar;
			}
			//if (!$return) return new \GuzzleHttp\Psr7\Response(($id ? 404 : 400), ['Content-Type' => 'text/plain'], ('').PHP_EOL);
			break;
			
		case 'avatars':
			$idarray = $data ?? array(); // $data contains POST data
			$results = [];
			$promise = $discord->users->fetch($idarray[0])->then(function ($user) use (&$results) {
			  $results[$user->id] = $user->avatar;
			});
			
			for ($i = 1; $i < count($idarray); $i++) {
			  $promise->then(function () use (&$results, $idarray, $i, $discord) {
				return $discord->users->fetch($idarray[$i])->then(function ($user) use (&$results) {
				  $results[$user->id] = $user->avatar;
				});
			  });
			}

			$promise->done(function () use ($results) {
			  return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($results));
			}, function () use ($results) {
			  // return with error ?
			  return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($results));
			});
			break;

		default:
			$results = array();
			$results['message'] = '404: Not Found';
			$results['code'] = 0;
			return new \GuzzleHttp\Psr7\Response(404, ['Content-Type' => 'application/json'], json_encode($results));
	}
	/*if ($return)*/ return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'text/json'], json_encode($return));
});
$socket = new \React\Socket\Server(sprintf('%s:%s', '0.0.0.0', '27759'), $discord->getLoop());
$webapi->listen($socket);
$webapi->on('error', function ($e) {
	/*
	logDebug('[webapi] Error', [
		'msg' => $e->getMessage(),
		'prv' => ($e->getPrevious() ? $e->getPrevious()->getMessage() : null)
	]);
	*/
	echo $e->getMessage() . PHP_EOL;
});

try{
	include 'rescue-try-include.php';
	$discord->on('error', function ($error) { //Handling of thrown errors
		echo "[ERROR] $error" . PHP_EOL;
		try{
			echo '[ERROR EVENT]' . $error->getMessage() . " in file " . $error->getFile() . " on line " . $error->getLine() . PHP_EOL;
		}catch(Exception $e) {
			echo '[ERROR EVENT]' . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine() . PHP_EOL;
		}
	});
	$discord->once('ready', function ($discord) use ($loop, $token, $restcord, $stats, /*$connector,*/ $browser) {
		$act  = $discord->factory(\Discord\Parts\User\Activity::class, [
		'name' => 'superiority',
		'type' => \Discord\Parts\User\Activity::TYPE_COMPETING
		]);
		$discord->updatePresence($act, false, 'online', false);
		echo "[SETUP]" . PHP_EOL;
		echo "[READY]" . PHP_EOL;
		include 'ready-include.php'; //All modular event handlers
	 });
	$discord->on('disconnect', function ($erMsg, $code) use ($discord, $loop, $token) { //Automatically reconnect if the bot disconnects due to inactivity (Not tested)
		include 'disconnect-include.php';
	});
	//include_once 'slash.php';
	$discord->run();
	//$client->linkDiscord($discord, false); // false signifies that we still want to use the HTTP server - default is true, which will use gateway
	//$client->run();
}catch (Throwable $e) { //Restart the bot
	include 'rescue-catch-include.php';
}
?>