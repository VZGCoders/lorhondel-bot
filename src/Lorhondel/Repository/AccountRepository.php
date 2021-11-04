<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Repository;

use Lorhondel\Endpoint;
use Lorhondel\Http;
use Lorhondel\Parts\Account\Account;
use Lorhondel\Parts\Part;
use React\Promise\ExtendedPromiseInterface;

/**
 * Contains all Accounts in Lorhondel.
 *
 * @see \Lorhondel\Parts\Account\Account
 *
 * @method Account|null get(string $discrim, $key)  Gets an item from the collection.
 * @method Account|null first()                     Returns the first element of the collection.
 * @method Account|null pull($key, $default = null) Pulls an item from the repository, removing and returning the item.
 * @method Account|null find(callable $callback)    Runs a filter callback over the repository.
 */
class AccountRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected $endpoints = [
        'get' => Endpoint::ACCOUNT,
		//'put' => Endpoint::ACCOUNT_PUT,
		'create' => Endpoint::ACCOUNT_POST,
		'post' => Endpoint::ACCOUNT_POST,
		'update' => Endpoint::ACCOUNT_PATCH,
        'delete' => Endpoint::ACCOUNT_DELETE,
    ];

	 /**
     * @inheritdoc
     */
    protected $class = Account::class;
	
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
		$url = Http::BASE_URL . "/accounts/$method/{$part->id}/";
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
		
		$url = Http::BASE_URL . "/accounts/delete/{$part->id}/";
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

        $url = Http::BASE_URL . "/accounts/fresh/{$part->id}/";
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
        $url = Http::BASE_URL . "/accounts/fetch/{$part->id}/";
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
		$url = Http::BASE_URL . "/accounts/get/all/"; echo '[URL] ' . $url . PHP_EOL;
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
				
				//echo '[ACCOUNTS REPOSITORY]' . PHP_EOL;
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

	/*
	* Placeholder.
	*
	* @return string
	*/
	public function register($discord_id): string
	{
		$collection = $this->filter(fn($p) => $p->discord_id == $discord_id);
		foreach ($collection as $account)
			return 'An account with ID `' . $account->id . '` already exists for Discord ID `' . $discord_id . '`!';
		return $this->new($discord_id);
	}

	/**
     * Creates a new Account for the repository and saves it to the Lorhondel servers.
	 * This function is designed to take data from users.
     *
     * @param string $species    The species of the account.
     * @param string $name       The name of the account.
     *
     * @return string
     */
    public function new($discord_id = null): string
    {
		$snowflake = \Lorhondel\generateSnowflake($this->factory->lorhondel);
		if ($part = $this->factory->lorhondel->factory(\Lorhondel\Parts\Account\Account::class, [
			'id' => $snowflake,
			'discord_id' => $discord_id,
		])) {
			echo '[CREATE ACCOUNT WITH PART]'; var_dump($part);
			$this->save($part);
			$return = 'Created Account `' . $part->id . '`';
			if ($discord_id) $return .= ' for Discord ID `' . $discord_id . '`';
			$return .= '!';
			return $return;
		} else return 'Error building Account part!';
	}

	/**
     * Creates a new Account for the repository and saves it to the Lorhondel servers.
	 * This function is designed to take data from users.
     *
     * @param string $species    The species of the account.
     * @param string $name       The name of the account.
     *
     * @return string
     */
    public function activate($author_id = null, $id = null): string
    {
		if (! ($id instanceof Account)) {
			if (! is_numeric($id)) return "You must include the numeric ID of the Account you want to activate! You can check `<@{$this->factory->lorhondel->discord->id}> accounts` if you need a list of your IDs!";
			if (! $part = $this->offsetGet($id)) return "Unable to locate a Account with ID `$id`!";
		} else {
			$part = $id;
			$id = $id->id;
		}
		
		if (! $author_id) return $part->activate($this->factory->lorhondel);
		if ($part->discord_id == $author_id) return $part->activate($this->factory->lorhondel);
		return 'You can only activate a Account that you own!';
	}
	
	/*
	* Creates an Embed for a Account.
	*
	* @param string|int|Account $id    The Account to generate the Embed for.
	*
	* @return string|Discord\Parts\Embed\Embed
	*/
	function accountEmbed($id)
	{
		if (! ($id instanceof Account)) {
			if (! is_numeric($id)) return "You must include the numeric ID of the Account! You can check `accounts` if you need a list of your IDs!";
			if (! $account = $this->offsetGet($id)) return "Unable to locate a Account with ID `$id`!";
		} else $account = $id;
		
		$embed = $this->factory->lorhondel->discord->factory(\Discord\Parts\Embed\Embed::class);
		$embed->setColor(0xe1452d)
		//	->setDescription('$author_guild_name') // Set a description (below title, above fields)
		//	->setImage('https://avatars1.githubusercontent.com/u/4529744?s=460&v=4') // Set an image (below everything except footer)
			->setTimestamp()
			->setFooter('Lonhondel by ArtsyAxolotl#5128')
			->setURL('');
		if ($account->name) $embed->addFieldValues('Name', $account->name, true);
		$embed->addFieldValues('Species', $account->species, true);
		if ($account->party_id) $embed->addFieldValues('Party ID', $account->party_id, true);
		if ($party = $this->factory->lorhondel->parties->offsetGet($account->party_id))
			if ($party->name) $embed->addFieldValues('Party Name', $party->name, true);
		$embed->addFieldValues('ID', $account->id, false);
		$embed	
			->addFieldValues('Health', $account->health, true)
			->addFieldValues('Attack', $account->attack, true)
			->addFieldValues('Defense', $account->defense, true)
			->addFieldValues('Speed', $account->speed, true)
			->addFieldValues('Skill Points', $account->skillpoints, true);
		if ($user = $this->factory->lorhondel->discord->users->offsetGet($account->discord_id)) {
			$embed->setAuthor("{$user->username} ({$user->id})", $user->avatar); // Set an author with icon
			$embed->setThumbnail("{$user->avatar}"); // Set a thumbnail (the image in the top right corner)
		}
		return $embed;
	}
}
