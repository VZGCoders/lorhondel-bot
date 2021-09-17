<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */
 
include_once 'autoload.php';

//echo '[MESSAGE]' . PHP_EOL;
if (is_null($message) || empty($message)) return; //An invalid message object was passed
if (is_null($message->content)) return; //Don't process messages without content
if ($message["webhook_id"]) return; //Don't process webhooks

$message_content = $message->content;
$message_id = $message->id;
$message_content_lower = strtolower($message_content);

/*
*********************
*********************
Required includes
*********************
*********************
*/

include_once "custom_functions.php";

/*
*********************
*********************
Options
*********************
*********************
*/

$command_symbol = ';'; //Author must prefix text with this to use commands

/*
*********************
*********************
Load author data from message
*********************
*********************
*/

$author	= $message->author; //Member OR User object
//echo "Author class: " . get_class($author) . PHP_EOL;
if (get_class($author) == "Discord\Parts\User\Member") {
	$author_user = $author->user;
	$author_member = $author;
} else {
	$author_user = $author;
	$author_member = null;
}
$user_createdTimestamp											= $author_user->createdTimestamp();
$author_channel 												= $message->channel;
$author_channel_id												= $author_channel->id;
$author_channel_class											= get_class($author_channel);
$is_dm															= false; //echo "author_channel_class: " . $author_channel_class . PHP_EOL;
//echo "[CLASS] " . get_class($message->author) . PHP_EOL;
//echo "$author_check <@$author_id>: {$message_content}", PHP_EOL;
//$author_webhook = $author_user->webhook;
if (is_null($message->channel->guild_id)) $is_dm = true; //True if direct message

$author_username 												= $author_user->username; 										//echo "author_username: " . $author_username . PHP_EOL;
$author_discriminator 											= $author_user->discriminator;									//echo "author_discriminator: " . $author_discriminator . PHP_EOL;
$author_id 														= $author_user->id;												//echo "author_id: " . $author_id . PHP_EOL;
$author_avatar 													= $author_user->avatar;											//echo "author_avatar: " . $author_avatar . PHP_EOL;
$author_check 													= "$author_username#$author_discriminator"; 					//echo "author_check: " . $author_check . PHP_EOL;

//echo '[TEST]' . __FILE__ . ':' . __LINE__ . PHP_EOL;
if (!$is_dm) {
	$author_guild 												= $message->channel->guild; 									
	$author_guild_id 											= $author_guild->id; 											//echo "author_guild_id: " . $author_guild_id . PHP_EOL;
	$guild_folder = "\\guilds\\".$author_guild_id;
	$author_guild_avatar 										= $author_guild->icon;
	$author_guild_roles 										= $author_guild->roles;
	$author_channel 											= $message->channel;
	$author_channel_id											= $author_channel->id; 											//echo "author_channel_id: " . $author_channel_id . PHP_EOL;
	if (is_null($author_member)) return; //Probably a bot or webhook without a member object
	if ($author_member) $author_member_roles = $author_member->roles;
} else {
	return;
}

$creator = false;
	if ($author_id == 116927250145869826) $creator = true;

switch ($author_guild_id) {
	case '887118559833112638': //Lorhonderkind
		break;
	default:
		return;
}

if (str_starts_with($message_content, $command_symbol)) //Commands
{
	$message_content = substr($message_content, 1);
	$message_content_lower = substr($message_content_lower, 1);
	if ($creator) { //Debug commands
		switch($message_content_lower) {
			case 'ping':
				$message->reply('Pong!');
				break;
			case 'factory':
				$snowflake = \Lorhondel\generateSnowflake(time(), 0, 0, count($lorhondel->players));
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
				$message->reply($part);
				break;
			case 'players':
				$message->reply(json_encode($lorhondel->players));
				break;
			case 'get':
				$url = "http://lorhondel.valzargaming.com/api/v1/players/get/116927250145869826/";
				$browser->post($url, ['Content-Type' => 'application/json'], json_encode('116927250145869826'))->then(
					function (Psr\Http\Message\ResponseInterface $response) use ($lorhondel) {
						echo '[RESPONSE]' . PHP_EOL;
						print_r($response->getBody());
					},
					function ($error) {
						var_dump($error);
					}
				);
				break;
			case 'post':
				echo '[POST]' . PHP_EOL;
				$snowflake = \Lorhondel\generateSnowflake(time(), 0, 0, count($lorhondel->players));
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
				//$result = sqlCreate('players', json_encode($part));
				//$lorhondel->players->save($part); //Use $Browser instead, this is currently broken
				$url = "http://lorhondel.valzargaming.com/api/v1/players/post/{$part->id}/";
				$browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then(
					function (Psr\Http\Message\ResponseInterface $response) use ($lorhondel, $message, $part) {
						/*
						if ((string)$response->getBody() == json_encode($part)) {
							if ($response->getStatusCode() == 200) {
								echo '[PUSH] '; var_dump($lorhondel->players->push($part));
							} elseif ($response->getStatusCode() == 204) {
								if (!$old_part = $lorhondel->players->offsetGet($part->id))
									echo '[PUSH] '; var_dump($lorhondel->players->push($part));
							}
						}
						*/
					},
					function ($error) {
						var_dump($error);
					}
				);
				break;
			case 'save':
				echo '[save]' . PHP_EOL;
				$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
					'id' => 116927250145869826,
					'user_id' => 116927250145869826,
					'species' => 'Elarian', //Elarian, Manthean, Noldarus, Veias, Jedoa
					'health' => 0,
					'attack' => 1,
					'defense' => 2,
					'speed' => 3,
					'skillpoints' => 4,
				]);
				//$result = sqlCreate('players', json_encode($part));
				//$lorhondel->players->save($part); //Use $Browser instead, this is currently broken
				$url = "http://lorhondel.valzargaming.com/api/v1/players/post/{$part->id}/";
				$browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
					function (Psr\Http\Message\ResponseInterface $response) use ($lorhondel, $message, $part) {
						/*
						$message->reply((string)$response->getBody() . json_encode($part));
						if ((string)$response->getBody() == json_encode($part)) {
							if ($response->getStatusCode() == 200) { //Newly created in SQL
								echo '[PUSH] '; var_dump($lorhondel->players->push($part));
							} elseif ($response->getStatusCode() == 204) { //id already exists in SQL
								if (!$old_part = $lorhondel->players->offsetGet($part->id))
									echo '[CREATED] '; $part->created = true;
									echo '[PUSH] '; var_dump($lorhondel->players->push($part));
							}
						}
						*/
					},
					function ($error) {
						var_dump($error);
					}
				);
				break;
			case 'delete':
				echo '[delete]' . PHP_EOL;
				$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
					'id' => 116927250145869826,
					'user_id' => 116927250145869826,
					'species' => 'Elarian', //Elarian, Manthean, Noldarus, Veias, Jedoa
					'health' => 0,
					'attack' => 1,
					'defense' => 2,
					'speed' => 3,
					'skillpoints' => 4,
				]);
				$url = "http://lorhondel.valzargaming.com/api/v1/players/delete/{$part->id}/";
				$browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->done( //Make this a function
					function (Psr\Http\Message\ResponseInterface $response) use ($lorhondel, $message, $part) {
						echo '[DELETE] '; var_dump($lorhondel->players->offsetUnset($part->id)); 
						var_dump($lorhondel->players);
						
					},
					function ($error) {
						var_dump($error);
					}
				);
				break;
		}
	}
}