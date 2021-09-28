<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */
 
namespace Lorhondel;

if (is_null($message) || empty($message)) return; //An invalid message object was passed
if (is_null($message->content)) return; //Don't process messages without content
if ($message["webhook_id"]) return; //Don't process webhooks

$message_content = $message->content;
$message_id = $message->id;
$message_content_lower = strtolower($message_content);
if (!str_starts_with($message_content_lower, $lorhondel->command_symbol)) return;
$message_content = substr($message_content, strlen($lorhondel->command_symbol));
$message_content_lower = substr($message_content_lower, strlen($lorhondel->command_symbol));

/*
*********************
*********************
Load author data from message
*********************
*********************
*/

$author	= $message->author; //Member OR User object
if (get_class($author) == "Discord\Parts\User\Member") {
	$author_user = $author->user;
	$author_member = $author;
} else {
	$author_user = $author;
	$author_member = null;
	return; //Probably a bot or webhook without a member object
}

$user_createdTimestamp										= $author_user->createdTimestamp();
$author_channel 											= $message->channel;
$author_channel_id											= $author_channel->id;
$author_channel_class										= get_class($author_channel);
$is_dm = false;
if (is_null($message->channel->guild_id)) {
	return; //We shouldn't be processing direct messages right now
	$is_dm = true;
}

$author_username 											= $author_user->username;
$author_discriminator 										= $author_user->discriminator;
$author_id 													= $author_user->id;
$author_avatar 												= $author_user->avatar;
$author_check 												= $author_username.'#'.$author_discriminator;

$author_guild 												= $message->channel->guild;
$author_guild_id 											= $author_guild->id;
$author_guild_avatar 										= $author_guild->icon;
$author_guild_roles 										= $author_guild->roles;
$author_channel 											= $message->channel;
$author_channel_id											= $author_channel->id;

$author_member_roles = $author_member->roles;

/*
*********************
*********************
Process Lorhondel-related messages
*********************
*********************
*/
if ($author_guild_id != '887118559833112638') return;

$creator = false;
if ($author_id == 116927250145869826) $creator = true;

if(! $lorhondel->server) return;
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
				function ($response) use ($lorhondel) {
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
			return $lorhondel->players->save($part);
			break;
		case 'save':
			echo '[SAVE]' . PHP_EOL;
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
		case '__create party':
			if (count($collection = $lorhondel->players->filter(fn($p) => $p->user_id == $author_id && $p->active == 1 ))>0) {
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
											$lorhondel->players->save($player);
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
		default:
			break;
	}
}

echo '[LORHONDEL MESSAGE] ' . $author_id . PHP_EOL;
if ($player = getCurrentPlayer($lorhondel, $author_id))
	$party = getCurrentParty($lorhondel, $player->id);

if (str_starts_with($message_content_lower, 'player')) {
	$message_content_lower = trim(substr($message_content_lower, 6));
	echo "player message_content_lower: `$message_content_lower`" . PHP_EOL;
	if (str_starts_with($message_content_lower, 'create')) {
		/*
		if (count($collection = $lorhondel->players->filter(fn($p) => $p->user_id == $author_id && $p->active == 1 ))>0) {
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
		*/
	}
	elseif (str_starts_with($message_content_lower, 'activate')) {
		$name = $id = $message_content_lower = trim(substr($message_content_lower, 8));
		
		$collection = array();
		if (is_numeric($message_content_lower)) {
			$collection = $lorhondel->players->filter(fn($p) => $p->user_id == $author_id && $p->id == $id);
		} elseif($message_content_lower) {
			$collection = $lorhondel->players->filter(fn($p) => $p->user_id == $author_id && $p->name == $name);
		} else return $message->reply('Invalid format! Please include the ID or name of the Player you want to activate.');
		if (count($collection) > 0) {
			if (count($collection2 = $lorhondel->players->filter(fn($p) => $p->user_id == $author_id && $p->active == 1))>0) {
				foreach ($collection2 as $old_player) { //There should only be one
					$old_player->active = 0;
					$lorhondel->players->save($old_player);
				}
			}
			foreach ($collection as $player) { //There should only be one
				$player->active = 1;
				$lorhondel->players->save($player);
			}
			return $message->reply("Player $id is now your active player!");
		} else return $message->reply("You do not have any players with either ID or Name matching $id!");
		return;
	}
	elseif (is_numeric($message_content_lower)) {
		if ($target_player = $lorhondel->players->offsetGet($message_content_lower) ?? getCurrentPlayer($lorhondel, $message_content_lower))
			return $message->channel->sendEmbed(playerEmbed($lorhondel, $target_player));
		else return $message->reply("Unable to locate either a Player or relevant Discord account with a Player!");
	}
	/*
	*********************
	*********************
	These commands require an active player
	*********************
	*********************
	*/
	if (! $player) return $message->reply('Please create a player or activate one first!');
	if ($message_content_lower == 'looking') {
		if ($player->party_id === null) {
			$player->looking = ! $player->looking;
			switch($player->looking) {
				case false:
					$message->reply('Player ' . ($player->name ?? $player->id) . ' is no longer looking for a party!');
					break;
				case true:
					$message->reply('Player ' . ($player->name ?? $player->id) . ' is now looking for a party!');
					break;
			}
			$lorhondel->players->save($player);
		} else return $message->reply('Please leave your current party before listing yourself as looking for a new one!');
	} elseif (! $message_content_lower) {
		return $message->channel->sendEmbed(playerEmbed($lorhondel, $player));
	}
}

