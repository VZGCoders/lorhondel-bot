<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Repository;

use Lorhondel\Endpoint;
use Lorhondel\Http;
use Lorhondel\Parts\Player\Player;
use Lorhondel\Parts\Part;
use React\Promise\ExtendedPromiseInterface;

/**
 * Contains all players in Lorhondel.
 *
 * @see \Lorhondel\Parts\Player\Player
 *
 * @method Player|null get(string $discrim, $key)  Gets an item from the collection.
 * @method Player|null first()                     Returns the first element of the collection.
 * @method Player|null pull($key, $default = null) Pulls an item from the repository, removing and returning the item.
 * @method Player|null find(callable $callback)    Runs a filter callback over the repository.
 */
class PlayerRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected $endpoints = [
        'get' => Endpoint::PLAYER,
		//'put' => Endpoint::PLAYER_PUT,
		'create' => Endpoint::PLAYER_POST,
		'post' => Endpoint::PLAYER_POST,
		'update' => Endpoint::PLAYER_PATCH,
        'delete' => Endpoint::PLAYER_DELETE,
    ];

	 /**
     * @inheritdoc
     */
    protected $class = Player::class;
	
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
		if ($this->offsetGet($part->id)) $method = 'patch';
		else $method = 'post';
		$url = Http::BASE_URL . "/players/$method/{$part->id}/";
		return $this->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function ($response) use ($part) {
				echo '[SAVE RESPONSE] '; //var_dump($lorhondel->players->offsetUnset($part->id)); 
				//var_dump($lorhondel->players);
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
			$part = $this->factory->lorhondel->players->offsetGet($part);
        }
		
		$url = Http::BASE_URL . "/players/delete/{$part->id}/";
		return $this->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function ($response) use ($part) {
				echo '[DELETE RESPONSE] '; //var_dump($lorhondel->players->offsetUnset($part->id)); 
				//var_dump($lorhondel->players);
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

        $url = Http::BASE_URL . "/players/fresh/{$part->id}/";
		return $this->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function ($response) use ($lorhondel, $message, $part) {
				echo '[FETCH RESPONSE] '; //var_dump($lorhondel->players->offsetUnset($part->id)); 
				//var_dump($lorhondel->players);
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
        $url = Http::BASE_URL . "/players/fetch/{$part->id}/";
		return $this->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function ($response) use ($lorhondel, $message, $part) {
				echo '[FETCH RESPONSE] '; //var_dump($lorhondel->players->offsetUnset($part->id)); 
				//var_dump($lorhondel->players);
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
		$url = Http::BASE_URL . "/players/get/all/"; echo '[URL] ' . $url . PHP_EOL;
		return $this->factory->lorhondel->browser->get($url)->done( //Make this a function
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
				
				//echo '[PLAYERS REPOSITORY]' . PHP_EOL;
				//var_dump($that->factory->lorhondel->players);
				return $this;
			},
			function ($error) {
				echo '[BROWSER ERROR]' . $error->getMessage() . PHP_EOL;
				var_dump($error);
			}
		);
    }
}
