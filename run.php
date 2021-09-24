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
	'loadAllMembers' => false,
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

function json_validate($data)
{
	if (is_array($data) || is_object($data))
		$data = json_encode($data);
    // decode the JSON data
		
    $result = json_decode($data);

    // switch and check possible JSON errors
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = ''; // JSON is valid // No error has occurred
            break;
        case JSON_ERROR_DEPTH:
            $error = 'The maximum stack depth has been exceeded.';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'Invalid or malformed JSON.';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'Control character error, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_SYNTAX:
            $error = 'Syntax error, malformed JSON.';
            break;
        // PHP >= 5.3.3
        case JSON_ERROR_UTF8:
            $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_RECURSION:
            $error = 'One or more recursive references in the value to be encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_INF_OR_NAN:
            $error = 'One or more NAN or INF values in the value to be encoded.';
            break;
        case JSON_ERROR_UNSUPPORTED_TYPE:
            $error = 'A value of a type that cannot be encoded was given.';
            break;
        default:
            $error = 'Unknown JSON error occured.';
            break;
    }

    if ($error !== '') {
        echo '[JSON ERROR] '. $error . PHP_EOL;
    }
    // everything is OK
	//echo '[JSON OKAY]' . PHP_EOL; //var_dump ($result);
    return $result;
}

