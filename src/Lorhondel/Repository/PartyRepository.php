<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
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
        'all' => Endpoint::PLAYER_CURRENT_PARTIES,
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
		return $browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function (Psr\Http\Message\ResponseInterface $response) use ($lorhondel, $message, $part) {
				echo '[SAVE] '; var_dump($lorhondel->parties->offsetUnset($part->id)); 
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
            $part = $this->factory->part($this->class, [$this->discrim => $part], true);
        }
		
		$url = Http::BASE_URL . "/parties/delete/{$part->id}/";
		return $browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function (Psr\Http\Message\ResponseInterface $response) use ($lorhondel, $message, $part) {
				echo '[DELETE] '; var_dump($lorhondel->parties->offsetUnset($part->id)); 
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
		return $browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function (Psr\Http\Message\ResponseInterface $response) use ($lorhondel, $message, $part) {
				echo '[FRESH] '; var_dump($lorhondel->parties->offsetUnset($part->id)); 
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
		return $browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function (Psr\Http\Message\ResponseInterface $response) use ($lorhondel, $message, $part) {
				echo '[FETCH] '; var_dump($lorhondel->parties->offsetUnset($part->id)); 
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
		$this->factory->lorhondel->browser->get($url)->done( //Make this a function
			function (Psr\Http\Message\ResponseInterface $response) { //TODO: Not receiving response
				echo '[RESPONSE] ' . PHP_EOL;
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
				
				//echo '[parties REPOSITORY]' . PHP_EOL;
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
     * Causes the player to leave a party.
     *
     * @param Party|int $party
     *
     * @return ExtendedPromiseInterface
     */
    public function leave($party)
    {
        if ($party instanceof party) {
            $party = $party->id;
        }

		/*
        return $this->http->delete(Endpoint::bind(Endpoint::PLAYER_CURRENT_PARTY, $party))->then(function () use ($party) {
            $this->pull('id', $party);

            return $this;
        });
		*/
    }
	
	/**
     * Causes the player to join a party.
     *
     * @param Party|int $party
     *
     * @return ExtendedPromiseInterface
     */
    public function join($party)
    {
        if ($party instanceof Party) {
            $party = $party->id;
        }

		/*
        return $this->http->delete(Endpoint::bind(Endpoint::PLAYER_CURRENT_PARTY, $party))->then(function () use ($party) {
            $this->pull('id', $party);

            return $this;
        });
		*/
    }
	
	/**
     * Causes the player to join a party.
     *
     * @param Party|int $party
     *
     * @return ExtendedPromiseInterface
     */
    public function induct($player, $party)
    {
        if ($party instanceof Party) {
            $party = $party->id;
        }
		
		if ($player instanceof Player) {
            $player = $player->id;
        }

		/*
        return $this->http->delete(Endpoint::bind(Endpoint::PLAYER_CURRENT_PARTY, $party))->then(function () use ($party) {
            $this->pull('id', $party);

            return $this;
        });
		*/
    }
	
	

	/**
     * Alias for delete.
     *
     * @param Player $player The player to kick.
     *
     * @return PromiseInterface
     *
     * @see self::delete()
     */
    public function kick(Player $player): PromiseInterface
    {
        return $this->delete($player);
    }
	
	/**
     * Alias for delete.
     *
     * @param Player $player The player to kick.
     *
     * @return PromiseInterface
     *
     * @see self::delete()
     */
    public function kick(Player $player): PromiseInterface
    {
        return $this->delete($player);
    }
	
	
}
