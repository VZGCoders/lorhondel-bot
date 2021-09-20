<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Parts\Player;

use Lorhondel\Exceptions\FileNotFoundException;
use Lorhondel\Endpoint;
use Lorhondel\Parts\OAuth\Application;
use Lorhondel\Parts\Part;
use Lorhondel\Repository\PlayerRepository;
use Lorhondel\Repository\PartyRepository;
use React\Promise\ExtendedPromiseInterface;

/**
 * The client is the main interface for the client. Most calls on the main class are forwarded here.
 *
 * @property string                   $id               The unique identifier of the client.
 * @property Player                   $player           The player instance of the client.
 * @property int|null                 $user_id          The unique identifier of the user.
 * @property User|null                $user             The Discord user instance of the player.
 * @property PlayerRepository         $players
 * @property PartiesRepository        $parties
 */
class Client extends Part
{
    /**
     * @inheritdoc
     */
    protected static $fillable = ['id', 'player', 'user_id', 'user'];

    /**
     * @inheritdoc
     */
    protected $repositories = [
        'players' => PlayerRepository::class,
		'parties' => PartyRepository::class,
    ];

	/**
     * Returns the fillable attributes.
     *
     * @return array
     */
    public static function getFillableAttributes($context = '')
	{
		$fillable = array();
		foreach (self::$fillable as $attr) {
			if (! $context || in_array($context, self::$fillable)) {
				$fillable[] = $attr;
			}
		}
		return $fillable;
	}

    /**
     * Runs any extra construction tasks.
     */
    public function afterConstruct(): void
    {
        $this->application = $this->factory->create(Application::class, [], true);

        $this->http->get(Endpoint::APPLICATION_CURRENT)->done(function ($response) {
            $this->application->fill((array) $response);
        });
    }

    /**
     * Gets the player attribute.
     *
     * @return Player
     */
    protected function getPlayerAttribute()
    {
        return $this->factory->create(Player::class, $this->attributes, true);
    }
	
    /**
     * Saves the client instance.
     *
     * @return ExtendedPromiseInterface
     */
    public function save(): ExtendedPromiseInterface
    {
        return $this->http->patch(Endpoint::PLAYER_CURRENT, $this->getUpdatableAttributes());
    }

    /**
     * @inheritdoc
     */
    public function getUpdatableAttributes($discord = null): array
    {
		if (isset($this->attributes['user_id'])) {
			$attributes['user'] = $this->discord->users->offsetGet($attributes['user_id']);
		}

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getRepositoryAttributes(): array
    {
        return [];
    }
}
