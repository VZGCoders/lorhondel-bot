<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 *
 * This object works differently than other repositories because it is a collection of parties which contain references to object in other repositories
 */

namespace Lorhondel\Repository;

use Lorhondel\Endpoint;
use Lorhondel\Http;
use Lorhondel\Parts\Player\Player;
use Lorhondel\Parts\Party\Party;
use Lorhondel\Parts\Part;
use React\Promise\ExtendedPromiseInterface;

/**
 * Contains all parties in Lorhondel.
 *
 * @see \Lorhondel\Parts\Party\Party
 *
 * @method Party|null get(string $discrim, $key)  Gets an item from the collection.
 * @method Party|null first()                     Returns the first element of the collection.
 * @method Party|null pull($key, $default = null) Pulls an item from the repository, removing and returning the item.
 * @method Party|null find(callable $callback)    Runs a filter callback over the repository.
 */
class PartyRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected $endpoints = [
        'all' => Endpoint::PLAYER_CURRENT_PARTY,
        'get' => Endpoint::PARTY,
        'create' => Endpoint::PARTY,
        'update' => Endpoint::PARTY,
        'delete' => Endpoint::PARTY,
        'leave' => Endpoint::PARTY,
    ];

	 /**
     * @inheritdoc
     */
    protected $class = Party::class;
	
	/**
     * Attempts to save a part to the Lorhondel servers.
     *
     * @param Part $part The part to save.
     *
     * @return ExtendedPromiseInterface
     * @throws \Exception
     */

    public function save(Part $part): ExtendedPromiseInterface
    {
		if ($this->factory->lorhondel->parties->offsetGet($part->id)) $method = 'patch';
		else $method = 'post';
		$url = Http::BASE_URL . "/parties/$method/{$part->id}/";
		return $this->factory->lorhondel->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function ($response) use ($part) {
				echo '[SAVE RESPONSE] '; //var_dump($this->factory->lorhondel->parties->offsetGet($part->id)); 
				//var_dump($this);
			},
			function ($error) {
				echo '[SAVE ERROR]' . PHP_EOL;
				var_dump($error);
			}
		);
    }
	
	/**
     * Attempts to delete a part on the Lorhondel servers.
     *
     * @param Part|snowflake $part The part to delete.
     *
     * @return ExtendedPromiseInterface
     * @throws \Exception
	 */
	public function delete($part): ExtendedPromiseInterface
	{
		if (! ($part instanceof Part)) {
            $part = $this->factory->lorhondel->parties->offsetGet($part);
        }
		
		$url = Http::BASE_URL . "/parties/delete/{$part->id}/";
		return $this->factory->lorhondel->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function ($response) use ($part) {
				echo '[DELETE RESPONSE] '; //var_dump($this->offsetUnset($part->id)); 
				//var_dump($this);
			},
			function ($error) {
				echo '[DELETE ERROR]' . PHP_EOL;
				var_dump($error);
			}
		);
	}
	
	/**
     * Returns a part with fresh values.
     *
     * @param Part $part The part to get fresh values.
     *
     * @return ExtendedPromiseInterface
     * @throws \Exception
     */
    public function fresh(Part $part): ExtendedPromiseInterface
    {
        if (! $part->created) {
            return \React\Promise\reject(new \Exception('You cannot get a non-existant part.'));
        }

        if (! isset($this->endpoints['get'])) {
            return \React\Promise\reject(new \Exception('You cannot get this part.'));
        }

        $url = Http::BASE_URL . "/parties/fresh/{$part->id}/";
		return $this->factory->lorhondel->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function ($response) use ($part) {
				echo '[FETCH RESPONSE] '; //var_dump($this->offsetUnset($part->id)); 
				//var_dump($this);
			},
			function ($error) {
				echo '[FRESH ERROR]' . PHP_EOL;
				var_dump($error);
			}
		);
    }
	
	/**
     * Gets a part from the repository or Lorhondel servers.
     *
     * @param string $id    The ID to search for.
     * @param bool   $fresh Whether we should skip checking the cache.
     *
     * @return ExtendedPromiseInterface
     * @throws \Exception
     */
    public function fetch(string $id, bool $fresh = false): ExtendedPromiseInterface
    {
        if (! $fresh && $part = $this->get($this->discrim, $id)) {
            return \React\Promise\resolve($part);
        }

        if (! isset($this->endpoints['get'])) {
            return \React\Promise\resolve(new \Exception('You cannot get this part.'));
        }

        $part = $this->factory->create($this->class, [$this->discrim => $id]);
        $url = Http::BASE_URL . "/parties/fetch/{$part->id}/";
		return $this->factory->lorhondel->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function ($response) use ($part) {
				echo '[FETCH RESPONSE] '; //var_dump($this->offsetUnset($part->id)); 
				//var_dump($this);
			},
			function ($error) {
				echo '[FETCH ERROR]' . PHP_EOL;
				var_dump($error);
			}
		);
    }
	
	/**
     * Freshens the repository collection.
     *
     * @return ExtendedPromiseInterface
     * @throws \Exception
     */
    public function freshen(): ExtendedPromiseInterface
    {
		$url = Http::BASE_URL . "/parties/get/all/"; echo '[URL] ' . $url . PHP_EOL;
		return $this->factory->lorhondel->browser->get($url)->then( //Make this a function
			function ($response) { //TODO: Not receiving response
				echo '[FRESHEN RESPONSE] ' . PHP_EOL;
				$obj = json_decode((string)$response->getBody()->getContents());
				if (is_object($obj)) {
					echo '[VALID JSON]' . PHP_EOL;
					var_dump($obj);
				}
				$freshen = (string)$response->getBody()->getContents();
				var_dump($freshen);
				
				/*
				$this->fill([]);
				
				foreach ($freshen as $value) {
					$value = array_merge($this->vars, (array) $value);
					$part = $this->factory->create($this->class, $value, true);

					$that->push($part);
				}
				*/
				
				//echo '[PARTIES REPOSITORY]' . PHP_EOL;
				//var_dump($that->factory->lorhondel->parties);
				return $this;
			},
			function ($error) {
				echo '[BROWSER ERROR]' . $error->getMessage() . PHP_EOL;
				var_dump($error);
			}
		);
    }

	public function help(): string
	{
		return '';
	}

	/**
     * Creates a new Party for the repository and saves it to the Lorhondel servers.
	 * This function is designed to take data from users.
     *
     * @param string $species    The species of the Player.
     * @param string $name       The name of the Player.
     *
     * @return string
     */
	public function new($player = null, $party = null, $name = null): string
	{
		if (! $player) return 'Please create a Player or activate one first!';
		if ($party) return 'Please leave your current Party before creating a new one!';
		if ($name) $this->save($this->factory->lorhondel->factory(\Lorhondel\Parts\Party\Party::class, ['player1' => $player->id, 'name' => $name]));
		else $this->save($this->factory->lorhondel->factory(\Lorhondel\Parts\Party\Party::class, ['player1' => $player->id]));
		/*
		return $this->freshen()->done(
			function($result) use ($lorhondel, $message, $player) {
				if (count($collection = $this->filter(fn($p) => $p->player1 == $player->id))>0) {
					foreach ($collection as $party) { //There should only be one
						$player->party_id = $party->id;
						$lorhondel->players->save($player)->done(
							function ($result) use ($message, $party) {
								return $message->reply('Created Party `'. ($party->name ?? $party->id) . '`!');
							}
						);
					}
				}
			}
		);
		*/
		return 'Your Party is currently being created. You can retrieve it with `' . $this->factory->lorhondel->command_symbol . 'party` in a few moments.';
	}

	public function join($player = null, $party = null, $id = null): string
	{
		if (! $player) return 'Please create a Player or activate one first!';
		if ($player->party_id == ($id ?? $party->id)) return 'You are already in this Party!';
		if ($player->party_id) return 'Please leave your current Party before joining a new one!';
		if (! $party && ! $id) return 'Invalid format! Please include the ID of the Party you want your Player to join.';
		if (! $party && $id) { //Probably called by a user who needs to have been invited
			if (! $party = $this->offsetGet($id)) return "Party with id `$id` does not exist!";
			if (! $party->looking && ! in_array($player->id, $party->invites)) return "You were not invited to join Party `$id`!";
		}
		return $party->join($this->factory->lorhondel, $player);
	}

	/**
     * Causes the Player to join a Party.
     *
     * @param Party|int $party
     */
    public function induct($player = null, $party = null): bool
    {
		if (! ($party instanceof Party)) {
            if ($party = $this->offsetGet($party)) {
				return false;
			}
		}

        if (! ($player instanceof Player)) {
            if (! $player = $this->factory->lorhondel->players->offsetGet($player)) {
				return false;
			}
		}

		if (! $player->party_id && ! in_array($player->id, (array) $party)) {
			if (! $party->player1) {
				$party->player1 = $player->id;
			} elseif (! $party->player2) {
				$party->player2 = $player->id;
			} elseif (! $party->player3) {
				$party->player3 = $player->id;
			} elseif (! $party->player4) {
				$party->player4 = $player->id;
			} elseif (! $party->player5) {
				$party->player5 = $player->id;
			} else return false;
			$player->party_id = $party->id;
			$this->factory->lorhondel->players->save($player);
			$this->save($party);
			return true;
		} else return false;
    }
	
	/**
     * Causes the Player to leave a Party.
     *
	 * @param Player|int $player The Player to remove
     * @param Party|int $party The Party to remove from
     */
    public function kick($party, $player): string|bool
    {
        if (! $party instanceof Party) {
            if ($party = $this->offsetGet($party)) {
				return false;
			}
		}

		if (! $player instanceof Player) {
            if (! $player = $this->factory->lorhondel->players->offsetGet($player)) {
				return false;
			}
		}

		$member = false;
		foreach ($party as $key => $value) {
			if ($value == $player->id) {
				$party->$key = null;
				$member = true;
			}
		}
		if (! $member) return false;

		if ($succeed = $this->succession($party)) {
			$this->save($party);
			return $succeed;
		}
		return true;
    }
	
	/**
     * Kicks a member. Alias for `$players->expel($party, $player)`.
     *
     * @param Player|string $player
     */
    public function expel($party, $player): string|bool
    {
        return $this->kick($party, $player);
    }

	/**
     * Transfers ownership of the Party to another Player.
     * This is an internal server function and should not be callable by Players
	 *
	 * @param Player|int $player The Party to transfer ownership of.
     * @param Player|int $player The Player to transfer ownership to.
     */
    public function transferOwnership($party, $player): bool
    {
		if (! $party instanceof Party) {
            if (! $party = $this->offsetGet($party)) {
				return false;
			}
		}

		if (! $player instanceof Player) {
            if (! $player = $this->factory->lorhondel->players->offsetGet($player)) {
				return false;
			}
		}
		
		if ($party->player1 == $player->id) $party->leader = 'player1';
		elseif ($party->player2 == $player->id) $party->leader = 'player2';
		elseif ($party->player3 == $player->id) $party->leader = 'player3';
		elseif ($party->player4 == $player->id) $party->leader = 'player4';
		elseif ($party->player5 == $player->id) $party->leader = 'player5';
		else return false;
		$this->save($party);
		return true;
    }
	
	/*
	* Alias for transferOwnership
	* This is an internal server function and should not be callable by Players
	*/
	public function transfer($party, $target_player): string|bool
	{
		if ($this->transferOwnership($party, $target_player)) return false;
		return 'Player `' . ($target_player->name ?? $target_player->id) . '` is the new leader of `' . ($this->name ?? $this->party) . '`! `';;
	}
	/**
	 * Disbands the Party if no players remain
	 * Assign a new Party leader if no leader exists
     */
    public function succession($party)
    {
		if ($party instanceof Party || $party = $this->offsetGet($party)) {
            $party_id = $party->id;
        } else return false;
		
		if (! $party->leader) {
			if ($party->player1 && ($party->player1 != $party->leader))
				$party->leader = 'player1';
			elseif ($party->player2 && ($party->player2 != $party->leader))
				$party->leader = 'player2';
			elseif ($party->player3 && ($party->player3 != $party->leader))
				$party->leader = 'player3';
			elseif ($party->player4 && ($party->player4 != $party->leader))
				$party->leader = 'player4';
			elseif ($party->player5 && ($party->player5 != $party->leader))
				$party->leader = 'player5';
			else return $party->disband();
			$this->save($party);
			return true;
		} else return false;
    }
	
	public function disband($party): bool
	{
		//
		if ($party instanceof Party || $party = $this->offsetGet($party)) {
            $party_id = $party->id;
        } else return false;
		
		if ($party->player1 && $player = $this->factory->lorhondel->players->offsetGet($party->player1)) {
			$player->party_id = null;
			
		}
		if ($party->player2 && $player = $this->factory->lorhondel->players->offsetGet($party->player2)) {
			$player->party_id = null;
			$this->factory->lorhondel->players->save($player);
		}
		if ($party->player3 && $player = $this->factory->lorhondel->players->offsetGet($party->player3)) {
			$player->party_id = null;
			$this->factory->lorhondel->players->save($player);
		}
		if ($party->player4 && $player = $this->factory->lorhondel->players->offsetGet($party->player4)) {
			$player->party_id = null;
			$this->factory->lorhondel->players->save($player);
		}
		if ($party->player5 && $player = $this->factory->lorhondel->players->offsetGet($party->player5)) {
			$player->party_id = null;
			$this->factory->lorhondel->players->save($player);
		}
		$this->delete($party);
		return true;
	}
	
	/*
	* Creates an Embed for a Party.
	*
	* @param string|int|Party $id    The Party to generate the Embed for.
	*
	* @return string|Discord\Parts\Embed\Embed
	*/
	function partyEmbed($id)
	{
		if (! ($id instanceof Party)) {
			if (! is_numeric($id)) return "You must include the numeric ID of the Party! You can check `players` if you need a list of your IDs!";
			if (! $party = $this->offsetGet($id)) return "Unable to locate a Party with ID `$id`!";
		} else $party = $id;
		
		$players = array();
		$players[] = $player1 = $this->factory->lorhondel->players->offsetGet($party->player1);
		$players[] = $player2 = $this->factory->lorhondel->players->offsetGet($party->player2);
		$players[] = $player3 = $this->factory->lorhondel->players->offsetGet($party->player3);
		$players[] = $player4 = $this->factory->lorhondel->players->offsetGet($party->player4);
		$players[] = $player5 = $this->factory->lorhondel->players->offsetGet($party->player5);
		
		$embed = $this->factory->lorhondel->discord->factory(\Discord\Parts\Embed\Embed::class);
		$embed->setColor(0xe1452d)
		//	->setDescription('$author_guild_name') // Set a description (below title, above fields)
		//	->setImage('https://avatars1.githubusercontent.com/u/4529744?s=460&v=4') // Set an image (below everything except footer)
			->setTimestamp()
			->setFooter('Lorhondel by ArtsyAxolotl#5128')                             					// Set a footer without icon
			->setURL('');                             												// Set the URL
		if ($party->name) $embed->addFieldValues('Name', $party->name, true);
		$embed->addFieldValues('ID', $party->id, true);
		foreach ($players as $player) {
			if ($player && $user = $this->factory->lorhondel->discord->users->offsetGet($player->user_id)) {
				$embed->setAuthor("{$user->username} ({$user->id})", $user->avatar); // Set an author with icon
				if ($player->id == $party->{$party->leader}) {
				if ($player->name) $leader_string = "{$player->name} ({$player->id})";
				else $leader_string = "{$player->id}";
					$embed->addFieldValues('Leader', $leader_string, true);
					$embed->setThumbnail("{$user->avatar}"); // Set a thumbnail (the image in the top right corner)
				}
			}
		}
		$inline = false;
		for ($x=0; $x<count($players); $x++) {
			if ($players[$x]) {
				if ($players[$x]->name) $player_string = "{$players[$x]->name} ({$players[$x]->id})";
				else $player_string = "{$players[$x]->id}";
				$embed->addFieldValues('Player ' . $x+1, $player_string, $inline);
				$inline = true;
			}
		}
		if ($party->looking) $embed->addFieldValues('Looking', 'This Party is looking for Players!', true);
		return $embed;
	}
}