if (str_starts_with($message_content_lower, 'party')) {
	$message_content_lower = trim(substr($message_content_lower, 5));
	
	if (is_numeric($message_content_lower)) {
		if ($target_party = $lorhondel->parties->offsetGet($message_content_lower))
			return $message->channel->sendEmbed(partyEmbed($lorhondel, $target_party));
		else return $message->reply("Unable to locate a party with ID $message_content_lower!");
	}
	
	/*
	*********************
	*********************
	These commands require an active player
	*********************
	*********************
	*/
	if (! $player) return $message->reply('Please create a player or activate one first!');
	if (str_starts_with($message_content_lower, 'create')) {
		if (! $party) {
			if (property_exists($player, 'timer')) return $message->reply('Please wait for the previous task to finish!');
			$lorhondel->parties->save($lorhondel->factory(\Lorhondel\Parts\Party\Party::class, ['player1' => $player->id]));
			return $lorhondel->parties->freshen()->done(
				function($result) use ($lorhondel, $message, $player) {
					if (count($collection = $lorhondel->parties->filter(fn($p) => $p->player1 == $player->id))>0) {
						foreach ($collection as $party) { //There should only be one
							$player->party_id = $party->id;
							$lorhondel->players->save($player)->done(
								function ($result) use ($message, $party) {
									return $message->reply('Created party `'. ($party->name ?? $party->id) . '`!');
								}
							);
						}
					} else return $message->reply('Something went wrong! Unable to locate the newly created party.');
				}
			);
		} else return $message->reply('Please leave your current party before creating a new one!');
	}
	elseif (str_starts_with($message_content_lower, 'join')) {
		$id = $message_content_lower = trim(substr($message_content_lower, 4));
		foreach (['<@', '!', '>'] as $filter)
			$id = str_replace($filter, '', $id);
		if(! is_numeric($id)) return $message->reply('Invalid format! Please include the ID of either the Discord account, player, or party you wish to join.');
		
		if (! $player) return $message->reply('You do not currently have an active player!');
		//Turn this into a function
		if (! $party = $lorhondel->parties->offsetGet($id)) {
			if ($target_player = getCurrentPlayer($lorhondel, $id) ?? $lorhondel->players->offsetGet($id)) {
				if ($target_player->party_id) $party = $lorhondel->parties->offsetGet($player->party_id);
				else return $message->reply('Player is not in a party!');
			} else return $message->reply('No Party or Player found!');
		}
		
		switch ($player->party_id) {
			case ($party->id):
				return $message->reply('You are already in the party!');
			case is_numeric($player->party_id):
				return $message->reply('You must leave your current party first!');
		}
		
		if (! $party->looking) return $message->reply('Party is not currently looking for players!');
		
		if (! $position = $party->join($lorhondel, $player)) return $message->reply('Party is full!');
		else $message->reply('Player ' . ($player->name ?? $player->id) . ' has joined Party ' . ($party->name ?? $party->id) . " in position $position!");
		
		if (! isPartyJoinable($lorhondel, $party)) {
			$party->looking = false;
		}
		$player->party_id = $party->id;
		$player->looking = false;
		
		$lorhondel->players->save($player);
		$lorhondel->parties->save($party);
		return;
	}
	/*
	*********************
	*********************
	These commands require an active party
	*********************
	*********************
	*/
	if (! $party) return $message->reply('No active party found! Try joining one with `;party join {party or player id here}`');
	if (str_starts_with($message_content_lower, 'leave')) {
		if ($position = $party->leave($lorhondel, $player))
			return $message->reply("You are no longer player $position in party " . ($party->name ?? $party->id));
		else return $message->reply("Something went wrong!"); //This shouldn't happen unless they aren't actually a member of the party
	}
	elseif (! $message_content_lower) {
		return $message->channel->sendEmbed(partyEmbed($lorhondel, $party));
	}
	elseif ($message_content_lower) {
		return $message->reply("Unrecognized subcommand `$message_content_lower'");
	}
}

$documentation = '';