function sqlGet(array $columns = [], string $table = '', string $wherecolumn = '', array $values = [], string $order = '', $limit = ''): array
{
	//sqlGet(['*'], $repository, '', [], '', 500); //get all
	if (empty($columns)) return [];
	if (!$table) return [];
	include 'connect.php'; //$mysqli and $pdo
	$array = array();
	
	$sql = "SELECT ";
	for($x=0;$x<count($columns);$x++)
		if ($x<count($columns)-1) $sql .= $columns[$x] . ', ';
		else $sql .= $columns[$x] . ' ';
	$sql .= "FROM $table";
	if ($wherecolumn && !empty($values)) {
		$sql .= " WHERE $wherecolumn = ?";
	}
	if ($order) $sql .= " ORDER BY $order";
	if ($limit) $sql .= " LIMIT $limit";
	echo '[SQL] ' . $sql . PHP_EOL;
	$value_string = '(';
	foreach ($values as $value) {
		//if ($value !== null) {
			//if ($value == false) $value = '0';
			$value_string .= "$value, ";
		//}
	}
	$value_string = substr($value_string, 0, strlen($value_string)-2) . ')';
	echo $value_string . PHP_EOL;
	
	if (!$wherecolumn) {
		$stmt = mysqli_prepare($mysqli, $sql); //Select all values in the column
		$stmt->execute();
		if ($result = $stmt->get_result()) {
			while ($rows = $result->fetch_all(MYSQLI_ASSOC)) {
				foreach ($rows as $row) {
					foreach ($row as $r => $v) {
						$array[$row['id']][$r] = $v;
					}
				}
			}
			
		} else {
			var_dump (mysqli_stmt_error($stmt));
			return [];
		}
	}
	elseif ($wherecolumn && !empty($values)) {
		if ($stmt = mysqli_prepare($mysqli, $sql)) {
			$stmt->bind_param("s", $value);
			foreach ($values as $value) {
				$stmt->execute();
				if ($result = $stmt->get_result()) {
					while ($rows = $result->fetch_all(MYSQLI_ASSOC)) {
						foreach ($rows as $row) {
							foreach ($row as $r => $v) {
								$array[$row['id']][$r] = $v;
							}
						}
					}
				} else {
					var_dump(mysqli_stmt_error($stmt));
					return [];
				}
			}
		} 
	}
	echo '[GET ARRAY]'; var_dump($array);
	return $array;
}
function sqlCreate(string $table, $data)
{
	include 'connect.php';
	if (is_object($data))
		$string = json_encode($data);
	echo '[DATA]' . PHP_EOL;
	var_dump ($data);
	//$data = json_decode(json_encode($data), true); //var_dump($data);
	$types = '';
	$values_clean = array();
	if (!empty($data)) {
		$sql = "INSERT INTO $table (";
		foreach ($data as $key => $value) {
			$sql .= $key . ', ';
		}
		$sql = substr($sql, 0, strlen($sql)-2) . ') VALUES (';
		foreach ($data as $key => $value) {
			$sql .= '?, ';
			//$types .= 's';
			$value = $value; //Remove any _ from variable names
			$values_clean[] = $value;
			//$sql .= "$value, ";
		}
		$sql = substr($sql, 0, strlen($sql)-2) . ')';
	} else return false;
	echo '[SQL] ' . $sql . PHP_EOL;
	echo '[VALUES_CLEAN] '; var_dump($values_clean);
	
	if ($stmt = $PDO->prepare($sql)) {
		if ($stmt->execute($values_clean)) return true;
		else echo mysqli_stmt_error($stmt);
	} else echo mysqli_stmt_error($stmt);
	return false;
}
function sqlUpdate(array $columns = [], array $values = [], string $table, string $wherecolumn = '', $target = '')
{
	echo '[UPDATE COLUMNS]'; var_dump($columns);
	echo '[UPDATE VALUES]'; var_dump($values);
	
	if (empty($columns)) return false;
	if (!$table) return false;
	if (count($columns) != count($values)) return false;
	include 'connect.php';
	
	$sql = "UPDATE $table SET ";
	for($x=0;$x<count($columns);$x++)
	{
		if ($x<count($columns)-1) $sql .= "{$columns[$x]} = ?, "; // {$values[$x]}
		else $sql .= "{$columns[$x]} = ?"; //{$values[$x]}
	}
	if ($wherecolumn && $target) {
		$sql .= " WHERE $wherecolumn = '$target'";
	}
	echo '[SQL] ' . $sql . PHP_EOL;
	$value_string = '(';
	foreach ($values as $value) {
		$value_string .= "$value, ";
	}
	$value_string = substr($value_string, 0, strlen($value_string)-2) . ')';
	echo $value_string . PHP_EOL;

	if ($stmt = $PDO->prepare($sql)) {
		if ($stmt->execute($values)) return true;
		else echo mysqli_stmt_error($stmt);
	} else echo mysqli_stmt_error($stmt);
	return false;
}
function sqlDelete(string $table, string $wherecolumn = '', array $values = [], string $order = '', int|string $limit = '')
{
	include 'connect.php';
	$array = array();
	
	$sql = "DELETE FROM $table";
	if ($wherecolumn && !empty($values)) {
		$sql .= " WHERE $wherecolumn = ?";
	}
	if ($order) $sql .= " ORDER BY $order";
	if ($limit) $sql .= " LIMIT $limit";
	echo '[SQL] ' . $sql . PHP_EOL;
	$value_string = '(';
	foreach ($values as $value) {
		$value_string .= "$value, ";
	}
	$value_string = substr($value_string, 0, strlen($value_string)-2) . ')';
	echo $value_string . PHP_EOL;
	
	if ($stmt = $PDO->prepare($sql)) {
		if ($stmt->execute($values))	 return true;
		else echo mysqli_stmt_error($stmt);
	} else echo mysqli_stmt_error($stmt);
	return false;
}

function partPusher($lorhondel, $repository, $part_name, $array)
{
	$part = null;
	foreach ($array as $data) { //Create all into parts and push
		if ($attributes = json_decode(json_encode(json_validate($data)), true)) {
			if ($part = $lorhondel->factory($part_name, $attributes)) {
				if ($lorhondel->$repository->offsetGet($part->id))
					$lorhondel->$repository->pull($part->id);
				$lorhondel->$repository->push($part);
			}
		}
	}
	return $part;
}

