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

    public function save(Part $part)
    {
		if ($this->factory->lorhondel->parties->offsetGet($part->id)) $method = 'patch';
		else $method = 'post';
		$url = Http::BASE_URL . "/parties/$method/{$part->id}/";
		return $this->factory->lorhondel->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function ($response) use ($part) {
				echo '[SAVE RESPONSE] '; //var_dump($this->factory->lorhondel->parties->offsetGet($part->id)); 
				//var_dump($lorhondel->parties);
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
				echo '[DELETE RESPONSE] '; //var_dump($lorhondel->parties->offsetUnset($part->id)); 
				//var_dump($lorhondel->parties);
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
				echo '[FETCH RESPONSE] '; //var_dump($lorhondel->parties->offsetUnset($part->id)); 
				//var_dump($lorhondel->parties);
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
				echo '[FETCH RESPONSE] '; //var_dump($lorhondel->parties->offsetUnset($part->id)); 
				//var_dump($lorhondel->parties);
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
    public function freshen()
    {
		$url = Http::BASE_URL . "/parties/get/all/"; echo '[URL] ' . $url . PHP_EOL;
		return $this->factory->lorhondel->browser->get($url)->then( //Make this a function
			function ($response) { //TODO: Not receiving response
				echo '[FRESHEN RESPONSE] ' . PHP_EOL;
				if (is_object(json_decode((string)$response->getBody()->getContents()))) echo '[VALID JSON]' . PHP_EOL;
				/*
				$freshen = (string)$response->getBody()->getContents();
				var_dump($freshen);
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

	/**
     * Causes the player to join a party.
     *
     * @param Party|int $party
     *
     * @return ExtendedPromiseInterface
     */
    public function join($party, $player)
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
			return $this->save($party);
		} else return false;
    }
	
	/**
     * Causes the player to join a party.
     *
	 * @param Player|int $party
     * @param Party|int $party
     *
     * @return ExtendedPromiseInterface
     */
    public function induct($party, $player)
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

		/*
        return $this->http->delete(Endpoint::bind(Endpoint::PLAYER_CURRENT_PARTY, $party))->then(function () use ($party) {
            $this->pull('id', $party);

            return $this;
        });
		*/

    }/**
     * Causes the player to leave a party.
     *
	 * @param Player|int $player The player to remove
     * @param Party|int $party The party to remove from
     *
     * @return ExtendedPromiseInterface
     */
    public function kick($party, $player)
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
			return $this->save($party);
		} else return $succeed;
    }
	
	/**
     * Kicks a member. Alias for `$players->expel($party, $player)`.
     *
     * @param Player|string $player
     *
     * @return ExtendedPromiseInterface
     */
    public function expel($party, $player)
    {
        return $this->kick($party, $player);
    }

	/**
     * Transfers ownership of the party to another player.
     *
     * @param Player|int $player The member to transfer ownership to.
     *
     * @return ExtendedPromiseInterface
     */
    public function transferOwnership($party, $player): ExtendedPromiseInterface
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
		
		if (in_array($player->id, (array) $party)) {
			$party->leader = $player->id;
			$this->save($party);
		}
    }

	/**
	 *
	 * Disbands the party if no players remain
	 * Assign a new party leader if no leader exists
	 *
     */
    public function succession($party)
    {
		if ($party instanceof Party || $party = $this->offsetGet($party)) {
            $party_id = $party->id;
        } else return false;
		
		if (! $party->leader) {
			if ($party->player1 && ($party->player1 != $party->leader))
				$party->leader = $party->player1;
			elseif ($party->player2 && ($party->player2 != $party->leader))
				$party->leader = $party->player2;
			elseif ($party->player3 && ($party->player3 != $party->leader))
				$party->leader = $party->player3;
			elseif ($party->player4 && ($party->player4 != $party->leader))
				$party->leader = $party->player4;
			elseif ($party->player5 && ($party->player5 != $party->leader))
				$party->leader = $party->player5;
			else return $party->disband();
			return $this->save($party);
		} else return false;
    }
	
	public function disband($party)
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
		
		return $this->delete($party);
	}
	

}
