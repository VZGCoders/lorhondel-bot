<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */
 
namespace Lorhondel;

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
				return $message->reply('Pong!');
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
			case 'characters':
				$characters = $lorhondel->players->filter(fn($p) => $p->user_id == $message->author->user->id);
				return $message->reply(json_encode($characters));
				break;
			case 'players':
				return $message->reply(json_encode($lorhondel->players));
				break;
			case 'parties':
				return $message->reply(json_encode($lorhondel->parties));
				break;
			case 'getcurrentplayer':
				$player = getCurrentPlayer($lorhondel, $author_id);
				if ($player) return $message->channel->sendEmbed(playerEmbed($lorhondel, getCurrentPlayer($lorhondel, $author_id)));
				else return $message->reply('No players found!');
				break;
			case 'getcurrentparty':
				if (! $player = getCurrentPlayer($lorhondel, $author_id))
					return $message->reply('No players found!');
				if (! $party = getCurrentParty($lorhondel, $player->id))
					return $message->reply('No party found!');
				return $message->channel->sendEmbed(partyEmbed($lorhondel, $party));
				break;
			case str_contains($message_content_lower, 'setcurrentplayer '):
				$id = trim(str_replace('setcurrentplayer ', '', $message_content_lower));
				if (is_numeric($id) && $player = $lorhondel->players->offsetGet($id))
					return $message->reply('Result: ' . (setCurrentPlayer($lorhondel, $author_id, $id) ?? 'None'));
				else return $message->reply('Invalid input!');
				break;
			case 'playersfreshen':
				return $lorhondel->players->freshen();
				break;
			case 'part':
				$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
					'id' => 116927250145869826,
					'user_id' => 116927250145869826,
					'species' => 'Elarian', //Elarian, Manthean, Noldarus, Veias, Jedoa
					'health' => 888,
					'attack' => 888,
					'defense' => 888,
					'speed' => 888,
					'skillpoints' => 888,
				]);
				$return = sqlGet(['*'], 'players', 'id', [$part->id], '', 1);
				echo '[RETURN]'; var_dump($return);
				foreach ($return as $data) {
					$part = $lorhondel->factory('\Lorhondel\Parts\Player\Player', $data);
					echo '[PART]';  var_dump($part);
				}
				return;
				break;
			case 'get':
				$url = Lorhondel\Http::BASE_URL . '/players/get/116927250145869826/';
				return $browser->post($url, ['Content-Type' => 'application/json'], json_encode('116927250145869826'))->then(
					function (Psr\Http\Message\ResponseInterface $response) use ($lorhondel) {
						echo '[RESPONSE]' . PHP_EOL;
						print_r($response->getBody());
					},
					function ($error) {
						var_dump($error);
					}
				);
				break;
			case 'patch':
				$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
					'id' => 116927250145869826,
					'user_id' => 116927250145869826,
					'species' => 'Elarian', //Elarian, Manthean, Noldarus, Veias, Jedoa
					'health' => 888,
					'attack' => 888,
					'defense' => 888,
					'speed' => 888,
					'skillpoints' => 888,
				]);
				echo '[PART]' . var_dump($part);
				return $lorhondel->players->save($part);
				break;
			case 'post':
				echo '[POST]' . PHP_EOL;
				$snowflake = \Lorhondel\generateSnowflake(time(), 0, 0, count($lorhondel->players));
				$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
					'id' => $snowflake,
					'user_id' => 116927250145869826,
					'party_id' => null,
					'active' => false,
					'species' => 'Elarian', //Elarian, Manthean, Noldarus, Veias, Jedoa
					'health' => 0,
					'attack' => 1,
					'defense' => 2,
					'speed' => 3,
					'skillpoints' => 4,
				]);
				return $lorhondel->players->save($part); //Use $Browser instead, this is currently broken
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
				return $lorhondel->players->save($part);
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
				return $lorhondel->players->delete($part);
				break;
			case 'stats':
				if ($embed = $stats->handle())
					return $message->channel->sendEmbed($embed);
				break;
			case 'discord':
				return $message->reply(get_class($lorhondel->discord));
				break;
			case 'create party':
				if (! empty($collection = $lorhondel->players->filter(fn($p) => $p->user_id == $author_id && $p->active == 1 ))) {
					echo '[COLLECTION PARTY]'; var_dump($collection);
					foreach ($collection as $player) {
						if (property_exists($player, 'timer')) return $message->reply('Please wait for the previous task to finish!');
						if ($player instanceof \Lorhondel\Parts\Player\Player) {
							if (! $player->party_id) {
								$lorhondel->parties->save($lorhondel->factory(\Lorhondel\Parts\Party\Party::class, ['player1' => $player->id]));
								$lorhondel->parties->freshen();
								$message->react("⏰");
								$timer = $lorhondel->getLoop()->addTimer(3, 
									function($result) use ($lorhondel, $message, $player) {
										unset($player->timer);
										$message->react("👍");
										if (! empty($collection = $lorhondel->parties->filter(fn($p) => $p->player1 == $player->id))) {
											echo '[COLLECTION]'; var_dump($collection);
											foreach ($collection as $party) { //There should only be one
												$player->party_id = $party->id;
												echo '[COLLECTION PLAYER]'; var_dump($player);
												$lorhondel->players->save($player); //This is not saving the party_id and user_id
												return $message->reply('Party created and assigned!');
											}
										} else return $message->reply('Unable to locate party part!');
									}
								);
								$player->timer = $timer;
								return;
							} else return $message->reply('You must leave your current party first!');
						} else return $message->reply('No players found!');
					}
				} else return $message->reply('No active players found!');
				break;
		}
	}
}