function getCurrentPlayer($lorhondel, $user_id)
{
	if ($collection = $lorhondel->players->filter(fn($p) => $p->user_id == $user_id && $p->active == 1 )) {
		echo '[FOUND ACTIVE PLAYER]'; var_dump($collection);
		foreach ($collection as $player) //There should only be one
			return $player;
	}
	
	//No active Player part was found, so check SQL to make sure
	include 'connect.php';
	$sql = "SELECT * FROM players WHERE user_id = ? AND active = 1";
	$get = array();
	$part = null;
	if ($stmt = $PDO->prepare($sql))
		if ($stmt->execute([$user_id]))
			if ($result = $stmt->fetchAll())
				$get = $result;
	echo '[getCurrentPlayer]'; var_dump($get);
	if (! empty($get)) {
		$part = partPusher($lorhondel, 'players', '\Lorhondel\Parts\Player\Player', $get);
		echo '[getCurrentPlayer2]'; var_dump($part);
		return $part;
	} else return null;
}

function setCurrentPlayer($lorhondel, $user_id, $id)
{
	$cached = false;
	if ($collection = $lorhondel->players->filter(fn($p) => $p->user_id == $user_id && $p->active == 1 )) {
		echo '[FOUND ACTIVE PLAYER]'; var_dump($collection);
		foreach ($collection as $player) //There should only be one
			$lorhondel->players->offsetGet($player->id)->active = 0;
	}
	if ($player = $lorhondel->players->offsetGet($id) && $player->user_id == $user_id) {
		$player->active = 1;
		$cached = true;
	}
	
	include 'connect.php';
	$deactivated = false;
	$activated = false;
	$part = null;
	$sql = "UPDATE players SET active = 0 WHERE user_id = ?";
	if ($stmt = $PDO->prepare($sql))
		if ($stmt->execute([$id]))
			if ($result = $stmt->fetchAll())
				$deactivated = true; //echo "[DEACTIVATED] $user_id";
	$sql = "UPDATE players SET active = 1 WHERE user_id = ? AND id = ?";
	if ($stmt = $PDO->prepare($sql))
		if ($stmt->execute([$user_id, $id]))
			$activated = true; //echo "[ACTIVATED] $user_id/$id";
	echo '[setCurrentPlayer]'; var_dump($result);
	
	if (! $cached && $activated) {
		$get = json_validate(sqlGet(['*'], 'players', 'id', [$id], '', 1));
		$part = partPusher($lorhondel, 'players', '\Lorhondel\Parts\Player\Player', $get);
		echo '[setCurrentPlayer Part]'; var_dump($part);
	}
	
	return $player ?? $part ?? $result;
}

function getCurrentParty($lorhondel, $id)
{
	if ($part = $lorhondel->parties->filter(fn($p) => $p->player1 == $id || p->player2 == $id || p->player3 == $id || p->player4 == $id || p->player5 == $id)) {
		echo '[FOUND PARTY]'; var_dump($part);
		foreach ($part as $party) //There should only be one
			return $party;
	}
	
	//No Party part for the Player was found, so check SQL to make sure
	include 'connect.php';
	$part = null;
	$sql = "SELECT * FROM parties WHERE ? in (player1, player2, player3, player4, player5)";
	if ($stmt = $PDO->prepare($sql))
		if ($stmt->execute([$id]))
			if ($result = $stmt->fetchAll())
				$part = partPusher($lorhondel, 'parties', '\Lorhondel\Parts\Party\Party', $result);
	return $part ?? false;
}

function setCurrentParty($lorhondel, $user_id)
{
	
	
	include 'connect.php';
	//
}

function getPlayerLocation()
{
	//
}

