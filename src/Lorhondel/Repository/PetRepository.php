<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Repository;

use Lorhondel\Endpoint;
use Lorhondel\Http;
use Lorhondel\Parts\Pet\Pet;
use Lorhondel\Parts\Part;
use React\Promise\ExtendedPromiseInterface;

/**
 * Contains all Pets in Lorhondel.
 *
 * @see \Lorhondel\Parts\Pet\Pet
 *
 * @method Pet|null get(string $discrim, $key)  Gets an item from the collection.
 * @method Pet|null first()                     Returns the first element of the collection.
 * @method Pet|null pull($key, $default = null) Pulls an item from the repository, removing and returning the item.
 * @method Pet|null find(callable $callback)    Runs a filter callback over the repository.
 */
class PetRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected $endpoints = [
        'get' => Endpoint::PET,
		//'put' => Endpoint::PET_PUT,
		'create' => Endpoint::PET_POST,
		'post' => Endpoint::PET_POST,
		'update' => Endpoint::PET_PATCH,
        'delete' => Endpoint::PET_DELETE,
    ];

	 /**
     * @inheritdoc
     */
    protected $class = Pet::class;
	
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
		$url = Http::BASE_URL . "/pets/$method/{$part->id}/";
		return $this->factory->lorhondel->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function ($response) use ($part) {
				echo '[SAVE RESPONSE] '; //var_dump($this->offsetGet($part->id)); 
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
			$part = $this->offsetGet($part);
        }
		
		$url = Http::BASE_URL . "/pets/delete/{$part->id}/";
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

        $url = Http::BASE_URL . "/pets/fresh/{$part->id}/";
		return $this->factory->lorhondel->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function ($response) use ($lorhondel, $message, $part) {
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
        $url = Http::BASE_URL . "/pets/fetch/{$part->id}/";
		return $this->factory->lorhondel->browser->post($url, ['Content-Type' => 'application/json'], json_encode($part))->then( //Make this a function
			function ($response) use ($lorhondel, $message, $part) {
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
    public function freshen()
    {
		$url = Http::BASE_URL . "/pets/get/all/"; echo '[URL] ' . $url . PHP_EOL;
		return $this->factory->lorhondel->browser->get($url)->then( //Make this a function
			function ($response) { //TODO: Not receiving response
				echo '[FRESHEN RESPONSE] ' . PHP_EOL;
				$obj = json_decode((string)$response->getBody()->getContents());
				if (is_object($obj)) {
					echo '[VALID JSON]' . PHP_EOL;
					var_dump($obj);
				}
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
				
				//echo '[PETS REPOSITORY]' . PHP_EOL;
				//var_dump($this);
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
     * Creates a new Pet for the repository and saves it to the Lorhondel servers.
	 * This function is designed to take data from users.
     *
     * @param string $species    The species of the pet.
     * @param string $name       The name of the pet.
     *
     * @return string
     */
    public function new($author_id = null, $species = null, $name = null, $class = null): string
    {
		$snowflake = \Lorhondel\generateSnowflake($this->factory->lorhondel);
		if ($part = $this->factory->lorhondel->factory(\Lorhondel\Parts\Pet\Pet::class, [
			'id' => $snowflake,
			'user_id' => $author_id,
			'name' => $name,
			'species' => $species,
		])) {
			if (! $species || ! in_array(trim($species), $part::getFillableSpeciesAttributes())) {
				$return = 'Please tell us the species you want for your Pet in the following format: `<@' . $this->factory->lorhondel->id . '> pet create {species}` where `{species}` is any of the following:' . PHP_EOL;
				foreach ($part::getFillableSpeciesAttributes() as $choice) {
					$return .= "$choice, ";
				}
				$return = substr($return, 0, strlen($return)-2);
				return $return;
			}		
			echo '[CREATE PET WITH PART]'; var_dump($part);
			$this->save($part);
			return 'Created pet `' . $part->name . ' ` with ID `' . $part->id . '`. You can make this your active Pet by using the command <@' . $this->factory->lorhondel->id . '> pet activate ' . $part->id . '`.';				
		} else return 'Error building Pet part!';
	}

	/**
     * Creates a new Pet for the repository and saves it to the Lorhondel servers.
	 * This function is designed to take data from users.
     *
     * @param string $species    The species of the pet.
     * @param string $name       The name of the pet.
     *
     * @return string
     */
    public function activate($author_id = null, $id = null): string
    {
		if (! ($id instanceof Pet)) {
			if (! is_numeric($id)) return "You must include the numeric ID of the Pet you want to activate! You can check `<@{$this->factory->lorhondel->id}> pets` if you need a list of your IDs!";
			if (! $part = $this->offsetGet($id)) return "Unable to locate a Pet with ID `$id`!";
		} else {
			$part = $id;
			$id = $id->id;
		}
		
		if (! $author_id) return $part->activate($this->factory->lorhondel);
		if ($part->user_id == $author_id) return $part->activate($this->factory->lorhondel);
		return 'You can only activate a Pet that you own!';
	}
}
