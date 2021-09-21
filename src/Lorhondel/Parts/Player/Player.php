<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Parts\Player;

use Lorhondel\Builders\MessageBuilder;
use Lorhondel\Endpoint;
use Lorhondel\Parts\Channel\Channel;
use Lorhondel\Parts\Part;
use Lorhondel\Parts\Channel\Message;
use React\Promise\ExtendedPromiseInterface;

/**
 * A player is a general player that is not attached to a group.
 *

 * @property int    $id            The unique identifier of the player.
 * @property int    $user_id       Discord user id.
 * @property int    $party_id      Current party id.
 * @property bool   $active        Whether the player is active.
 *
 * @property string $name          The name of the player.
 * @property string $species       The species of the player.
 * @property int    $health        Health, obviously.
 * @property int    $attack        How much damage you output.
 * @property int    $defense       How much damage you block.
 * @property int    $speed         Evasiveness; Higher speed means a higher chance to evade an attack.
 * @property int    $skillpoints   Skill Points.

 */
class Player extends Part
{

    /**
     * @inheritdoc
     */
    protected static $fillable = ['id', 'user_id', 'party_id', 'active', 'name', 'species', 'health', 'attack', 'defense', 'speed', 'skillpoints'];

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
     * @inheritdoc
     */
    public function getCreatableAttributes(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
			'party_id' => $this->party_id,
			'active' => $this->active,
			'name' => $this->name,
            'species' => $this->species,
            'health' => $this->health,
            'attack' => $this->attack,
            'defense' => $this->defense,
            'speed' => $this->speed,
            'skillpoints' => $this->skillpoints,
        ];
    }

    /**
     * Returns a timestamp for when a player's account was created.
     *
     * @return float
     */
    public function createdTimestamp()
    {
        return \Lorhondel\getSnowflakeTimestamp($this->id);
    }
	

    /**
     * @inheritdoc
     */
    public function getRepositoryAttributes(): array
    {
        return [
            'player_id' => $this->id,
        ];
    }

    /**
     * Returns a formatted mention.
     *
     * @return string A formatted mention.
     */
    public function __toString()
    {
        return "<@{$this->user_id}>";
    }
}