function playerEmbed($lorhondel, $player)
{
	$embed = $lorhondel->discord->factory(\Discord\Parts\Embed\Embed::class);
	$embed->setColor(0xe1452d)
	//	->setDescription('$author_guild_name') // Set a description (below title, above fields)
	//	->setImage('https://avatars1.githubusercontent.com/u/4529744?s=460&v=4') // Set an image (below everything except footer)
		->setTimestamp()
		->setFooter('Lonhondel by ArtsyAxolotl#5128')
		->setURL('');
	if ($player->name) $embed->addFieldValues('Name', $player->name, true);
	$embed->addFieldValues('Species', $player->species, true);
	if ($player->party_id) $embed->addFieldValues('Party ID', $player->party_id, false);
	$embed	
		->addFieldValues('Health', $player->health, true)
		->addFieldValues('Attack', $player->attack, true)
		->addFieldValues('Defense', $player->defense, true)
		->addFieldValues('Speed', $player->speed, true)
		->addFieldValues('Skill Points', $player->skillpoints, true);
	if ($user = $lorhondel->discord->users->offsetGet($player->user_id)) {
		$embed->setAuthor("{$user->username} ({$user->id})", $user->avatar); // Set an author with icon
		$embed->setThumbnail("{$user->avatar}"); // Set a thumbnail (the image in the top right corner)
	}
	return $embed;
}

function partyEmbed($lorhondel, $party)
{
	echo '[CLASS]' . get_class($lorhondel->discord) . PHP_EOL;;
	$players = array();
	$players[] = $player1 = $lorhondel->players->offsetGet($party->player1);
	$players[] = $player2 = $lorhondel->players->offsetGet($party->player2);
	$players[] = $player3 = $lorhondel->players->offsetGet($party->player3);
	$players[] = $player4 = $lorhondel->players->offsetGet($party->player4);
	$players[] = $player5 = $lorhondel->players->offsetGet($party->player5);
	
	$embed = $lorhondel->discord->factory(\Discord\Parts\Embed\Embed::class);
	$embed->setColor(0xe1452d)
	//	->setDescription('$author_guild_name') // Set a description (below title, above fields)
	//	->setImage('https://avatars1.githubusercontent.com/u/4529744?s=460&v=4') // Set an image (below everything except footer)
		->setTimestamp()
		->setFooter('Lorhondel by ArtsyAxolotl#5128')                             					// Set a footer without icon
		->setURL('');                             												// Set the URL
	if ($party->name) $embed->addFieldValues('Name', $party->name, false);
	$embed->addFieldValues('Party ID', $party->id, false);
	foreach ($players as $player) {
		if ($player && $user = $lorhondel->discord->users->offsetGet($player->user_id)) {
			$embed->setAuthor("{$user->username} ({$user->id})", $user->avatar); // Set an author with icon
			if ($player->id == $party->{$party->leader}) {
				$embed->addFieldValues('Leader', $player->name ?? $player->id, true);
				$embed->setThumbnail("{$user->avatar}"); // Set a thumbnail (the image in the top right corner)
			}
		}
	}
	$inline = false;
	for ($x=0; $x<=count($players); $x++) {
		if ($players[$x]) {
			$embed->addFieldValues('Player ' . $x+1, $players[$x]->name ?? $players[$x]->id, $inline);
			$inline = true;
		}
		$x++;
	}
	return $embed;
}

