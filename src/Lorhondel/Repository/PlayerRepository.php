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
 * Contains all Players in Lorhondel.
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

    public function save(Part $part): ExtendedPromiseInterface
    {
        if ($this->offsetGet($part->id)) $method = 'patch';
        else $method = 'post';
        $url = Http::BASE_URL . "/players/$method/{$part->id}/";
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
        
        $url = Http::BASE_URL . "/players/delete/{$part->id}/";
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

        $url = Http::BASE_URL . "/players/fresh/{$part->id}/";
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
        $url = Http::BASE_URL . "/players/fetch/{$part->id}/";
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
        $url = Http::BASE_URL . "/players/get/all/"; echo '[URL] ' . $url . PHP_EOL;
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
                
                //echo '[PLAYERS REPOSITORY]' . PHP_EOL;
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
     * Creates a new Player for the repository and saves it to the Lorhondel servers.
     * This function is designed to take data from users.
     *
     * @param string $species    The species of the player.
     * @param string $name       The name of the player.
     *
     * @return string
     */
    public function new($discord_id = null, $species = null, $name = null, $class = null): string
    {
        $snowflake = \Lorhondel\generateSnowflake($this->factory->lorhondel);
        if (! $account = $lorhondel->accounts->get('discord_id', $discord_id)) {
            //
            return $message->reply("I wasn't able to locate your account! Please register using `account register` before using Lorhondel-related commands.");
        }
            
        if ($part = $this->factory->lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
            'id' => $snowflake,
            'account_id' => $account->id,
            'name' => $name,
            'species' => $species,
        ])) {
            if (! $species || ! in_array(trim($species), $part::getFillableSpeciesAttributes())) {
                $return = 'Please tell us the species you want for your Player in the following format: `<@' . $this->factory->lorhondel->discord->id . '> player create {species}` where `{species}` is any of the following:' . PHP_EOL;
                foreach ($part::getFillableSpeciesAttributes() as $choice) {
                    $return .= "$choice, ";
                }
                $return = substr($return, 0, strlen($return)-2);
                return $return;
            }        
            echo '[CREATE PLAYER WITH PART]'; var_dump($part);
            $this->save($part);
            return 'Created player `' . $part->name . ' ` with ID `' . $part->id . '`. You can make this your active Player by using the command `<@' . $this->factory->lorhondel->discord->id . '> player activate ' . $part->id . '`.';                
        } else return 'Error building Player part!';
    }

    /**
     * Creates a new Player for the repository and saves it to the Lorhondel servers.
     * This function is designed to take data from users.
     *
     * @param string $species    The species of the player.
     * @param string $name       The name of the player.
     *
     * @return string
     */
    public function activate($discord_id = null, $id = null): string
    {
        if (! ($id instanceof Player)) {
            if (! is_numeric($id)) return "You must include the numeric ID of the Player you want to activate! You can check `<@{$this->factory->lorhondel->discord->id}> players` if you need a list of your IDs!";
            if (! $part = $this->offsetGet($id)) return "Unable to locate a Player with ID `$id`!";
        } else {
            $part = $id;
            $id = $id->id;
        }
        
        if (! $discord_id) return $part->activate($this->factory->lorhondel);
        if ($part->account_id) {
            if ($account = $this->factory->lorhondel->accounts->offsetGet($part->account_id))
                if ($account->discord_id == $discord_id) return $part->activate($this->factory->lorhondel);
        }
        return 'You can only activate a Player that you own!';
    }
    
    /*
    * Creates an Embed for a Player.
    *
    * @param string|int|Player $id    The Player to generate the Embed for.
    *
    * @return string|Discord\Parts\Embed\Embed
    */
    function playerEmbed($id)
    {
        if (! ($id instanceof Player)) {
            if (! is_numeric($id)) return "You must include the numeric ID of the Player! You can check `players` if you need a list of your IDs!";
            if (! $player = $this->offsetGet($id)) return "Unable to locate a Player with ID `$id`!";
        } else $player = $id;
        
        $embed = $this->factory->lorhondel->discord->factory(\Discord\Parts\Embed\Embed::class);
        $embed->setColor(0xe1452d)
        //    ->setDescription('$author_guild_name') // Set a description (below title, above fields)
        //    ->setImage('https://avatars1.githubusercontent.com/u/4529744?s=460&v=4') // Set an image (below everything except footer)
            ->setTimestamp()
            ->setFooter('Lonhondel by ArtsyAxolotl#5128')
            ->setURL('');
        if ($player->name) $embed->addFieldValues('Name', $player->name, true);
        $embed->addFieldValues('Species', $player->species, true);
        if ($player->party_id) $embed->addFieldValues('Party ID', $player->party_id, true);
        if ($party = $this->factory->lorhondel->parties->offsetGet($player->party_id))
            if ($party->name) $embed->addFieldValues('Party Name', $party->name, true);
        $embed->addFieldValues('ID', $player->id, false);
        $embed    
            ->addFieldValues('Health', $player->health, true)
            ->addFieldValues('Attack', $player->attack, true)
            ->addFieldValues('Defense', $player->defense, true)
            ->addFieldValues('Speed', $player->speed, true)
            ->addFieldValues('Skill Points', $player->skillpoints, true);
        if ($account = $this->factory->lorhondel->accounts->offsetGet($player->account_id)) {
            if ($user = $this->factory->lorhondel->discord->users->offsetGet($account->discord_id)) {
                $embed->setAuthor("{$user->username} ({$user->id})", $user->avatar); // Set an author with icon
                $embed->setThumbnail("{$user->avatar}"); // Set a thumbnail (the image in the top right corner)
            }
        }
        return $embed;
    }
}
