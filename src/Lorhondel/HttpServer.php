<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel;

class HttpServer
{
	public function __construct($lorhondel, $socket)
    {
		$this->lorhondel = $lorhondel;
		$this->socket = $socket;
		
        $this->webapi = new \React\Http\HttpServer($lorhondel->getLoop(), function (\Psr\Http\Message\ServerRequestInterface $request) use ($lorhondel) {
			$discord = $lorhondel->discord;
			try {
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
						//if ($method != 'bot') break;
						//if ($id2 != '@me') break;
						$return = array();
						return new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($return));
						break;
					case 'gateway':
						if ($method != 'bot') break;
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
						break;
				}
				switch ($sub) {
					case 'ping':
						echo '[PING]' . PHP_EOL;
						$lorhondelBotSpam->sendMessage('Pong!');
						$return = 'Pong!';
						break;
					/*case 'stats':
						if ($embed = $stats->handle()) {
							$lorhondelBotSpam->sendEmbed($embed);
							$return = $embed;
						} else return webapiFail('stats', $stats);
						break;*/
					case 'channel':
						if (! $id || !webapiSnow($id) || ! $return = $lorhondel->discord->getChannel($id))
							return webapiFail('channel_id', $id);
						break;

					case 'guild':
						if (! $id || !webapiSnow($id) || ! $return = $lorhondel->discord->guilds->offsetGet($id))
							return webapiFail('guild_id', $id);
						break;
						
					case 'bans':
						if (! $id || !webapiSnow($id) || ! $guild = $lorhondel->discord->guilds->offsetGet($id))
							return webapiFail('guild_id', $id);
						$return = $guild->bans;
						break;
						
					case 'channels':
						if (! $id || !webapiSnow($id) || ! $guild = $lorhondel->discord->guilds->offsetGet($id))
							return webapiFail('guild_id', $id);
						$return = $guild->channels;
						break;
						
					case 'members':
						if (! $id || !webapiSnow($id) || ! $guild = $lorhondel->discord->guilds->offsetGet($id))
							return webapiFail('guild_id', $id);
						$return = $guild->members;
						break;
						
					case 'emojis':
						if (! $id || !webapiSnow($id) || ! $guild = $lorhondel->discord->guilds->offsetGet($id))
							return webapiFail('guild_id', $id);
						$return = $guild->emojis;
						break;
					
					case 'invites':
						if (! $id || !webapiSnow($id) || ! $guild = $lorhondel->discord->guilds->offsetGet($id))
							return webapiFail('guild_id', $id);
						$return = $guild->invites;
						break;
					
					case 'roles':
						if (! $id || !webapiSnow($id) || ! $guild = $lorhondel->discord->guilds->offsetGet($id))
							return webapiFail('guild_id', $id);
						$return = $guild->roles;
						break;

					case 'guildMember':
					case 'member':
						if (! $id || !webapiSnow($id) || ! $guild = $lorhondel->discord->guilds->offsetGet($id))
							return webapiFail('guild_id', $id);
						if (! $id2 || !webapiSnow($id2) || ! $return = $guild->members->offsetGet($id2))
							return webapiFail('user_id', $id2);
						break;

					case 'user':
						if (! $id || !webapiSnow($id) || ! $return = $lorhondel->discord->users->offsetGet($id)) {
							return webapiFail('user_id', $id);
						}
						break;

					case 'userName':
						if (! $id || ! $return = $lorhondel->discord->users->get('name', $id))
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
						if (! $id || !webapiSnow($id) || ! $return = $lorhondel->discord->user->fetch($id))
							return webapiFail('user_id', $id);
						break;

					case 'owner':
						if (substr($request->getServerParams()['REMOTE_ADDR'], 0, 6) != '10.0.0') {
							echo '[REJECT]' . $request->getServerParams()['REMOTE_ADDR'] . PHP_EOL;
							return new \GuzzleHttp\Psr7\Response(501, ['Content-Type' => 'text/plain'], 'Reject'.PHP_EOL);
						}
						if (! $id || !webapiSnow($id))
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
						if (! $id || !webapiSnow($id)) {
							return webapiFail('user_id', $id);
						}
						if (! $user = $lorhondel->discord->users->offsetGet($id)) {
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
						//if (! $return) return new \GuzzleHttp\Psr7\Response(($id ? 404 : 400), ['Content-Type' => 'text/plain'], ('').PHP_EOL);
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
					'pets' => [
						'part_name' => '\Lorhondel\Parts\Pet\Pet',
						'part_name_short' => 'Pet',
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
					'votes' => [
						'part_name' => 'Lorhondel\Parts\Vote\Vote',
						'part_name_short' => 'Vote',
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
								if ($whitelisted || ! $methods['privileged'] || in_array($id2, $methods['privileged_endpoints'])) {
									echo "[ALLOWED METHOD/ENDPOINT] $method/$id2" . PHP_EOL; 
									$target_method = $method;
									$target_id2 = $id2;
								} else return new \GuzzleHttp\Psr7\Response(403, ['Content-Type' => 'application/json'], json_encode($_403));
							}
						}
					}
				}
				if (! $target_method) return new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'], json_encode($_400));
				
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
						if (property_exists($data, $attribute)/* && $data->$attribute !== null*/) {
							echo '[DATA->ATTRIBUTE]'; var_dump($data->$attribute);
							$fillable_attributes[] = $attribute;
						}
					}
					foreach ($part->getFillableAttributes() as $attribute) {
						if (!property_exists($data, $attribute)/* && $data->$attribute !== null*/) {
							//Add the missing as null
							$fillable_attributes[] = $attribute;
							$attributes[$attribute] = null;
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
											if (! $lorhondel->$repository->offsetGet($part->id))
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
						} elseif (! $partial) {
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
				elseif ($target_method == 'delete') { echo '[DELETE RESPONSE]' . PHP_EOL;
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
		$this->webapi->on('error', function ($e) {
			/*
			logDebug('[webapi] Error', [
				'msg' => $e->getMessage(),
				'prv' => ($e->getPrevious() ? $e->getPrevious()->getMessage() : null)
			]);
			*/
			echo '[WEBAPI ERROR] ' . $e->getMessage() . PHP_EOL;
			var_dump($e);
		});
		$this->webapi->listen($this->socket);
    }

	private function webapiFail($part, $id)
	{
		//logInfo('[webapi] Failed', ['part' => $part, 'id' => $id]);
		//return new \GuzzleHttp\Psr7\Response(($id ? 404 : 400), ['Content-Type' => 'text/plain'], ($id ? 'Invalid' : 'Missing').' '.$part.PHP_EOL);
		$return = array();
		$return['message'] = '404: Not Found';
		$return['code'] = 0;
		return new \GuzzleHttp\Psr7\Response(404, ['Content-Type' => 'application/json'], json_encode($return));
	}

	private function webapiSnow($string)
	{
		return preg_match('/^[0-9]{16,18}$/', $string);
	}
	
}