$webapi = new \React\Http\HttpServer($loop, function (\Psr\Http\Message\ServerRequestInterface $request) use ($lorhondel, $discord, $stats) {
	try{
		echo '[API] ';
		$path = explode('/', $request->getUri()->getPath());
		$ver = (isset($path[1]) ? (string) strtolower($path[1]) : false); if ($ver) echo "/$ver/";
		$repository = $sub = (isset($path[2]) ? (string) strtolower($path[2]) : false); if ($repository) echo "$repository/";
		$method = $id = (isset($path[3]) ? (string) strtolower($path[3]) : false); if ($method) echo "$method/";
		$id2 = $repository2 = (isset($path[4]) ? (string) strtolower($path[4]) : false); if ($id2) echo "$id2/";
		$partial = $method2 = (isset($path[5]) ? (string) strtolower($path[5]) : false); if ($partial) echo "$partial/";
		$id3 = (isset($path[6]) ? (string) strtolower($path[6]) : false); if ($id3) echo "$id3/";
		$id4 = (isset($path[7]) ? (string) strtolower($path[7]) : false); if ($id4) echo "$id4/";
		echo PHP_EOL;
		if ($data = json_decode((string)$request->getBody())) {
			echo '[-----DATA START-----]' . PHP_EOL;
			var_dump($data);
			echo '[-----DATA END-----]' . PHP_EOL;
		}
		/*
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
		*/
		
		$lorhondelBattleground = $lorhondel->discord->getChannel(887118621065768970);
		$lorhondelBotSpam = $lorhondel->discord->getChannel(887118679697940481);
		
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
		$_304['message'] = '304: Not Modified'; //is only a valid response for GET and HEAD requests. 304s are only used for the purposes of caching
		$_304['code'] = 0;
		
		$_204 = array();
		$_204['message'] = '204: No Content'; //Put/Post/Delete request has 'succeeded' but no object needs to be returned
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
		else echo "[REMOTE_ADDR]" . $request->getServerParams()['REMOTE_ADDR'].PHP_EOL;
		//logInfo('[webapi] Request', ['path' => $path]);
		
		if ($ver != 'v' . $lorhondel::GATEWAY_VERSION) {
			$_400['message'] = '400: Bad Request. Invalid API version provided'; //Improper format or bad data
			$_400['code'] = 50001;
			return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($_400));
		}
		
		switch ($repository) { //gateway
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
		}
		switch ($sub) {
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
			case 'channel':
				if (!$id || !webapiSnow($id) || !$return = $lorhondel->discord->getChannel($id))
					return webapiFail('channel_id', $id);
				break;

			case 'guild':
				if (!$id || !webapiSnow($id) || !$return = $lorhondel->discord->guilds->offsetGet($id))
					return webapiFail('guild_id', $id);
				break;
				
			case 'bans':
				if (!$id || !webapiSnow($id) || !$guild = $lorhondel->discord->guilds->offsetGet($id))
					return webapiFail('guild_id', $id);
				$return = $guild->bans;
				break;
				
			case 'channels':
				if (!$id || !webapiSnow($id) || !$guild = $lorhondel->discord->guilds->offsetGet($id))
					return webapiFail('guild_id', $id);
				$return = $guild->channels;
				break;
				
			case 'members':
				if (!$id || !webapiSnow($id) || !$guild = $lorhondel->discord->guilds->offsetGet($id))
					return webapiFail('guild_id', $id);
				$return = $guild->members;
				break;
				
			case 'emojis':
				if (!$id || !webapiSnow($id) || !$guild = $lorhondel->discord->guilds->offsetGet($id))
					return webapiFail('guild_id', $id);
				$return = $guild->emojis;
				break;
			
			case 'invites':
				if (!$id || !webapiSnow($id) || !$guild = $lorhondel->discord->guilds->offsetGet($id))
					return webapiFail('guild_id', $id);
				$return = $guild->invites;
				break;
			
			case 'roles':
				if (!$id || !webapiSnow($id) || !$guild = $lorhondel->discord->guilds->offsetGet($id))
					return webapiFail('guild_id', $id);
				$return = $guild->roles;
				break;

			case 'guildMember':
			case 'member':
				if (!$id || !webapiSnow($id) || !$guild = $lorhondel->discord->guilds->offsetGet($id))
					return webapiFail('guild_id', $id);
				if (!$id2 || !webapiSnow($id2) || !$return = $guild->members->offsetGet($id2))
					return webapiFail('user_id', $id2);
				break;

			case 'user':
				if (!$id || !webapiSnow($id) || !$return = $lorhondel->discord->users->offsetGet($id)) {
					return webapiFail('user_id', $id);
				}
				break;

			case 'userName':
				if (!$id || !$return = $lorhondel->discord->users->get('name', $id))
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
				if (!$id || !webapiSnow($id) || !$return = $lorhondel->discord->user->fetch($id))
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
				if ($user = $lorhondel->discord->users->offsetGet($id)) { //Search all guilds the bot is in and check if the user id exists as a guild owner
					foreach ($lorhondel->discord->guilds as $guild) {
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
				if (!$user = $lorhondel->discord->users->offsetGet($id)) {
					$lorhondel->discord->users->fetch($id)->done(
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
				$promise = $lorhondel->discord->users->fetch($idarray[0])->then(function ($user) use (&$return) {
				  $return[$user->id] = $user->avatar;
				});
				
				for ($i = 1; $i < count($idarray); $i++) {
				  $promise->then(function () use (&$return, $idarray, $i, $discord) {
					return $lorhondel->discord->users->fetch($idarray[$i])->then(function ($user) use (&$return) {
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
		
		$repositories = [
			'players' => [
				'part_name' => '\Lorhondel\Parts\Player\Player',
				'part_name_short' => 'Player',
				'allowed_methods' => [
					['method' => 'get', 'privileged' => false, 'privileged_endpoints' => [null, 'all', 'freshen']],
					['method' => 'fresh', 'privileged' => false, 'privileged_endpoints' => []],
					['method' => 'put', 'privileged' => true, 'privileged_endpoints' => []],
					['method' => 'patch', 'privileged' => true, 'privileged_endpoints' => []],
					['method' => 'post', 'privileged' => true, 'privileged_endpoints' => []],
					['method' => 'delete', 'privileged' => true, 'privileged_endpoints' => []],
				],
			],
			'parties' => [
				'part_name' => '\Lorhondel\Parts\Party\Party',
				'part_name_short' => 'Party',
				'allowed_methods' => [
					['method' => 'get', 'privileged' => false, 'privileged_endpoints' => [null, 'all', 'freshen']],
					['method' => 'fresh', 'privileged' => false, 'privileged_endpoints' => []],
					['method' => 'put', 'privileged' => true, 'privileged_endpoints' => []],
					['method' => 'patch', 'privileged' => true, 'privileged_endpoints' => []],
					['method' => 'post', 'privileged' => true, 'privileged_endpoints' => []],
					['method' => 'delete', 'privileged' => true, 'privileged_endpoints' => []],
				],
			],
			'battles' => [
				'part_name' => '\Lorhondel\Parts\Battle\Battle',
				'part_name_short' => 'Battle',
				'allowed_methods' => [
					['method' => 'get', 'privileged' => false, 'privileged_endpoints' => [null, 'all', 'freshen']],
					['method' => 'fresh', 'privileged' => false, 'privileged_endpoints' => []],
					['method' => 'put', 'privileged' => true, 'privileged_endpoints' => []],
					['method' => 'patch', 'privileged' => true, 'privileged_endpoints' => []],
					['method' => 'post', 'privileged' => true, 'privileged_endpoints' => []],
					['method' => 'delete', 'privileged' => true, 'privileged_endpoints' => []],
				],
			],
			
		];

		//$collection = false;
		$part_name = '';
		$target_repository = null;
		$target_method = null;
		$target_id2 = null;
		foreach ($repositories as $key => $value) { //Verify permissions
			if ($repository == $key) {
				//$collection = true;
				$target_repository = $key;
				$part_name = $repositories[$key]['part_name'];
				foreach($repositories[$key]['allowed_methods'] as $methods) {
					if ($method == $methods['method']) {
						if ($whitelisted || !$methods['privileged'] || in_array($id2, $methods['privileged_endpoints'])) {
							echo "[ALLOWED METHOD/ENDPOINT] $method/$id2" . PHP_EOL; 
							$target_method = $method;
							$target_id2 = $id2;
						} else return new \GuzzleHttp\Psr7\Response(403, ['Content-Type' => 'application/json'], json_encode($_403));
					}
				}
			}
		}
		if (!$target_method) return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($_400));
		
		$requires_part = ['put', 'patch', 'post'];
		$has_part = false;
		$part = null;
		$attributes = [];
		//Catch endpoint-related errors for parts
		if (in_array($method, $requires_part)) {
			echo '[METHOD/ENDPOINT REQUIRES PART]' . PHP_EOL;
			//Format the data appropriately
			foreach ($data as $key => $value) {
				if ($value === null)
					unset($data->$key);
				if ($value === false)
					$data->$key = '0';
			}
			echo '[DATA DUMP]' . PHP_EOL; var_dump($data);
			//Attempt to build the part
			$data = json_validate($data);
			if ($attributes = json_decode(json_encode($data), true)) {
				echo '[ATTRIBUTES]'; var_dump($attributes);
				if ($part = $lorhondel->factory($part_name, $attributes)) $has_part = true;
				else return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($part));
			} else {
				echo '[ATTRIBUTES FAIL]' . PHP_EOL;
				return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($_400));
			}
		}
		
		
		$allowed_properties = [];
		if ($part) {
			$allowed_properties = array_merge(['*'], $part->getFillableAttributes());
		}
		
		if ($has_part) { //Catch method-related errors (Process collection request?)
			//Remove any attributes that weren't provided in the part
			$fillable_attributes = array();
			foreach ($part->getFillableAttributes() as $attribute) {
				if (property_exists($data, $attribute) && $data->$attribute !== null) {
					echo '[DATA->ATTRIBUTE]'; var_dump($data->$attribute);
					$fillable_attributes[] = $attribute;
				}
			}
			echo '[FILLABLE ATTRIBUTES]'; var_dump ($fillable_attributes);
			
			if ($target_method == 'patch') {
				if (empty($get = sqlGet(['*'], $repository, 'id', [$id2], '', 1)))
					return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($part)); //Data does not exist to update
				elseif ($update = sqlUpdate($fillable_attributes, array_values($attributes), $repository, 'id', $id2)) {
					$get = sqlGet(['*'], $repository, 'id', [$id2], '', 1);
					foreach ($get as $array) {
						$part = $lorhondel->factory($part_name, $array);
						if ($part->id) {
							if ($lorhondel->$repository->offsetGet($part->id))
								$lorhondel->$repository->pull($part->id);
							$lorhondel->$repository->push($part);
						}
					}
					return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($get));
				} else return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($_400)); //The data provided is either missing or didn't get passed to the SQL method
			}
			elseif ($target_method == 'post' || $target_method == 'put') { //Put works here because we should never be creating duplicates of objects or reusing the same id for multiple objects
				if ($id2 && is_numeric(($id2))) {
					if (!empty($return = sqlGet(['*'], $repository, 'id', [$id2], '', 1)))
						return new \GuzzleHttp\Psr7\Response(204, ['Content-Type' => 'application/json'], json_encode($part));
					else {
						if (sqlCreate($repository, $data)) {
							$get = sqlGet(['*'], $repository, 'id', [$id2], '', 1);
							foreach ($get as $array) {
								$part = $lorhondel->factory($part_name, $array);
								if ($part->id) {
									if (!$lorhondel->$repository->offsetGet($part->id))
										$lorhondel->$repository->push($part);
									else echo '[EXISTS] ' . $data->id . PHP_EOL;
								}
							}
							return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($get));
						} else return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($_400)); //The data provided is either missing or didn't get passed to the SQL method
					}
				} else {
					if (sqlCreate($repository, $data)) //Create a new part without using an ID
						echo '[CREATED PART WITHOUT ID]' . PHP_EOL; //$lorhondel->$repository->freshen(); //attempt to retrieve the new part by freshening the repository
					return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($data));
				}
			}
		}
		if ($target_method == 'get' || $target_method == 'fetch') {
			if ($id2 && is_numeric(($id2))) {
				if (in_array($partial, $allowed_properties)) {
					if (empty($return = sqlGet([$partial], $repository, 'id', [$id2], '', 1)))
						return new \GuzzleHttp\Psr7\Response(404, ['Content-Type' => 'application/json'], json_encode($_404));
					else {
						//NYI
						return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($return ?? $_200));
					}
				} elseif (!$partial) {
					if (!empty($array = sqlGet(['*'], $repository, 'id', [$id2], '', 1))) {
						foreach ($array as $data) //Create all into parts and push
						if ($attributes = json_decode(json_encode(json_validate($array)), true)) {
							if ($part = $lorhondel->factory($part_name, $attributes[$id2])) {
								if ($lorhondel->$repository->offsetGet($part->id))
									$lorhondel->$repository->pull($part->id);
								$lorhondel->$repository->push($part);
							}
						}
						return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($part ?? $_200));
					} else return new \GuzzleHttp\Psr7\Response(404, ['Content-Type' => 'application/json'], json_encode($_404));
				}else return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($_400));
			} else { //all
				$array = json_validate(sqlGet(['*'], $repository, '', [], '', '')); //array
				foreach ($array as $data) //Create all into parts and push
					if ($attributes = json_decode(json_encode(json_validate($data)), true)) {
						if ($part = $lorhondel->factory($part_name, $attributes)) {
							if ($lorhondel->$repository->offsetGet($part->id))
								$lorhondel->$repository->pull($part->id);
							$lorhondel->$repository->push($part);
						}
					}
				return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($array)); //return isn't being received?
			}
		}
		elseif ($target_method == 'delete') { echo '[DELETE]' . PHP_EOL;
			if (empty($return = sqlGet(['*'], $repository, 'id', [$id2], '', 1)))
				return new \GuzzleHttp\Psr7\Response(204, ['Content-Type' => 'application/json'], json_encode($_204)); //Data does not exist to delete
			elseif (sqlDelete($repository, 'id', [$id2], '', 1)) {
				if ($lorhondel->$repository->offsetGet($id2))
					$lorhondel->$repository->pull($id2);
				return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($part ?? $lorhondel->$repository->pull($id2) ?? $id2));
			} else return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($_400)); //The data provided is either missing or didn't get passed to the SQL method
		}
		elseif ($target_method == 'fresh') {
			if ($id2 && is_numeric(($id2))) {
				if (!empty($data = sqlGet(['*'], $repository, 'id', [$id2], '', 1))) //Recreate the part with data from SQL
					//if ($attributes = json_decode(json_encode(json_validate($data)), true))
						if ($attributes = json_decode(json_encode(json_validate($data)), true))
						if ($part = $lorhondel->factory($part_name, $attributes[$id2])) {
							if ($lorhondel->$repository->offsetGet($id2))
								$lorhondel->$repository->pull($id2);
							$lorhondel->$repository->push($part);
							return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($part));
						} else return new \GuzzleHttp\Psr7\Response(500, ['Content-Type' => 'application/json'], json_encode($_5xx)); //Nothing from SQL should be throwing an error, so this is a problem!
				else return new \GuzzleHttp\Psr7\Response(204, ['Content-Type' => 'application/json'], json_encode($part));
			} else return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($_400));
		}

		$return = $_404;
		return new \GuzzleHttp\Psr7\Response(404, ['Content-Type' => 'application/json'], json_encode($return));
		/*if ($return)*/ return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($return));
	}
	catch (Exception $e) {
		echo '[ERROR]' . PHP_EOL; var_dump($e);
		$return = $_5xx;
		return new \GuzzleHttp\Psr7\Response(500, ['Content-Type' => 'application/json'], json_encode($return));
	}
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
	echo '[WEBAPI ERROR] ' . $e->getMessage() . PHP_EOL;
	var_dump($e);
});

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