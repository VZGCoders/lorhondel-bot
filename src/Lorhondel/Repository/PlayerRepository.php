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
 * Contains users that the user shares guilds with.
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
        'update' => Endpoint::PLAYER,
		'post' => Endpoint::PLAYER_POST,
		'create' => Endpoint::PLAYER_POST,
        'delete' => Endpoint::PLAYER,
    ];

    /**
     * Attempts to delete a part on the Lorhondel servers.
     *
     * @param Part|snowflake $part The part to delete.
     *
     * @return ExtendedPromiseInterface
     * @throws \Exception
	 */
    protected $class = Player::class;
	
	public function delete($part): ExtendedPromiseInterface
	{
		if (! ($part instanceof Part)) {
            $part = $this->factory->part($this->class, [$this->discrim => $part], true);
        }

        if (! $part->created) {
            return \React\Promise\reject(new \Exception('You cannot delete a non-existant part.'));
        }
		
		$url = Http::BASE_URL . "/players/delete/{$part->id}/";
		return $browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function (Psr\Http\Message\ResponseInterface $response) use ($lorhondel, $message, $part) {
				echo '[DELETE] '; var_dump($lorhondel->players->offsetUnset($part->id)); 
				//var_dump($lorhondel->players);
			},
			function ($error) {
				echo '[DELETE ERROR]' . PHP_EOL;
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
		echo '[FRESHEN()]' . PHP_EOL;
		$url = Http::BASE_URL . "/players/get/all/"; echo '[URL] ' . $url . PHP_EOL;
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
