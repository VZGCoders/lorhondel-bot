<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Repository;

use Lorhondel\Endpoint;
use Lorhondel\Http;
use Lorhondel\Parts\Vote\Vote;
use Lorhondel\Parts\Part;
use React\Promise\ExtendedPromiseInterface;

/**
 * Contains all votes in Lorhondel.
 *
 * @see \Lorhondel\Parts\Vote\Vote
 *
 * @method Vote|null get(string $discrim, $key)  Gets an item from the collection.
 * @method Vote|null first()                     Returns the first element of the collection.
 * @method Vote|null pull($key, $default = null) Pulls an item from the repository, removing and returning the item.
 * @method Vote|null find(callable $callback)    Runs a filter callback over the repository.
 */
class VoteRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected $endpoints = [
        'get' => Endpoint::VOTE,
		//'put' => Endpoint::VOTE_PUT,
		'create' => Endpoint::VOTE_POST,
		'post' => Endpoint::VOTE_POST,
		'update' => Endpoint::VOTE_PATCH,
        'delete' => Endpoint::VOTE_DELETE,
    ];

	 /**
     * @inheritdoc
     */
    protected $class = Vote::class;
	
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
		$url = Http::BASE_URL . "/votes/$method/{$part->id}/";
		return $this->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function (Psr\Http\Message\ResponseInterface $response) use ($part) {
				echo '[SAVE] '; //var_dump($lorhondel->votes->offsetUnset($part->id)); 
				//var_dump($lorhondel->votes);
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
		
		$url = Http::BASE_URL . "/votes/delete/{$part->id}/";
		return $this->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function (Psr\Http\Message\ResponseInterface $response) use ($part) {
				echo '[DELETE] '; //var_dump($lorhondel->votes->offsetUnset($part->id)); 
				//var_dump($lorhondel->votes);
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

        $url = Http::BASE_URL . "/votes/fresh/{$part->id}/";
		return $this->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function (Psr\Http\Message\ResponseInterface $response) use ($lorhondel, $message, $part) {
				echo '[FRESH] '; var_dump($lorhondel->votes->offsetUnset($part->id)); 
				//var_dump($lorhondel->votes);
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
        $url = Http::BASE_URL . "/votes/fetch/{$part->id}/";
		return $this->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function (Psr\Http\Message\ResponseInterface $response) use ($lorhondel, $message, $part) {
				echo '[FETCH] '; var_dump($lorhondel->votes->offsetUnset($part->id)); 
				//var_dump($lorhondel->votes);
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
		$url = Http::BASE_URL . "/votes/get/all/"; echo '[URL] ' . $url . PHP_EOL;
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
				
				//echo '[VOTES REPOSITORY]' . PHP_EOL;
				//var_dump($that->factory->lorhondel->votes);
				return $this;
			},
			function ($error) {
				echo '[BROWSER ERROR]' . $error->getMessage() . PHP_EOL;
				var_dump($error);
			}
		);
    }
}
