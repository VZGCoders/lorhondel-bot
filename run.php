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

$options = array(
	'token' => "$token",
	'loop' => $loop,
	'browser' => $browser,
	'discord' => $discord,
	'logger' => $logger,
);
$lorhondel = new Lorhondel\Lorhondel($options);

function webapiFail($part, $id)
{
	//logInfo('[webapi] Failed', ['part' => $part, 'id' => $id]);
	//return new \GuzzleHttp\Psr7\Response(($id ? 404 : 400), ['Content-Type' => 'text/plain'], ($id ? 'Invalid' : 'Missing').' '.$part.PHP_EOL);
	$return = array();
	$return['message'] = '404: Not Found';
	$return['code'] = 0;
	return new \GuzzleHttp\Psr7\Response(404, ['Content-Type' => 'application/json'], json_encode($return));
}
function webapiSnow($string)
{
	return preg_match('/^[0-9]{16,18}$/', $string);
}

function sqlCreate(string $table, $data)
{
	include 'connect.php';
	$array = json_decode(json_encode($data), true); //var_dump($array);
	$types = '';
	$values = array();
	if(!empty($array)) {
		$sql = "INSERT INTO $table (";
		foreach ($array as $key => $value) {
			$sql .= str_replace('_', '', $key) . ', ';
		}
		$sql = substr($sql, 0, strlen($sql)-2) . ') VALUES (';
		foreach ($array as $key => $value) {
			$sql .= '?, ';
			$types .= 's';
			$values[] = str_replace('_', '', $value); //Remove any _ from variable names
		}
		$sql = substr($sql, 0, strlen($sql)-2) . ')';
	} else return null;
	echo '[SQL] ' . $sql . PHP_EOL;
	
	$stmt = mysqli_prepare($con, $sql);
	$stmt->bind_param($types, ...$values);
	return $stmt->execute();
	//printf("%d row inserted.\n", $stmt->affected_rows);
}
function sqlUpdate(array $columns = [], string $table, string $wherecolumn = '', array $values = [])
{
	if (empty($columns)) return [];
	if (count($columns) != count($values)) return [];
	include 'connect.php';
	
	$sql = "UPDATE $table";
	$sql .= 'SET ';
	for($x=0;$x<count($columns);$x++)
		if ($x<count($columns)-1) $sql .= $columns[$x] .  ' = ' . $values[$x] . ', ';
		else $sql .= $columns[$x] . ' = ' . $values[$x] . ' ';
	if ($wherecolumn && !empty($values)) $sql .= " WHERE $wherecolumn = ?";
	echo '[SQL] ' . $sql . PHP_EOL;
	
	//Check if already exists
}
function sqlDelete(array $columns = [], string $table, string $wherecolumn = '', array $values = [])
{
	if (empty($columns)) return [];
	include 'connect.php';
	
	$sql = "DELETE ";
	for($x=0;$x<count($columns);$x++)
		if ($x<count($columns)-1) $sql .= $columns[$x] . ', ';
		else $sql .= $columns[$x] . ' ';
	$sql .= "FROM $table";
	if ($wherecolumn && !empty($values)) $sql .= " WHERE $wherecolumn = ?";
	echo '[SQL] ' . $sql . PHP_EOL;
	
	
}
function sqlGet(array $columns = [], string $table, string $wherecolumn = '', array $values = [], string $order = '', int|string $limit = ''): array
{
	if (empty($columns)) return [];
	include 'connect.php'; //$con
	$array = array();
	
	$sql = "SELECT ";
	for($x=0;$x<count($columns);$x++)
		if ($x<count($columns)-1) $sql .= $columns[$x] . ', ';
		else $sql .= $columns[$x] . ' ';
	$sql .= "FROM $table";
	if ($wherecolumn && !empty($values)) $sql .= " WHERE $wherecolumn = ?";
	if ($order) $sql .= " ORDER BY $order";
	if ($limit) $sql .= " LIMIT $limit";
	echo '[SQL] ' . $sql . PHP_EOL;
	
	if (!$wherecolumn) {
		$stmt = mysqli_prepare($con, $sql); //Select all values in the column
		$stmt->execute();
		if ($result = $stmt->get_result()) {
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				foreach ($row as $r => $v) {
					$array[$r] = $v;
				}
			}
			
		} else return [mysqli_stmt_error($stmt)];
	} else if ($wherecolumn && !empty($values)) {
		if ($stmt = mysqli_prepare($con, $sql)) {
			$stmt->bind_param("s", $value);
			foreach ($values as $value) {
				echo 'value: ' . $value . PHP_EOL;
				$stmt->execute();
				if ($result = $stmt->get_result()) {
					while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
						foreach ($row as $r => $v) {
							$array[$r] = $v;
						}
					}
				} else return [mysqli_stmt_error($stmt)];
			}
		} 
	}
	return $array;
}

$webapi = new \React\Http\HttpServer($loop, function (\Psr\Http\Message\ServerRequestInterface $request) use ($lorhondel, $discord, $stats) {
	echo '[API] ';
	$path = explode('/', $request->getUri()->getPath());
	$ver = (isset($path[1]) ? (string) strtolower($path[1]) : false); if ($ver) echo '[ver] ' . $ver . ' ';
	$repository = $sub = (isset($path[2]) ? (string) strtolower($path[2]) : false); if ($sub) echo '[sub] ' . $sub . ' ';
	$method = $id = (isset($path[3]) ? (string) strtolower($path[3]) : false); if ($id) echo '[method/id] ' . $id . ' ';
	$id2 = (isset($path[4]) ? (string) strtolower($path[4]) : false); if ($id2) echo '[id2] ' . $id2 . ' ';
	$partial = $id3 = (isset($path[5]) ? (string) strtolower($path[5]) : false); if ($id3) echo '[partial/id3] ' . $id3 . ' ';
	$id4 = (isset($path[6]) ? (string) strtolower($path[6]) : false); if ($id4) echo '[id4] ' . $id4 . ' ';
	$id5 = (isset($path[7]) ? (string) strtolower($path[7]) : false); if ($id5) echo '[id5] ' . $id5 . ' ';
	echo PHP_EOL;
	if($data = json_decode((string)$request->getBody())) {
		echo '[-----DATA START-----]' . PHP_EOL;
		var_dump($data);
		echo '[-----DATA END-----]' . PHP_EOL;
	}
	if ($attributes = $request->getAttributes()) {
		echo '[-----ATTRIBUTES START-----]' . PHP_EOL;
		var_dump($attributes);
		echo '[-----ATTRIBUTES END-----]' . PHP_EOL;
	}
	if ($headers = $request->getHeaders()) {
		echo '[-----HEADERS START-----]' . PHP_EOL;
		var_dump($headers);
		echo '[-----HEADERS END-----]' . PHP_EOL;
	}
	
	
	$lorhondelBattleground = $discord->getChannel(887118621065768970);
	$lorhondelBotSpam = $discord->getChannel(887118679697940481);
	
	$_5xx = array();
	$_5xx['message'] = '5xx: Server Error'; //Something went wrong (Code is broken)
	$_5xx['code'] = 0;
	
	$_502 = array();
	$_502['message'] = '502: Gateway Unavailable'; //??? This is the gateway
	$_502['code'] = 0;
	
	$_429 = array();
	$_429['message'] = '429: Too Many Requests'; //Ratelimit
	$_429['code'] = 0;
	
	$_405 = array();
	$_405['message'] = '405: Method Not Allowed'; //Not valid for endpoint
	$_405['code'] = 0;
	
	$_404 = array();
	$_404['message'] = '404: Not Found'; //Resource not found
	$_404['code'] = 0;
	
	$_403 = array();
	$_403['message'] = '403: Forbidden. You lack permissions to perform that action'; //Missing permissions
	$_403['code'] = 50013;
	
	$_401 = array();
	$_401['message'] = '401: Unauthorized. Invalid authentication token provided'; //Invalid token
	$_401['code'] = 50014;
	
	$_400 = array();
	$_400['message'] = '400: Bad Request'; //Improper format or bad data
	$_400['code'] = 0;
	
	$_304 = array();
	$_304['message'] = '304: Not Modified'; //Patch but no change
	$_304['code'] = 0;
	
	$_204 = array();
	$_204['message'] = '204: No Content'; //Resource 'exists' but is empty
	$_204['code'] = 0;
	
	$_201 = array();
	$_201['message'] = '201: Created';
	$_201['code'] = 0;
	
	$_200 = array();
	$_200['message'] = '200: OK';
	$_200['code'] = 0;
	
	$whitelisted = false;
	if (
		substr($request->getServerParams()['REMOTE_ADDR'], 0, 6) == '10.0.0' ||
		substr($request->getServerParams()['REMOTE_ADDR'], 0, 7) == '127.0.0'
	) $whitelisted = true;
	else {
		echo "[REMOTE_ADDR]" . $request->getServerParams()['REMOTE_ADDR'].PHP_EOL;
	}

	//$array = array();
	//$array['message'] = '404: Not Found';
	//$array['code'] = 0;
	//$return = $array;
	
	//logInfo('[webapi] Request', ['path' => $path]);
	if ($ver == 'v1') {
	try{
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
			$return = [];
			$promise = $discord->users->fetch($idarray[0])->then(function ($user) use (&$return) {
			  $return[$user->id] = $user->avatar;
			});
			
			for ($i = 1; $i < count($idarray); $i++) {
			  $promise->then(function () use (&$return, $idarray, $i, $discord) {
				return $discord->users->fetch($idarray[$i])->then(function ($user) use (&$return) {
				  $return[$user->id] = $user->avatar;
				});
			  });
			}

			$promise->done(function () use ($return) {
			  return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($return));
			}, function () use ($return) {
			  // return with error ?
			  return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($return));
			});
			break;
		default:
			break;
	}
	switch ($repository) {
		case 'oauth2':
			if (!$method == 'bot') break;
			if (!$id2 == '@me') break;
			$return = array();
			return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($return));
		case 'gateway':
			if ($method == 'bot') {
				$return = array();
				$return['url'] = 'https://lorhondel.valzargaming.com/gateway/';
				$return['shards'] = 1;
				$return['session_start_limit'] = [
				"total" => 1000, //	The total number of session starts the current user is allowed
				"remaining" => 999, //	The remaining number of session starts the current user is allowed
				"reset_after" => 14400000, // The number of milliseconds after which the limit resets
				"max_concurrency" => 1 // The number of identify requests allowed per 5 seconds
				];
				return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($return));
			}
			return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($return));
		case 'ping':
			echo '[PING]' . PHP_EOL;
			$lorhondelBotSpam->sendMessage('Pong!');
			$return = 'Pong!';
			break;
		case 'stats':
			if ($embed = $stats->handle()) {
				$lorhondelBotSpam->sendEmbed($embed);
				$return = $embed;
			} else return webapiFail('stats', $stats);
			break;
		case 'player':
			$snowflake = \Lorhondel\generateSnowflake(time(), 0, 0, count($lorhondel->players));
			echo '[SNOWFLAKE] ' . $snowflake . PHP_EOL;
			$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
				'id' => $snowflake,
				'user_id' => 116927250145869826,
				'species' => 'Elarian', //Elarian, Manthean, Noldarus, Veias, Jedoa
				'health' => 0,
				'attack' => 1,
				'defense' => 2,
				'speed' => 3,
				'skillpoints' => 4,
			]);
			if ($player = $lorhondel->players->offsetGet(116927250145869826)) {
				$exist = true;
				$result = 'Editing existing Part with ID ' . $part->id;
			} else {
				$exist = false;
				$result = 'Creating new Part with ID ' . $part->id;
			}
			echo '[SAVE] ' . $lorhondel->players->save($part)->done( //Update repository
				function ($part) use ($lorhondel, $lorhondelBotSpam, $exist) {
					if ($exist) {
						$lorhondelBotSpam->sendMessage('Updated existing Part with ID: ' . $part->id);
						//Try to update in SQL
					} else {
						$lorhondelBotSpam->sendMessage('Added new Part with ID: ' . $part->id);
						//Try to create in SQL
					}
				},
				function ($error) {
					echo '[ERROR] ';
					//var_dump($error->getMessage());
				}
			);
			$lorhondelBotSpam->sendMessage(json_encode($result));
			break;
		case 'players':
			echo '[PLAYERS] ';
			$allowed = ['*', 'id', 'userid', 'user_id', 'species', 'health', 'attack', 'defense', 'speed', 'skillpoints'];
			if ($method == 'get') {
				if ($id2 == 'all' || $id2 == 'freshen') {
					if ($whitelisted) {
						echo '[GET ALL]' . PHP_EOL;
						$return = sqlGet(['*'], 'players', '', [], '', 500);
						return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($return));
						break;
					} else {
						$return = $_403;
						return new \GuzzleHttp\Psr7\Response(403, ['Content-Type' => 'application/json'], json_encode($return));
					}
				}
				elseif (is_int((int)$id2)) {
					if (in_array($partial, $allowed)) {
						if (empty($return = sqlGet([$partial], 'players', 'id', [$id2], '', 1))) {
							$return = $_404;
							return new \GuzzleHttp\Psr7\Response(404, ['Content-Type' => 'application/json'], json_encode($return));
						}
					}
					elseif (!$partial) {
						if (empty($return = sqlGet(['*'], 'players', 'id', [$id2], '', 1))) {
							$return = $_404;
							return new \GuzzleHttp\Psr7\Response(404, ['Content-Type' => 'application/json'], json_encode($return));
						}
					}
					else {
						$return = array($_400);
					}
				}
				else {
					$return = array($_400);
					return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($return));
				}
			}
			elseif ($method == 'patch') {
				if ($whitelisted) {
					//check if already exists
					//404 if does not exist
				} else {
					$return = $_403;
					return new \GuzzleHttp\Psr7\Response(403, ['Content-Type' => 'application/json'], json_encode($return));
				}
			}
			elseif ($method == 'post') {
				if ($whitelisted) {
					if (is_int((int)$id2)) {
						if (!empty($return = sqlGet(['*'], 'players', 'id', [$id2], '', 1))) {
							var_dump($return);
							$return = $_304;
							return new \GuzzleHttp\Psr7\Response(304, ['Content-Type' => 'application/json'], json_encode($return));
						}
						else {
							//Check if part exists in repository
							if (is_array($data)) {
								$part_id = $data['id'];
								$part_user_id = $data['user_id'];
								$part_species = $data['species'];
								$part_health = $data['health'];
								$part_attack = $data['attack'];
								$part_defense = $data['defense'];
								$part_speed = $data['speed'];
								$part_skillpoints = $data['skillpoints'];
							} elseif (is_object($data)) {
								$part_id = $data->id;
								$part_user_id = $data->user_id;
								$part_species = $data->species;
								$part_health = $data->health;
								$part_attack = $data->attack;
								$part_defense = $data->defense;
								$part_speed = $data->speed;
								$part_skillpoints = $data->skillpoints;
							}
							if($part = $lorhondel->players->offsetGet($partid) && $part->id) {
								echo '[EXISTS] ' . $partid . PHP_EOL;
								var_dump($return);
								var_dump($part);
								$return = $_204;
								return new \GuzzleHttp\Psr7\Response(204, ['Content-Type' => 'application/json'], json_encode($return));
							}
							//Recreate the part using factory and save it
							$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
								'id' => $part_id,
								'user_id' => $part_user_id,
								'species' => $part_species,
								'health' => $part_health,
								'attack' => $part_attack,
								'defense' => $part_defense,
								'speed' => $part_speed,
								'skillpoints' => $part_skillpoints,
							]);
							$lorhondel->players->push($part);
							$lorhondelBotSpam->sendMessage('Added part to repository');
							if (sqlCreate($repository, $data)) {
								$return = $_201;
								return new \GuzzleHttp\Psr7\Response(201, ['Content-Type' => 'application/json'], json_encode($return));
							} else { //The data provided is either missing or didn't get passed to the SQL method
								echo '[POST CREATE ERROR]' . PHP_EOL;
								$return = $_204;
								return new \GuzzleHttp\Psr7\Response(204, ['Content-Type' => 'application/json'], json_encode($return));
							}
						}
					}
					else {
						$return = array($_400);
						return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($return));
					}
					//do not post if $id2 exists
					//create if does not exist
					$return = sqlGet(['*'], 'players');
				} else {
					$return = $_403;
					return new \GuzzleHttp\Psr7\Response(403, ['Content-Type' => 'application/json'], json_encode($return));
				}
			}
			elseif ($method == 'delete') {
				if ($whitelisted) {
					//check if exists
					//delete if exists
					//404 if does not exist
				} else {
					$return = $_403;
					return new \GuzzleHttp\Psr7\Response(403, ['Content-Type' => 'application/json'], json_encode($return));
				}
			} else {
				$return = $_400;
				return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($return));
			}
			break;
		case 'dumpplayers':
			if ($lorhondel->players)
				$return = $lorhondel->players;
			break;
		default:
			$return = $_404;
			return new \GuzzleHttp\Psr7\Response(404, ['Content-Type' => 'application/json'], json_encode($return));
	}
	}
	catch (Exception $e) {
		$return = $_5xx;
		return new \GuzzleHttp\Psr7\Response(500, ['Content-Type' => 'application/json'], json_encode($return));
	}
	} else {
		//50041
		$_400 = array();
		$_400['message'] = '400: Bad Request. Invalid API version provided'; //Improper format or bad data
		$_400['code'] = 50001;
		return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($return));
	}
	/*if ($return)*/ return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($return));
});
$socket = new \React\Socket\Server(sprintf('%s:%s', '0.0.0.0', '27759'), $loop);
$webapi->listen($socket);
$webapi->on('error', function ($e) {
	/*
	logDebug('[webapi] Error', [
		'msg' => $e->getMessage(),
		'prv' => ($e->getPrevious() ? $e->getPrevious()->getMessage() : null)
	]);
	*/
	echo '[ERROR] ' . $e->getMessage() . PHP_EOL;
	var_dump($e);
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
	$discord->once('ready', function ($discord) use ($lorhondel, $loop, $token, $stats, /*$connector,*/ $browser) {
		$act  = $discord->factory(\Discord\Parts\User\Activity::class, [
		'name' => 'superiority',
		'type' => \Discord\Parts\User\Activity::TYPE_COMPETING
		]);
		$discord->updatePresence($act, false, 'online', false);
		echo "[READY]" . PHP_EOL;
		include 'ready-include.php'; //All modular event handlers
	 });
	$discord->run();
}catch (Throwable $e) { //Restart the bot
	include 'rescue-catch-include.php';
}
?>